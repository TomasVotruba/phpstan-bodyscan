<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final readonly class PHPStanConfig
{
    public function __construct(
        private string $fileContents,
        private ?string $originalFileName
    ) {
    }

    public function getFileContents(): string
    {
        return $this->fileContents;
    }

    public function getOriginalFileName(): ?string
    {
        return $this->originalFileName;
    }
}
