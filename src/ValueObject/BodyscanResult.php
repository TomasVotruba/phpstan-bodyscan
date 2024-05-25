<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final readonly class BodyscanResult
{
    /**
     * @param LevelResult[] $levelResults
     * @param TypeCoverageResult[]|null $typeCoverageResults
     */
    public function __construct(
        private array $levelResults,
        private ?array $typeCoverageResults,
    ) {
    }

    /**
     * @return LevelResult[]
     */
    public function getLevelResults(): array
    {
        return $this->levelResults;
    }

    /**
     * @return TypeCoverageResult[]|null
     */
    public function getTypeCoverageResults(): ?array
    {
        return $this->typeCoverageResults;
    }
}
