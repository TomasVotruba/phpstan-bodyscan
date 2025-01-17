<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Process;

use PHPStanBodyscan202501\Symfony\Component\Process\Process;
use TomasVotruba\PHPStanBodyscan\Utils\ComposerLoader;
final class AnalyseProcessFactory
{
    /**
     * @var int
     */
    private const TIMEOUT_IN_SECONDS = 400;
    /**
     * @var string
     */
    private const MEMORY_LIMIT = '16G';
    /**
     * @param array<string, mixed> $envVariables
     */
    public function create(string $projectDirectory, int $phpStanLevel, array $envVariables) : Process
    {
        $phpStanBinFilePath = ComposerLoader::getPHPStanBinFile($projectDirectory);
        $command = [
            $phpStanBinFilePath,
            'analyse',
            '--error-format',
            'json',
            // increase default memory limit to allow analyse huge projects
            '--memory-limit',
            self::MEMORY_LIMIT,
            '--level',
            $phpStanLevel,
            '--configuration',
            'phpstan-bodyscan.neon',
        ];
        return new Process($command, $projectDirectory, $envVariables, null, self::TIMEOUT_IN_SECONDS);
    }
}
