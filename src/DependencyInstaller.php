<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use TomasVotruba\PHPStanBodyscan\Utils\ComposerLoader;

final class DependencyInstaller
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    ) {
    }

    public function ensurePHPStanIsInstalled(string $projectDirectory): void
    {
        if (! file_exists($projectDirectory . '/' . ComposerLoader::getPHPStanBinFile($projectDirectory))) {
            $this->symfonyStyle->note('PHPStan not found in the project... installing');

            $process = new Process(['composer', 'require', 'phpstan/phpstan', '--dev'], $projectDirectory);
            $process->mustRun();

            return;
        }

        $this->symfonyStyle->note('PHPStan found in the project, lets run it!');
        $this->symfonyStyle->newLine(2);
    }

    public function ensureTypeCoverageIsInstalled(string $projectDirectory): void
    {
        $typeCoveragePackageDirectory = $projectDirectory . '/vendor/tomasvotruba/type-coverage';
        if (file_exists($typeCoveragePackageDirectory)) {
            // installed! all good
            return;
        }

        $requirePHPStanProcess = new Process([
            'composer',
            'require',
            'tomasvotruba/type-coverage',
            '--dev',
        ], $projectDirectory);

        $requirePHPStanProcess->mustRun();

        // @todo cleanup after run
    }
}
