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

    public function create(string $projectDirectory, int $phpStanLevel): Process
    {
        $phpStanBinFilePath = $this->resolvePhpStanBinFile($projectDirectory);

        // resolve source paths
        $sourcePaths = array_filter(
            self::POSSIBLE_SOURCE_PATHS,
            fn (string $possibleSourcePath) => file_exists($projectDirectory . '/' . $possibleSourcePath)
        );

        return $this->createAnalyseLevelProcess(
            $phpStanBinFilePath,
            $sourcePaths,
            $phpStanLevel,
            $projectDirectory
        );
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
        $command = [
            $phpstanBinFilePath,
            'analyse',
            ...$sourcePaths,
            '--error-format',
            'json',
            '--level',
            $phpStanLevel,
            '--config',
            'phpstan-bodyscan.neon',
        ];

        return new Process(
            $command,
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
