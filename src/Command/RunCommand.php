<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Command;

use PHPStanBodyscan202405\Symfony\Component\Console\Command\Command;
use PHPStanBodyscan202405\Symfony\Component\Console\Input\InputArgument;
use PHPStanBodyscan202405\Symfony\Component\Console\Input\InputInterface;
use PHPStanBodyscan202405\Symfony\Component\Console\Input\InputOption;
use PHPStanBodyscan202405\Symfony\Component\Console\Output\OutputInterface;
use PHPStanBodyscan202405\Symfony\Component\Console\Style\SymfonyStyle;
use PHPStanBodyscan202405\Symfony\Component\Process\Process;
use TomasVotruba\PHPStanBodyscan\Exception\AnalysisFailedException;
use TomasVotruba\PHPStanBodyscan\Logger;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter;
use TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory;
use TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory;
use TomasVotruba\PHPStanBodyscan\Utils\ComposerLoader;
use TomasVotruba\PHPStanBodyscan\Utils\FileLoader;
use TomasVotruba\PHPStanBodyscan\Utils\JsonLoader;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult;
final class RunCommand extends Command
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory
     */
    private $analyseProcessFactory;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory
     */
    private $phpStanConfigFactory;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter
     */
    private $jsonOutputFormatter;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter
     */
    private $tableOutputFormatter;
    public function __construct(SymfonyStyle $symfonyStyle, AnalyseProcessFactory $analyseProcessFactory, PHPStanConfigFactory $phpStanConfigFactory, JsonOutputFormatter $jsonOutputFormatter, TableOutputFormatter $tableOutputFormatter)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->analyseProcessFactory = $analyseProcessFactory;
        $this->phpStanConfigFactory = $phpStanConfigFactory;
        $this->jsonOutputFormatter = $jsonOutputFormatter;
        $this->tableOutputFormatter = $tableOutputFormatter;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('run');
        $this->setDescription('Check classes that are not used in any config and in the code');
        $this->addArgument('directory', InputArgument::OPTIONAL, 'Directory to scan', \getcwd());
        $this->addOption('min-level', null, InputOption::VALUE_REQUIRED, 'Min PHPStan level to run', 0);
        $this->addOption('max-level', null, InputOption::VALUE_REQUIRED, 'Max PHPStan level to run', 8);
        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'Path to project .env file');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Show result in JSON');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $minPhpStanLevel = (int) $input->getOption('min-level');
        $maxPhpStanLevel = (int) $input->getOption('max-level');
        $projectDirectory = $input->getArgument('directory');
        $isJson = (bool) $input->getOption('json');
        // silence output till the end to avoid invalid json format
        if ($isJson) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        $vendorBinDirectory = ComposerLoader::getBinDirectory($projectDirectory);
        // 1. is phpstan installed in the project?
        $this->ensurePHPStanIsInstalled($projectDirectory, $vendorBinDirectory);
        $envVariables = $this->loadEnvVariables($input);
        $levelResults = [];
        // 1. prepare empty phpstan config
        // no baselines, ignores etc. etc :)
        $phpstanConfiguration = $this->phpStanConfigFactory->create($projectDirectory);
        \file_put_contents($projectDirectory . '/phpstan-bodyscan.neon', $phpstanConfiguration);
        // 2. measure phpstan levels
        for ($phpStanLevel = $minPhpStanLevel; $phpStanLevel <= $maxPhpStanLevel; ++$phpStanLevel) {
            $this->symfonyStyle->section(\sprintf('Running PHPStan level %d', $phpStanLevel));
            $levelResults[] = $this->measureErrorCountInLevel($phpStanLevel, $projectDirectory, $envVariables);
            $this->symfonyStyle->newLine();
        }
        $bodyscanResult = new BodyscanResult($levelResults);
        // 3. tidy up temporary config
        \unlink($projectDirectory . '/phpstan-bodyscan.neon');
        if ($isJson) {
            $this->jsonOutputFormatter->outputResult($bodyscanResult);
        } else {
            $this->tableOutputFormatter->outputResult($bodyscanResult);
        }
        return self::SUCCESS;
    }
    /**
     * @param array<string, mixed> $envVariables
     */
    private function measureErrorCountInLevel(int $phpStanLevel, string $projectDirectory, array $envVariables) : LevelResult
    {
        $analyseLevelProcess = $this->analyseProcessFactory->create($projectDirectory, $phpStanLevel, $envVariables);
        $this->symfonyStyle->writeln('Running: <fg=green>' . $analyseLevelProcess->getCommandLine() . '</>');
        $analyseLevelProcess->run();
        $jsonResult = $analyseLevelProcess->getOutput();
        $json = JsonLoader::loadToArray($jsonResult, $analyseLevelProcess);
        // fatal errors, they stop the analyss
        if ((int) $json['totals']['errors'] > 0) {
            $loggedOutput = $jsonResult ?: $analyseLevelProcess->getErrorOutput();
            Logger::log($loggedOutput);
            throw new AnalysisFailedException(\sprintf('PHPStan failed on level %d with %d fatal errors. See %s for more', $phpStanLevel, (int) $json['totals']['errors'], Logger::LOG_FILE_PATH));
        }
        $fileErrorCount = (int) $json['totals']['file_errors'];
        $this->symfonyStyle->writeln(\sprintf('Found %d errors', $fileErrorCount));
        $this->symfonyStyle->newLine();
        Logger::log(\sprintf('Project directory "%s" - PHPStan level %d: %d errors', $projectDirectory, $phpStanLevel, $fileErrorCount));
        return new LevelResult($phpStanLevel, $fileErrorCount);
    }
    private function ensurePHPStanIsInstalled(string $projectDirectory, string $vendorBinDirectory) : void
    {
        if (!\file_exists($projectDirectory . $vendorBinDirectory . '/phpstan')) {
            $this->symfonyStyle->note('PHPStan not found in the project... installing');
            $requirePHPStanProcess = new Process(['composer', 'require', 'phpstan/phpstan', '--dev'], $projectDirectory);
            $requirePHPStanProcess->mustRun();
            return;
        }
        $this->symfonyStyle->note('PHPStan found in the project, lets run it!');
        $this->symfonyStyle->newLine(2);
    }
    /**
     * @return array<string, string>
     */
    private function loadEnvVariables(InputInterface $input) : array
    {
        $envFile = $input->getOption('env-file');
        if (!\is_string($envFile)) {
            return [];
        }
        $envVariables = FileLoader::resolveEnvVariablesFromFile($envFile);
        $this->symfonyStyle->note(\sprintf('Adding envs from "%s" file:', $envFile));
        foreach ($envVariables as $name => $value) {
            $this->symfonyStyle->writeln(' * ' . $name . ': ' . $value);
        }
        $this->symfonyStyle->newLine();
        return $envVariables;
    }
}
