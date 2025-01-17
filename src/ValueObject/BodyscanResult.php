<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\ValueObject;

use PHPStanBodyscan202501\Webmozart\Assert\Assert;
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
        Assert::allIsInstanceOf($levelResults, \TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult::class);
        Assert::notEmpty($levelResults);
    }
    /**
     * @return LevelResult[]
     */
    public function getLevelResults() : array
    {
        $this->computeChangesToPreviousLevels();
        return $this->levelResults;
    }
    private function computeChangesToPreviousLevels() : void
    {
        $previousLevelResult = null;
        foreach ($this->levelResults as $levelResult) {
            if ($previousLevelResult instanceof \TomasVotruba\PHPStanBodyscan\ValueObject\LevelResult) {
                $changeToPreviousLevel = $levelResult->getErrorCount() - $previousLevelResult->getErrorCount();
                $levelResult->setChangeToPreviousLevel($changeToPreviousLevel);
            }
            $previousLevelResult = $levelResult;
        }
    }
}
