<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Fixture;

use TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\Source\FourthUsedClass;

final class FileUsesStaticCall
{
    public function other(): FourthUsedClass
    {
        return SomeFactory::create();
    }
}
