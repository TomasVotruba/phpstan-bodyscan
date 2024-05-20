<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final readonly class LevelResult
{
    public function __construct(
        private int $level,
        private int $errorCount
    ) {
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }
}
