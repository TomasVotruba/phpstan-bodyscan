<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Fixture;

use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Source\FourthUsedClass;

final class SomeFactory
{
    public static function create(): FourthUsedClass
    {
        return new FourthUsedClass();
    }
}
