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
    private const PHPSTAN_FILE_NAMES = ['phpstan.neon', 'phpstan.neon.dist', 'phpstan.php', 'phpstan.php.dist'];

    /**
     * @param array<string, mixed[]> $extraConfiguration
     */
    public function create(string $projectDirectory, array $extraConfiguration = [], bool $bare = false): PHPStanConfig
    {
        $existingPHPStanFile = null;
        $phpstanFileName = null;
        foreach (self::PHPSTAN_FILE_NAMES as $phpstanFileName) {
            if (file_exists($projectDirectory . '/' . $phpstanFileName)) {
                $existingPHPStanFile = $projectDirectory . '/' . $phpstanFileName;
                break;
            }
        }

        // no config found? we have to create it
        if ($existingPHPStanFile === null) {
            $phpstanConfiguration = $this->createBasicPHPStanConfiguration($projectDirectory);
            $phpstanNeon = $this->dumpToNeon($phpstanConfiguration);
            return new PHPStanConfig($phpstanNeon, null);
        }

        // keep original setup
        if ($bare === false) {
            return new PHPStanConfig(file_get_contents($existingPHPStanFile), $phpstanFileName);
        }

        $phpstanConfiguration = $this->createBarePHPStanConfiguration($existingPHPStanFile);
        $phpstanConfiguration = array_merge_recursive($phpstanConfiguration, $extraConfiguration);

        $phpstanNeon = $this->dumpToNeon($phpstanConfiguration);
        return new PHPStanConfig($phpstanNeon, $phpstanFileName);
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

    private function dumpToNeon(array $phpstanConfiguration): string
    {
        $encodedNeon = Neon::encode($phpstanConfiguration, true, '    ');
        return trim($encodedNeon) . PHP_EOL;
    }
}
