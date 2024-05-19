<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan;

use PHPStanBodyscan202405\Nette\Neon\Neon;
/**
 * @see \TomasVotruba\PHPStanBodyscan\Tests\PHPStanConfigFactory\PHPStanConfigFactoryTest
 */
final class PHPStanConfigFactory
{
    public function create(string $projectDirectory) : string
    {
        $projectPHPStanFile = $projectDirectory . '/phpstan.neon';
        // make use of existing phpstan paths if found
        if (!\file_exists($projectPHPStanFile)) {
            return \PHP_EOL;
        }
        $projectPHPStan = Neon::decodeFile($projectPHPStanFile);
        $configuration = ['parameters' => ['paths' => $projectPHPStan['parameters']['paths'] ?? [], 'excludePaths' => $projectPHPStan['parameters']['excludePaths'] ?? []]];
        $encodedNeon = Neon::encode($configuration, \true, '    ');
        return \trim($encodedNeon) . \PHP_EOL;
    }
}
