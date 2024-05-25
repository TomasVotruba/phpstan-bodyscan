<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final readonly class TypeCoverage
{
    public function __construct(
        private string $category,
        private float $relative,
        private int $totalCount
    ) {
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getRelative(): float
    {
        return $this->relative;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
