<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Tests\PHPStanConfigFactory;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory;

final class PHPStanConfigFactoryTest extends TestCase
{
    private PHPStanConfigFactory $phpStanConfigFactory;

    protected function setUp(): void
    {
        $this->phpStanConfigFactory = new PHPStanConfigFactory();
    }

    #[DataProvider('provideData')]
    public function test(string $projectDirectory, string $expectedPHPStanConfigFile): void
    {
        $phpstanNeon = $this->phpStanConfigFactory->create($projectDirectory);

        $this->assertStringEqualsFile($expectedPHPStanConfigFile, $phpstanNeon->getFileContents());
    }

    public static function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/some-project', __DIR__ . '/Fixture/expected-some-project-phpstan.neon'];
        yield [__DIR__ . '/Fixture/empty-project', __DIR__ . '/Fixture/expected-empty-project-phpstan.neon'];
    }
}
