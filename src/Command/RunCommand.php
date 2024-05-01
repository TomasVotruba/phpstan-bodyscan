<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use TomasVotruba\PHPStanBodyscan\Exception\AnalysisFailedException;
use TomasVotruba\PHPStanBodyscan\Utils\FileLoader;

final class RunCommand extends Command
{
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
        $errorCountByLevel = [];

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

        // 2. measure phpstan levels
        for ($phpStanLevel = 0; $phpStanLevel <= $maxPhpStanLevel; ++$phpStanLevel) {
            $this->symfonyStyle->writeln(sprintf('Running PHPStan level %d', $phpStanLevel));

            $errorCountByLevel[$phpStanLevel] = $this->measureErrorCountInLevel(
                $phpStanLevel,
                $projectDirectory,
                $envFile
            );
        }

        $this->renderResultInTable($errorCountByLevel);

        return self::SUCCESS;
    }

    private function measureErrorCountInLevel(int $phpStanLevel, string $projectDirectory, ?string $envFile): int
    {
        $phpstanBinFilePath = file_exists(
            $projectDirectory . '/vendor/bin/phpstan'
        ) ? 'vendor/bin/phpstan' : 'bin/phpstan';

        // resolve source paths
        $possibleSourcePaths = ['app', 'src', 'tests'];
        $sourcePaths = array_filter(
            $possibleSourcePaths,
            fn (string $possibleSourcePath) => file_exists($projectDirectory . '/' . $possibleSourcePath)
        );

        $analyseLevelProcess = new Process(
            // with json format
            [$phpstanBinFilePath, 'analyse', ...$sourcePaths, '--error-format', 'json', '--level', $phpStanLevel],
            $projectDirectory,
            null,
            null,
            // timeout in seconds
            200,
        );

        if (is_string($envFile) && file_exists($envFile)) {
            $envVariables = FileLoader::resolveEnvVariablesFromFile($envFile);
            $analyseLevelProcess->setEnv($envVariables);

            $this->symfonyStyle->note('Adding envs:');
            $this->symfonyStyle->listing($envVariables);
        }

        die;

        $this->symfonyStyle->writeln('Running: ' . $analyseLevelProcess->getCommandLine());

        $analyseLevelProcess->run();
        $jsonResult = $analyseLevelProcess->getOutput();

        try {
            $json = json_decode($jsonResult, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new JsonException(sprintf(
                'Could not decode JSON from phpstan: "%s"',
                $jsonResult ?: $analyseLevelProcess->getErrorOutput()
            ), 0, $jsonException);
        }

        // fatal errors, they stop the analyss
        if ((int) $json['totals']['errors'] > 0) {
            throw new AnalysisFailedException(sprintf(
                'PHPStan failed on level %d with %d fatal errors: "%s"',
                $phpStanLevel,
                (int) $json['totals']['errors'],
                $jsonResult
            ));
        }

        return (int) $json['totals']['file_errors'];
    }

    /**
     * @param array<int, int> $errorCountByLevel
     */
    private function renderResultInTable(array $errorCountByLevel): void
    {
        // convert to symfony table data
        $tableRows = [];
        foreach ($errorCountByLevel as $phpstanLevel => $errorCount) {
            $tableRows[] = [$phpstanLevel, $errorCount];
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
}
