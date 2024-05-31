<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan;

use Nette\Neon\Neon;
use TomasVotruba\PHPStanBodyscan\ValueObject\PHPStanConfig;

/**
 * @see \TomasVotruba\PHPStanBodyscan\Tests\PHPStanConfigFactory\PHPStanConfigFactoryTest
 */
final class PHPStanConfigFactory
{
    /**
     * @var string[]
     */
    private const POSSIBLE_SOURCE_PATHS = ['app', 'config', 'lib', 'src', 'tests'];

    /**
     * @var string[]
     */
    private const PHPSTAN_FILE_NAMES = ['phpstan.neon', 'phpstan.neon.dist'];

    /**
     * @param array<string, mixed[]> $extraConfiguration
     */
    public function create(string $projectDirectory, array $extraConfiguration = [], bool $bare = false): PHPStanConfig
    {
        $existingPHPStanFile = null;

        foreach (self::PHPSTAN_FILE_NAMES as $phpstanFileName) {
            if (file_exists($projectDirectory . '/' . $phpstanFileName)) {
                $existingPHPStanFile = $projectDirectory . '/' . $phpstanFileName;
                break;
            }
        }

        // no config found? we have to create it
        if ($existingPHPStanFile === null) {
            $phpstanConfiguration = $this->createBasicPHPStanConfiguration($projectDirectory);
            $phpStanNeonContents = $this->dumpNeonToString($phpstanConfiguration);
            return new PHPStanConfig($phpStanNeonContents);
        }

        // keep original setup
        if ($bare === false) {
            $phpStanNeonContents = $this->loadFileAndMergeParameters($existingPHPStanFile, [
                'parameters' => [
                    // disable ignored error reporting, to make no fatal errors
                    'reportUnmatchedIgnoredErrors' => false,
                ],
            ]);
            return new PHPStanConfig($phpStanNeonContents);
        }

        $phpstanConfiguration = $this->createBarePHPStanConfiguration($existingPHPStanFile);
        $phpstanConfiguration = array_merge_recursive($phpstanConfiguration, $extraConfiguration);

        $phpstanNeon = $this->dumpNeonToString($phpstanConfiguration);
        return new PHPStanConfig($phpstanNeon);
    }

    /**
     * @return mixed[]
     */
    private function createBarePHPStanConfiguration(string $projectPHPStanFile): array
    {
        // make use of existing PHPStan paths
        $projectPHPStan = Neon::decodeFile($projectPHPStanFile);

        return [
            'parameters' => [
                'paths' => $projectPHPStan['parameters']['paths'] ?? [],
                'excludePaths' => $projectPHPStan['parameters']['excludePaths'] ?? [],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function createBasicPHPStanConfiguration(string $projectDirectory): array
    {
        // in case of no config
        $sourcePaths = array_filter(
            self::POSSIBLE_SOURCE_PATHS,
            static fn (string $possibleSourcePath): bool => file_exists(
                $projectDirectory . '/' . $possibleSourcePath
            )
        );

        $sourcePaths = array_values($sourcePaths);

        return [
            'parameters' => [
                'paths' => $sourcePaths,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $extraContents
     */
    private function loadFileAndMergeParameters(string $existingPHPStanFile, array $extraContents): string
    {
        $neon = Neon::decodeFile($existingPHPStanFile);
        $neon = array_merge_recursive($neon, $extraContents);

        return $this->dumpNeonToString($neon);
    }

    /**
     * @param array<string, mixed> $phpstanConfiguration
     */
    private function dumpNeonToString(array $phpstanConfiguration): string
    {
        $encodedNeon = Neon::encode($phpstanConfiguration, true, '    ');
        return trim($encodedNeon) . PHP_EOL;
    }
}
