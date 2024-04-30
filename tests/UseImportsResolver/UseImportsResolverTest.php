<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\PHPStanBodyscan\Tests\AbstractTestCase;
use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Fixture\SomeFactory;
use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Source\FirstUsedClass;
use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Source\FourthUsedClass;
use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Source\SecondUsedClass;
use TomasVotruba\PHPStanBodyscan\UseImportsResolver;

final class UseImportsResolverTest extends AbstractTestCase
{
    private UseImportsResolver $useImportsResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useImportsResolver = $this->make(UseImportsResolver::class);
    }

    /**
     * @param string[] $expectedClassUsages
     */
    #[DataProvider('provideData')]
    public function test(string $filePath, array $expectedClassUsages): void
    {
        $resolvedClassUsages = $this->useImportsResolver->resolve($filePath);
        $this->assertSame($expectedClassUsages, $resolvedClassUsages);
    }

    public static function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/FileUsingOtherClasses.php', [FirstUsedClass::class, SecondUsedClass::class]];
        yield [__DIR__ . '/Fixture/FileUsesStaticCall.php', [SomeFactory::class, FourthUsedClass::class]];
    }
}
