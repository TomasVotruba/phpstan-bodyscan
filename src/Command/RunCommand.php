<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use Entropy\Console\Contract\DefaultCommandInterface;
use Entropy\Console\Enum\ExitCode;
use Entropy\Console\Output\OutputPrinter;
use TomasVotruba\PHPStanBodyscan\Logger;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter;
use TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory;
use TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory;
use TomasVotruba\PHPStanBodyscan\Process\PHPStanResultResolver;
use TomasVotruba\PHPStanBodyscan\Utils\FileLoader;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult;
use Webmozart\Assert\Assert;

final readonly class RunCommand implements DefaultCommandInterface
{
    public function __construct(
        private OutputPrinter $outputPrinter,
        private AnalyseProcessFactory $analyseProcessFactory,
        private PHPStanConfigFactory $phpStanConfigFactory,
        private JsonOutputFormatter $jsonOutputFormatter,
        private TableOutputFormatter $tableOutputFormatter,
        private PHPStanResultResolver $phpStanResultResolver
    ) {
    }

    public function getName(): string
    {
        return 'run';
    }

    public function getDescription(): string
    {
        return 'Check classes that are not used in any config and in the code';
    }

    /**
     * @option $minLevel
     * @option $maxLevel
     * @option $envFile
     * @option $json
     * @option $bare
     *
     * @param int $minLevel Min PHPStan level to run
     * @param int $maxLevel Max PHPStan level to run
     * @param string|null $envFile Path to project .env file
     * @param bool $json Show result in JSON
     * @param bool $bare Without any extensions, without ignores, without baselines, just pure PHPStan
     * @return ExitCode::*
     */
    public function run(
        int $minLevel = 0,
        int $maxLevel = 8,
        ?string $envFile = null,
        bool $json = false,
        bool $bare = false
    ): int {
        /** @var string $projectDirectory */
        $projectDirectory = getcwd();

        Assert::lessThanEq($minLevel, $maxLevel);

        $envVariables = $this->loadEnvVariables($envFile, $json);

        // 1. prepare empty phpstan config
        // no baselines, ignores etc. etc :)
        $phpstanConfig = $this->phpStanConfigFactory->create($projectDirectory, [], $bare);
        file_put_contents($projectDirectory . '/phpstan-bodyscan.neon', $phpstanConfig->getFileContents());

        $levelResults = [];

        $phpstanExtensionFile = $projectDirectory . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php';
        $areExtensionsDisabled = false;

        if ($bare && file_exists($phpstanExtensionFile)) {
            // temporarily disable project PHPStan extensions
            $this->writeln('Disabling PHPStan extensions...', $json);
            rename($phpstanExtensionFile, $phpstanExtensionFile . '.bak');
            $areExtensionsDisabled = true;
        }

        // 2. measure phpstan levels
        for ($phpStanLevel = $minLevel; $phpStanLevel <= $maxLevel; ++$phpStanLevel) {
            $levelMessage = sprintf('Running PHPStan level %d%s', $phpStanLevel, $bare ? ' without extensions' : '');
            $this->writeln($levelMessage . '...', $json);

            $levelResult = $this->measureErrorCountInLevel($phpStanLevel, $projectDirectory, $envVariables);
            $levelResults[] = $levelResult;

            $this->writeln(sprintf($levelMessage . ': found %d errors', $levelResult->getErrorCount()), $json);
        }

        if ($areExtensionsDisabled) {
            // restore PHPStan extension file
            $this->writeln('Restoring PHPStan extensions...', $json);
            rename($phpstanExtensionFile . '.bak', $phpstanExtensionFile);
        }

        $bodyscanResult = new BodyscanResult($levelResults);

        // 3. tidy up temporary config
        unlink($projectDirectory . '/phpstan-bodyscan.neon');

        if ($json) {
            $this->jsonOutputFormatter->outputResult($bodyscanResult);
        } else {
            $this->tableOutputFormatter->outputResult($bodyscanResult);
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @param array<string, mixed> $envVariables
     */
    private function measureErrorCountInLevel(
        int $phpStanLevel,
        string $projectDirectory,
        array $envVariables
    ): LevelResult {
        $process = $this->analyseProcessFactory->create($projectDirectory, $phpStanLevel, $envVariables);

        $process->run();

        $result = $this->phpStanResultResolver->resolve($process);
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
    private function loadEnvVariables(?string $envFile, bool $isJson): array
    {
        if ($envFile === null) {
            return [];
        }

        $this->writeln(sprintf('Adding envs from "%s" file:', $envFile), $isJson);

        return FileLoader::resolveEnvVariablesFromFile($envFile);
    }

    /**
     * Progress output would break the JSON payload, so it is skipped in JSON mode.
     */
    private function writeln(string $message, bool $isJson): void
    {
        if ($isJson) {
            return;
        }

        $this->outputPrinter->writeln($message);
    }
}
