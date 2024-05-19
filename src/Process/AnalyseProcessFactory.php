<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Process;

use PHPStanBodyscan202405\Symfony\Component\Process\Process;
use TomasVotruba\PHPStanBodyscan\Utils\ComposerLoader;
final class AnalyseProcessFactory
{
    /**
     * @var int
     */
    private const TIMEOUT_IN_SECONDS = 400;
    /**
     * @param array<string, mixed> $envVariables
     */
    public function create(string $projectDirectory, int $phpStanLevel, array $envVariables) : Process
    {
        $phpStanBinFilePath = $this->resolvePhpStanBinFile($projectDirectory);
        return $this->createAnalyseLevelProcess($phpStanBinFilePath, $phpStanLevel, $projectDirectory, $envVariables);
    }
    /**
     * @param array<string, mixed> $envVariables
     */
    private function createAnalyseLevelProcess(string $phpstanBinFilePath, int $phpStanLevel, string $projectDirectory, array $envVariables) : Process
    {
        $command = [$phpstanBinFilePath, 'analyse', '--error-format', 'json', '--level', $phpStanLevel, '--configuration', 'phpstan-bodyscan.neon'];
        return new Process(
            $command,
            $projectDirectory,
            $envVariables,
            null,
            // timeout in seconds
            self::TIMEOUT_IN_SECONDS
        );
    }
    private function resolvePhpStanBinFile(string $projectDirectory) : string
    {
        $vendorBinDirectory = ComposerLoader::getBinDirectory($projectDirectory);
        if (\file_exists($vendorBinDirectory . '/phpstan')) {
            return $vendorBinDirectory . '/phpstan';
        }
        if (\file_exists($projectDirectory . '/vendor/bin/phpstan')) {
            return 'vendor/bin/phpstan';
        }
        // possible that /bin directory is used
        return 'bin/phpstan';
    }
}
