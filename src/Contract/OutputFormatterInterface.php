<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Contract;

use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;
interface OutputFormatterInterface
{
    public function outputResult(BodyscanResult $bodyscanResult) : void;
    public function outputTypeCoverageResult(TypeCoverageResult $typeCoverageResult) : void;
}
