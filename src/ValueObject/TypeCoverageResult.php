<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final readonly class TypeCoverageResult
{
    /**
     * @param TypeCoverage[] $typeCoverages
     */
    public function __construct(
        private array $typeCoverages,
    ) {
    }

    /**
     * @return TypeCoverage[]
     */
    public function getTypeCoverages(): array
    {
        return $this->typeCoverages;
    }
}
