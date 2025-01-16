<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final class PHPStanConfig
{
    /**
     * @readonly
     * @var string
     */
    private $fileContents;
    public function __construct(string $fileContents)
    {
        $this->fileContents = $fileContents;
    }
    public function getFileContents() : string
    {
        return $this->fileContents;
    }
}
