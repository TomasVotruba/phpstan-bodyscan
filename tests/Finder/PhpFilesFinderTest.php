<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Tests\Finder;

use TomasVotruba\PHPStanBodyscan\Finder\PhpFilesFinder;
use TomasVotruba\PHPStanBodyscan\Tests\AbstractTestCase;

final class PhpFilesFinderTest extends AbstractTestCase
{
    private PhpFilesFinder $phpFilesFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpFilesFinder = $this->make(PhpFilesFinder::class);
    }

    public function test(): void
    {
        $phpFiles = $this->phpFilesFinder->findPhpFiles([__DIR__ . '/Fixture'], ['php', 'phtml']);
        $this->assertCount(4, $phpFiles);

        $phpFiles = $this->phpFilesFinder->findPhpFiles([__DIR__ . '/Fixture'], ['php']);
        $this->assertCount(3, $phpFiles);
    }
}
