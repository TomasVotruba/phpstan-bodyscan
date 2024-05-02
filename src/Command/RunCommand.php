<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use TomasVotruba\PHPStanBodyscan\Exception\AnalysisFailedException;
use TomasVotruba\PHPStanBodyscan\Exception\ShouldNotHappenException;
use TomasVotruba\PHPStanBodyscan\Utils\FileLoader;
use TomasVotruba\PHPStanBodyscan\Utils\JsonLoader;
use TomasVotruba\PHPStanBodyscan\ValueObject\PHPStanLevelResult;

final class RunCommand extends Command
{
    /**
     * @var int
     */
    private const TIMEOUT_IN_SECONDS = 400;

    /**
     * @var string[]
     */
    private const POSSIBLE_SOURCE_PATHS = ['app', 'src', 'tests'];

    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('run');
        $this->setDescription('Check classes that are not used in any config and in the code');

        $this->addArgument('directory', InputArgument::OPTIONAL, 'Directory to scan', getcwd());
        $this->addOption('max-level', null, InputOption::VALUE_REQUIRED, 'Max PHPStan level to run', 8);

        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'Path to project .env file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maxPhpStanLevel = (int) $input->getOption('max-level');
        $projectDirectory = $input->getArgument('directory');

        // 1. is phpstan installed in the project?
        if (! file_exists($projectDirectory . '/vendor/phpstan')) {
            $this->symfonyStyle->note('PHPStan not found in the project... installing');
            $requirePHPStanProcess = new Process([
                'composer',
                'require',
                'phpstan/phpstan',
                '--dev',
            ], $projectDirectory);
            $requirePHPStanProcess->mustRun();
        } else {
            $this->symfonyStyle->note('PHPStan found in the project, lets run it!');
        }

        $envFile = $input->getOption('env-file');

        $phpStanLevelResults = [];

        // 2. measure phpstan levels
        for ($phpStanLevel = 0; $phpStanLevel <= $maxPhpStanLevel; ++$phpStanLevel) {
            $this->symfonyStyle->writeln(sprintf('Running PHPStan level %d', $phpStanLevel));

            $phpStanLevelResults[] = $this->measureErrorCountInLevel($phpStanLevel, $projectDirectory, $envFile);
        }

        $this->renderResultInTable($phpStanLevelResults);

        return self::SUCCESS;
    }

    private function measureErrorCountInLevel(
        int $phpStanLevel,
        string $projectDirectory,
        ?string $envFile
    ): PHPStanLevelResult {
        $phpStanBinFilePath = $this->resolvePhpStanBinFile($projectDirectory);

        // resolve source paths
        $sourcePaths = array_filter(
            self::POSSIBLE_SOURCE_PATHS,
            fn (string $possibleSourcePath) => file_exists($projectDirectory . '/' . $possibleSourcePath)
        );

        $analyseLevelProcess = $this->createAnalyseLevelProcess(
            $phpStanBinFilePath,
            $sourcePaths,
            $phpStanLevel,
            $projectDirectory
        );

        $this->handleEnvFile($envFile, $analyseLevelProcess);

        $this->symfonyStyle->writeln('Running: ' . $analyseLevelProcess->getCommandLine());
        $analyseLevelProcess->run();

        $jsonResult = $analyseLevelProcess->getOutput();
        $json = JsonLoader::loadToArray($jsonResult, $analyseLevelProcess);

        // fatal errors, they stop the analyss
        if ((int) $json['totals']['errors'] > 0) {
            throw new AnalysisFailedException(sprintf(
                'PHPStan failed on level %d with %d fatal errors: "%s"',
                $phpStanLevel,
                (int) $json['totals']['errors'],
                $jsonResult
            ));
        }

        $fileErrorCount = (int) $json['totals']['file_errors'];

        $this->symfonyStyle->writeln(sprintf('Found %d errors', $fileErrorCount));
        $this->symfonyStyle->newLine();

        return new PHPStanLevelResult($phpStanLevel, $fileErrorCount);
    }

    /**
     * @param PHPStanLevelResult[] $phpStanLevelResults
     */
    private function renderResultInTable(array $phpStanLevelResults): void
    {
        // convert to symfony table data
        $tableRows = [];
        foreach ($phpStanLevelResults as $phpStanLevelResult) {
            $tableRows[] = [$phpStanLevelResult->getLevel(), $phpStanLevelResult->getErrorCount()];
        }

        $tableStyle = new TableStyle();
        $tableStyle->setPadType(STR_PAD_LEFT);

        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->createTable()
            ->setHeaders(['Level', 'Error count'])
            ->setRows($tableRows)
            // align right
            ->setStyle($tableStyle)
            ->render();
    }

    private function handleEnvFile(?string $envFile, Process $process): void
    {
        if (! is_string($envFile)) {
            return;
        }

        if (! file_exists($envFile)) {
            throw new ShouldNotHappenException(sprintf('Env file "%s" was not found.', $envFile));
        }

        $envVariables = FileLoader::resolveEnvVariablesFromFile($envFile);
        $process->setEnv($envVariables);

        $this->symfonyStyle->note('Adding envs:');

        foreach ($envVariables as $name => $value) {
            $this->symfonyStyle->writeln(' * ' . $name . ': ' . $value);
        }

        $this->symfonyStyle->newLine();
    }

    /**
     * @param string[] $sourcePaths
     */
    private function createAnalyseLevelProcess(
        string $phpstanBinFilePath,
        array $sourcePaths,
        int $phpStanLevel,
        string $projectDirectory
    ): Process {
        return new Process(
            [$phpstanBinFilePath, 'analyse', ...$sourcePaths, '--error-format', 'json', '--level', $phpStanLevel],
            $projectDirectory,
            null,
            null,
            // timeout in seconds
            self::TIMEOUT_IN_SECONDS,
        );
    }

    private function resolvePhpStanBinFile(string $projectDirectory): string
    {
        if (file_exists($projectDirectory . '/vendor/bin/phpstan')) {
            return 'vendor/bin/phpstan';
        }

        // possible that /bin directory is used
        return 'bin/phpstan';
    }
}
