<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan;

use PHPStanBodyscan202406\Symfony\Component\Process\Process;
final class DependencyInstaller
{
    public function ensureTypeCoverageIsInstalled(string $projectDirectory) : void
    {
        $typeCoveragePackageDirectory = $projectDirectory . '/vendor/tomasvotruba/type-coverage';
        if (\file_exists($typeCoveragePackageDirectory)) {
            // installed! all good
            return;
        }
        $requirePHPStanProcess = new Process(['composer', 'require', 'tomasvotruba/type-coverage', '--dev'], $projectDirectory);
        $requirePHPStanProcess->mustRun();
        // @todo cleanup after run
    }
}
