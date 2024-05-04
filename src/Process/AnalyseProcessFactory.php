<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Process;

use Symfony\Component\Process\Process;

final class AnalyseProcessFactory
{
    /**
     * @var int
     */
    private const TIMEOUT_IN_SECONDS = 400;

    /**
     * @var string[]
     */
    private const POSSIBLE_SOURCE_PATHS = ['app', 'src', 'tests'];

    /**
     * @param array<string, mixed> $envVariables
     */
    public function create(string $projectDirectory, int $phpStanLevel, array $envVariables): Process
    {
        $phpStanBinFilePath = $this->resolvePhpStanBinFile($projectDirectory);

        // resolve source paths
        $sourcePaths = array_filter(
            self::POSSIBLE_SOURCE_PATHS,
            static fn (string $possibleSourcePath): bool => file_exists($projectDirectory . '/' . $possibleSourcePath)
        );

        return $this->createAnalyseLevelProcess(
            $phpStanBinFilePath,
            $sourcePaths,
            $phpStanLevel,
            $projectDirectory,
            $envVariables
        );
    }

    /**
     * @param string[] $sourcePaths
     * @param array<string, mixed> $envVariables
     */
    private function createAnalyseLevelProcess(
        string $phpstanBinFilePath,
        array $sourcePaths,
        int $phpStanLevel,
        string $projectDirectory,
        array $envVariables
    ): Process {
        $command = [
            $phpstanBinFilePath,
            'analyse',
            ...$sourcePaths,
            '--error-format',
            'json',
            '--level',
            $phpStanLevel,
            '--configuration',
            'phpstan-bodyscan.neon',
        ];

        return new Process(
            $command,
            $projectDirectory,
            $envVariables,
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
