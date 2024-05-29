<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final class BodyscanResult
{
    /**
     * @var LevelResult[]
     * @readonly
     */
    private $levelResults;
    /**
     * @param LevelResult[] $levelResults
     */
    public function __construct(array $levelResults)
    {
        $this->levelResults = $levelResults;
    }
    /**
     * @return LevelResult[]
     */
    public function getLevelResults() : array
    {
        return $this->levelResults;
    }
}
