<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final readonly class BodyscanResult
{
    /**
     * @param LevelResult[] $levelResults
     */
    public function __construct(
        private array $levelResults
    ) {
    }

    /**
     * @return LevelResult[]
     */
    public function getLevelResults(): array
    {
        return $this->levelResults;
    }
}
