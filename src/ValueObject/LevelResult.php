<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final class LevelResult
{
    private ?int $changeToPreviousLevel = null;

    public function __construct(
        private readonly int $level,
        private readonly int $errorCount
    ) {
    }

    public function setChangeToPreviousLevel(int $changeToPreviousLevel): void
    {
        $this->changeToPreviousLevel = $changeToPreviousLevel;
    }

    public function getChangeToPreviousLevel(): ?int
    {
        return $this->changeToPreviousLevel;
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
