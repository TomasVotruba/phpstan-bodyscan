<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\ValueObject;

use Webmozart\Assert\Assert;

final readonly class BodyscanResult
{
    /**
     * @param LevelResult[] $levelResults
     */
    public function __construct(
        private array $levelResults
    ) {
        Assert::allIsInstanceOf($levelResults, LevelResult::class);
        Assert::notEmpty($levelResults);
    }

    /**
     * @return LevelResult[]
     */
    public function getLevelResults(): array
    {
        $this->computeChangesToPreviousLevels();

        return $this->levelResults;
    }

    private function computeChangesToPreviousLevels(): void
    {
        $previousLevelResult = null;

        foreach ($this->levelResults as $levelResult) {
            if ($previousLevelResult instanceof LevelResult) {
                $changeToPreviousLevel = $levelResult->getErrorCount() - $previousLevelResult->getErrorCount();
                $levelResult->setChangeToPreviousLevel($changeToPreviousLevel);
            }

            $previousLevelResult = $levelResult;
        }
    }
}
