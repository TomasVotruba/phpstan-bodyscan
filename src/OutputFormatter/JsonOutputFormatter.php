<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use Entropy\Console\Output\OutputPrinter;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;

final readonly class JsonOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private OutputPrinter $outputPrinter
    ) {
    }

    public function outputTypeCoverageResult(TypeCoverageResult $typeCoverageResult): void
    {
        $rawData = [];

        foreach ($typeCoverageResult->getTypeCoverages() as $typeCoverageResult) {
            $rawData[] = [
                'category' => $typeCoverageResult->getCategory(),
                'relative_covered' => $typeCoverageResult->getRelative(),
                'total_count' => $typeCoverageResult->getTotalCount(),
            ];
        }

        $this->printArrayAsJson($rawData);
    }

    public function outputResult(BodyscanResult $bodyscanResult): void
    {
        $rawData = [];

        foreach ($bodyscanResult->getLevelResults() as $levelResult) {
            $rawData[] = [
                'level' => $levelResult->getLevel(),
                'error_count' => $levelResult->getErrorCount(),
                'increment_count' => $levelResult->getChangeToPreviousLevel(),
            ];
        }

        $this->printArrayAsJson($rawData);
    }

    /**
     * @param mixed[] $rawData
     */
    private function printArrayAsJson(array $rawData): void
    {
        $jsonOutput = json_encode($rawData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->outputPrinter->writeln($jsonOutput);
    }
}
