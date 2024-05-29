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
        // add relative to previous level
        $previousLevelResult = null;
        foreach ($this->levelResults as $levelResult) {
            if ($previousLevelResult instanceof \TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult) {
                $changeToPreviousLevel = $levelResult->getErrorCount() - $previousLevelResult->getErrorCount();
                $levelResult->setChangeToPreviousLevel($changeToPreviousLevel);
            }
            $previousLevelResult = $levelResult;
        }
        return $this->levelResults;
    }
}
