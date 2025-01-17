<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Command;

use PHPStanBodyscan202501\Symfony\Component\Console\Command\Command;
use PHPStanBodyscan202501\Symfony\Component\Console\Input\InputInterface;
use PHPStanBodyscan202501\Symfony\Component\Console\Input\InputOption;
use PHPStanBodyscan202501\Symfony\Component\Console\Output\ConsoleOutput;
use PHPStanBodyscan202501\Symfony\Component\Console\Output\OutputInterface;
use PHPStanBodyscan202501\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Logger;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter;
use TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory;
use TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory;
use TomasVotruba\PHPStanBodyscan\Process\PHPStanResultResolver;
use TomasVotruba\PHPStanBodyscan\Utils\FileLoader;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult;
use PHPStanBodyscan202501\Webmozart\Assert\Assert;
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
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\Process\PHPStanResultResolver
     */
    private $phpStanResultResolver;
    /**
     * @var array<int, string>
     */
    private const DOT_STATES = ['   ', '.  ', '.. ', '...', '....', '.....'];
    public function __construct(SymfonyStyle $symfonyStyle, AnalyseProcessFactory $analyseProcessFactory, PHPStanConfigFactory $phpStanConfigFactory, JsonOutputFormatter $jsonOutputFormatter, TableOutputFormatter $tableOutputFormatter, PHPStanResultResolver $phpStanResultResolver)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->analyseProcessFactory = $analyseProcessFactory;
        $this->phpStanConfigFactory = $phpStanConfigFactory;
        $this->jsonOutputFormatter = $jsonOutputFormatter;
        $this->tableOutputFormatter = $tableOutputFormatter;
        $this->phpStanResultResolver = $phpStanResultResolver;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('run');
        $this->setAliases(['scan', 'analyse']);
        $this->setDescription('Check classes that are not used in any config and in the code');
        $this->addOption('min-level', null, InputOption::VALUE_REQUIRED, 'Min PHPStan level to run', 0);
        $this->addOption('max-level', null, InputOption::VALUE_REQUIRED, 'Max PHPStan level to run', 8);
        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'Path to project .env file');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Show result in JSON');
        $this->addOption('bare', null, InputOption::VALUE_NONE, 'Without any extensions, without ignores, without baselines, just pure PHPStan');
    }
    /**
     * @param ConsoleOutput $output
     * @return Command::*
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        /** @var string $projectDirectory */
        $projectDirectory = \getcwd();
        $minPhpStanLevel = (int) $input->getOption('min-level');
        $maxPhpStanLevel = (int) $input->getOption('max-level');
        Assert::lessThanEq($minPhpStanLevel, $maxPhpStanLevel);
        $isBare = (bool) $input->getOption('bare');
        $isJson = (bool) $input->getOption('json');
        // silence output till the end to avoid invalid json format
        if ($isJson) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        $envVariables = $this->loadEnvVariables($input);
        // 1. prepare empty phpstan config
        // no baselines, ignores etc. etc :)
        $phpstanConfig = $this->phpStanConfigFactory->create($projectDirectory, [], $isBare);
        \file_put_contents($projectDirectory . '/phpstan-bodyscan.neon', $phpstanConfig->getFileContents());
        $levelResults = [];
        if ($isBare) {
            // temporarily disable project PHPStan extensions
            $phpstanExtensionFile = $projectDirectory . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php';
            if (\file_exists($phpstanExtensionFile)) {
                $this->symfonyStyle->writeln('Disabling PHPStan extensions...');
                $this->symfonyStyle->newLine();
                \rename($phpstanExtensionFile, $phpstanExtensionFile . '.bak');
            }
        }
        // 2. measure phpstan levels
        for ($phpStanLevel = $minPhpStanLevel; $phpStanLevel <= $maxPhpStanLevel; ++$phpStanLevel) {
            $infoMessage = '<info>' . \sprintf('Running PHPStan level %d%s', $phpStanLevel, $isBare ? ' without extensions' : '') . '</info>';
            $section = $output->section();
            for ($i = 0; $i < 20; ++$i) {
                $stateIndex = $i % \count(self::DOT_STATES);
                $section->overwrite($infoMessage . ': ' . self::DOT_STATES[$stateIndex]);
                \usleep(700000);
            }
            $levelResult = $this->measureErrorCountInLevel($phpStanLevel, $projectDirectory, $envVariables);
            $levelResults[] = $levelResult;
            $section->overwrite(\sprintf($infoMessage . ': found %d errors', $levelResult->getErrorCount()));
        }
        if ($isBare) {
            // restore PHPStan extension file
            $this->symfonyStyle->writeln('Restoring PHPStan extensions...');
            $this->symfonyStyle->newLine();
            $phpstanExtensionFile = $projectDirectory . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php';
            \rename($phpstanExtensionFile . '.bak', $phpstanExtensionFile);
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
        $process = $this->analyseProcessFactory->create($projectDirectory, $phpStanLevel, $envVariables);
        $process->run();
        $result = $this->phpStanResultResolver->resolve($process);
        $fileErrorCount = (int) $result['totals']['file_errors'];
        Logger::log(\sprintf('Project directory "%s" - PHPStan level %d: %d errors', $projectDirectory, $phpStanLevel, $fileErrorCount));
        return new LevelResult($phpStanLevel, $fileErrorCount);
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
        $this->symfonyStyle->note(\sprintf('Adding envs from "%s" file:', $envFile));
        $this->symfonyStyle->newLine();
        return FileLoader::resolveEnvVariablesFromFile($envFile);
    }
}
