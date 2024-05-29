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
        // add relative to previous level
        $previousLevelResult = null;
        foreach ($this->levelResults as $levelResult) {
            if ($previousLevelResult instanceof LevelResult) {
                $changeToPreviousLevel = $levelResult->getErrorCount() - $previousLevelResult->getErrorCount();
                $levelResult->setChangeToPreviousLevel($changeToPreviousLevel);
            }

            $previousLevelResult = $levelResult;
        }

        return $this->levelResults;
    }
}
