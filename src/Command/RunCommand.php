<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use Nette\Utils\Strings;
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
use TomasVotruba\PHPStanBodyscan\Utils\ComposerLoader;
use TomasVotruba\PHPStanBodyscan\Utils\FileLoader;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;

final class RunCommand extends Command
{
    /**
     * @var string
     * @see https://regex101.com/r/t64u6i/1
     */
    private const TYPE_COVERAGE_MESSAGE_REGEX = '#(?<category>.*?) coverage is (?<relative>.*?) % out of (?<total_count>\d+) possible#';

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

        $this->addOption('type-coverage', null, InputOption::VALUE_NONE, 'Include type coverage data');

        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'Path to project .env file');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Show result in JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDirectory = $input->getArgument('directory');

        $minPhpStanLevel = (int) $input->getOption('min-level');
        $maxPhpStanLevel = (int) $input->getOption('max-level');
        $isJson = (bool) $input->getOption('json');
        $includeTypeCoverage = (bool) $input->getOption('type-coverage');

        // silence output till the end to avoid invalid json format
        if ($isJson) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        $vendorBinDirectory = ComposerLoader::getBinDirectory($projectDirectory);

        // 1. is phpstan installed in the project?
        $this->dependencyInstaller->ensurePHPStanIsInstalled($projectDirectory, $vendorBinDirectory);

        $envVariables = $this->loadEnvVariables($input);

        // 1. prepare empty phpstan config
        // no baselines, ignores etc. etc :)
        $phpstanConfiguration = $this->phpStanConfigFactory->create($projectDirectory);
        file_put_contents($projectDirectory . '/phpstan-bodyscan.neon', $phpstanConfiguration);

        $levelResults = [];

        // 2. measure phpstan levels
        for ($phpStanLevel = $minPhpStanLevel; $phpStanLevel <= $maxPhpStanLevel; ++$phpStanLevel) {
            $this->symfonyStyle->section(sprintf('Running PHPStan level %d', $phpStanLevel));

            $levelResults[] = $this->measureErrorCountInLevel($phpStanLevel, $projectDirectory, $envVariables);

            $this->symfonyStyle->newLine();
        }

        $typeCoverageResult = $this->measureTypeCoverage($includeTypeCoverage, $projectDirectory);
        $bodyscanResult = new BodyscanResult($levelResults, $typeCoverageResult);

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

        $this->symfonyStyle->writeln('Running: <fg=green>' . $analyseLevelProcess->getCommandLine() . '</>');
        $analyseLevelProcess->run();

        $result = $this->phpStanResultResolver->resolve($analyseLevelProcess);

        $fileErrorCount = (int) $result['totals']['file_errors'];
        $this->symfonyStyle->writeln(sprintf('Found %d errors', $fileErrorCount));
        $this->symfonyStyle->newLine();

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

    /**
     * @return TypeCoverageResult[]|null
     */
    private function measureTypeCoverage(bool $includeTypeCoverage, mixed $projectDirectory): ?array
    {
        if ($includeTypeCoverage === false) {
            return null;
        }

        $this->dependencyInstaller->ensureTypeCoverageIsInstalled($projectDirectory);

        // the extension config should be loaded with phpstan extension installed,
        // @todo resolve manually here if missing
        $typeCoveragePhpstanConfiguration = $this->phpStanConfigFactory->create($projectDirectory, [
            'parameters' => [
                'type_coverage' => [
                    'measure' => true,
                ],
                'customRulesetUsed' => true,
            ],
        ]);
        file_put_contents($projectDirectory . '/phpstan-bodyscan.neon', $typeCoveragePhpstanConfiguration);

        $typeCoverageProcess = $this->analyseProcessFactory->createTypeCoverageProcess($projectDirectory);

        $this->symfonyStyle->writeln('Running: <fg=green>' . $typeCoverageProcess->getCommandLine() . '</>');
        $typeCoverageProcess->run();

        $result = $this->phpStanResultResolver->resolve($typeCoverageProcess);

        $typeCoverageResults = [];

        foreach ($result['files'] as $file => $fileErrors) {
            // we look only for global data
            if ($file !== 'N/A') {
                continue;
            }

            foreach ($fileErrors['messages'] as $errorMessage) {
                // var_dump($errorMessage['message']);

                $match = Strings::match(
                    $errorMessage['message'],
                    '#(?<category>.*?) coverage is (?<relative>.*?) % out of (?<total_count>\d+) possible#'
                );
                if ($match === null) {
                    continue;
                }

                $typeCoverageResults[] = new TypeCoverageResult(
                    (string) $match['category'],
                    (float) $match['relative'],
                    (int) $match['total_count'],
                );
            }
        }

<<<<<<< HEAD
        die;

<<<<<<< HEAD
        return null;
=======
        foreach ($result['files'] as $file => $fileErrors) {
            // we look only for global data
            if ($file !== 'N/A') {
                continue;
            }

            foreach ($fileErrors['messages'] as $errorMessage) {
                $match = Strings::match($errorMessage['message'], self::TYPE_COVERAGE_MESSAGE_REGEX);
                if ($match === null) {
                    continue;
                }

                $typeCoverageResults[] = new TypeCoverageResult(
                    lcfirst($match['category']),
                    (float) $match['relative'],
                    (int) $match['total_count'],
                );
            }
        }

        return $typeCoverageResults;
>>>>>>> 5de6845 (fixup! misc)
=======
        return $typeCoverageResults;
>>>>>>> feda13b (misc)
    }
}
