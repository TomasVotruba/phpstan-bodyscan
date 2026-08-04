<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use Entropy\Console\ConsoleTable\ConsoleTable;
use Entropy\Console\Output\OutputPrinter;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;

final readonly class TableOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private OutputPrinter $outputPrinter,
        private ConsoleTable $consoleTable,
    ) {
    }

    public function outputResult(BodyscanResult $bodyscanResult): void
    {
        $tableRows = $this->createRawData($bodyscanResult);

        $this->outputPrinter->newline(2);

        $this->consoleTable->render(['Level', 'Error count', 'Increment'], $tableRows);
    }

    public function outputTypeCoverageResult(TypeCoverageResult $typeCoverageResult): void
    {
        $this->outputPrinter->title('Type Coverage results');

        foreach ($typeCoverageResult->getTypeCoverages() as $typeCoverage) {
            $this->outputPrinter->writeln(sprintf(
                '%s coverage is %.1f %%, out of %d items total',
                ucfirst($typeCoverage->getCategory()),
                $typeCoverage->getRelative(),
                $typeCoverage->getTotalCount(),
            ));
        }

        $this->outputPrinter->newline();
    }

    /**
     * @return string[][]
     */
    private function createRawData(BodyscanResult $bodyscanResult): array
    {
        $tableRows = [];
        foreach ($bodyscanResult->getLevelResults() as $levelResult) {
            $increase = $levelResult->getChangeToPreviousLevel() ? '+ ' . $levelResult->getChangeToPreviousLevel() : '-';

            $tableRows[] = [(string) $levelResult->getLevel(), (string) $levelResult->getErrorCount(), $increase];
        }

        return $tableRows;
    }
}
