<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class DependencyInstaller
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    ) {
    }

    public function ensurePHPStanIsInstalled(string $projectDirectory, string $vendorBinDirectory): void
    {
        if (! file_exists($projectDirectory . $vendorBinDirectory . '/phpstan')) {
            $this->symfonyStyle->note('PHPStan not found in the project... installing');
            $requirePHPStanProcess = new Process([
                'composer',
                'require',
                'phpstan/phpstan',
                '--dev',
            ], $projectDirectory);

            $requirePHPStanProcess->mustRun();
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

        $this->symfonyStyle->note('Type coverage not found in the project... installing');

        $requirePHPStanProcess = new Process([
            'composer',
            'require',
            'tomasvotruba/type-coverage',
            '--dev',
        ], $projectDirectory);

        $requirePHPStanProcess->mustRun();
    }
}
