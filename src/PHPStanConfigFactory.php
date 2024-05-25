<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan;

use Nette\Neon\Neon;

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
     * @param array<string, mixed[]> $extraConfiguration
     */
    public function create(string $projectDirectory, array $extraConfiguration = []): string
    {
        $projectPHPStanFile = $projectDirectory . '/phpstan.neon';

        $phpstanConfiguration = $this->resolvePHPStanConfiguration($projectPHPStanFile, $projectDirectory);

        $phpstanConfiguration = array_merge_recursive($phpstanConfiguration, $extraConfiguration);

        $encodedNeon = Neon::encode($phpstanConfiguration, true, '    ');
        return trim($encodedNeon) . PHP_EOL;
    }

    /**
     * @return mixed[]
     */
    private function resolvePHPStanConfiguration(string $projectPHPStanFile, string $projectDirectory): array
    {
        if (! file_exists($projectPHPStanFile)) {
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

        // make use of existing PHPStan paths
        $projectPHPStan = Neon::decodeFile($projectPHPStanFile);

        $parameters = [];
        $parameters['parameters']['paths'] = $projectPHPStan['parameters']['paths'] ?? [];
        $parameters['parameters']['excludePaths'] = $projectPHPStan['parameters']['excludePaths'] ?? [];

        return $parameters;
    }
}
