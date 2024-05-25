<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\DependencyInstaller;
use TomasVotruba\PHPStanBodyscan\Logger;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter;
use TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory;
use TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory;
use TomasVotruba\PHPStanBodyscan\Process\PHPStanResultResolver;
use TomasVotruba\PHPStanBodyscan\Utils\FileLoader;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult;

final class RunCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly AnalyseProcessFactory $analyseProcessFactory,
        private readonly PHPStanConfigFactory $phpStanConfigFactory,
        private readonly JsonOutputFormatter $jsonOutputFormatter,
        private readonly TableOutputFormatter $tableOutputFormatter,
        private readonly DependencyInstaller $dependencyInstaller,
        private readonly PHPStanResultResolver $phpStanResultResolver
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('run');
        $this->setAliases(['scan', 'analyse']);
        $this->setDescription('Check classes that are not used in any config and in the code');

        $this->addArgument('directory', InputArgument::OPTIONAL, 'Directory to scan', getcwd());
        $this->addOption('min-level', null, InputOption::VALUE_REQUIRED, 'Min PHPStan level to run', 0);
        $this->addOption('max-level', null, InputOption::VALUE_REQUIRED, 'Max PHPStan level to run', 8);

        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'Path to project .env file');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Show result in JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDirectory = $input->getArgument('directory');

        $minPhpStanLevel = (int) $input->getOption('min-level');
        $maxPhpStanLevel = (int) $input->getOption('max-level');
        $isJson = (bool) $input->getOption('json');

        // silence output till the end to avoid invalid json format
        if ($isJson) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        // 1. is phpstan installed in the project?
        $this->dependencyInstaller->ensurePHPStanIsInstalled($projectDirectory);

        $envVariables = $this->loadEnvVariables($input);

        // 1. prepare empty phpstan config
        // no baselines, ignores etc. etc :)
        $phpstanConfiguration = $this->phpStanConfigFactory->create($projectDirectory);
        file_put_contents($projectDirectory . '/phpstan-bodyscan.neon', $phpstanConfiguration);

        $levelResults = [];

        // 2. measure phpstan levels
        for ($phpStanLevel = $minPhpStanLevel; $phpStanLevel <= $maxPhpStanLevel; ++$phpStanLevel) {
            $this->symfonyStyle->section(sprintf('Running PHPStan level %d', $phpStanLevel));
            $this->symfonyStyle->newLine();

            $levelResult = $this->measureErrorCountInLevel($phpStanLevel, $projectDirectory, $envVariables);
            $levelResults[] = $levelResult;

            $this->symfonyStyle->writeln(sprintf('Found %d errors', $levelResult->getErrorCount()));
            $this->symfonyStyle->newLine();
        }

        $bodyscanResult = new BodyscanResult($levelResults);

        // 3. tidy up temporary config
        unlink($projectDirectory . '/phpstan-bodyscan.neon');

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
    private function measureErrorCountInLevel(
        int $phpStanLevel,
        string $projectDirectory,
        array $envVariables
    ): LevelResult {
        $analyseLevelProcess = $this->analyseProcessFactory->create($projectDirectory, $phpStanLevel, $envVariables);

        // $this->symfonyStyle->writeln('Running: <fg=green>' . $analyseLevelProcess->getCommandLine() . '</>');
        $analyseLevelProcess->run();

        $result = $this->phpStanResultResolver->resolve($analyseLevelProcess);
        $fileErrorCount = (int) $result['totals']['file_errors'];

        Logger::log(sprintf(
            'Project directory "%s" - PHPStan level %d: %d errors',
            $projectDirectory,
            $phpStanLevel,
            $fileErrorCount
        ));

        return new LevelResult($phpStanLevel, $fileErrorCount);
    }

    /**
     * @return array<string, string>
     */
    private function loadEnvVariables(InputInterface $input): array
    {
        $envFile = $input->getOption('env-file');
        if (! is_string($envFile)) {
            return [];
        }

        $this->symfonyStyle->note(sprintf('Adding envs from "%s" file:', $envFile));
        $this->symfonyStyle->newLine();

        return FileLoader::resolveEnvVariablesFromFile($envFile);
    }
}
