<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Fixture;

use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Source\FirstUsedClass;
use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Source\SecondUsedClass;

final class FileUsingOtherClasses
{
    public function run(FirstUsedClass $firstUsedClass): SecondUsedClass
    {
        return new SecondUsedClass();
    }
}
