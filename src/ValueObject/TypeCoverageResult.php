<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final class TypeCoverageResult
{
    /**
     * @var TypeCoverage[]
     * @readonly
     */
    private $typeCoverages;
    /**
     * @param TypeCoverage[] $typeCoverages
     */
    public function __construct(array $typeCoverages)
    {
        $this->typeCoverages = $typeCoverages;
    }
    /**
     * @return TypeCoverage[]
     */
    public function getTypeCoverages() : array
    {
        return $this->typeCoverages;
    }
}
