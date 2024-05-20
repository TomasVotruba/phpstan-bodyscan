<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Contract;

use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
interface OutputFormatterInterface
{
    public function outputResult(BodyscanResult $bodyscanResult) : void;
}
