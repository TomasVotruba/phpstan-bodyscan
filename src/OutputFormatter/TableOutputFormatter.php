<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;

final readonly class TableOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private SymfonyStyle $symfonyStyle,
    ) {
    }

    public function outputResult(BodyscanResult $bodyscanResult): void
    {
        // convert to symfony table data
        $tableRows = $this->createRawData($bodyscanResult);

        $tableStyle = new TableStyle();
        $tableStyle->setPadType(STR_PAD_LEFT);

        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->createTable()
            ->setHeaders(['Level', 'Error count'])
            ->setRows($tableRows)
            // align right
            ->setStyle($tableStyle)
            ->render();
    }

    public function outputTypeCoverageResult(TypeCoverageResult $typeCoverageResult): void
    {
        $this->symfonyStyle->title('Type Coverage results');

        foreach ($typeCoverageResult->getTypeCoverages() as $typeCoverage) {
            $this->symfonyStyle->writeln(sprintf(
                '%s coverage is %.1f %%, out of %d items total',
                ucfirst($typeCoverage->getCategory()),
                $typeCoverage->getRelative(),
                $typeCoverage->getTotalCount(),
            ));
        }

        $this->symfonyStyle->newLine();
    }

    /**
     * @return mixed[]
     */
    private function createRawData(BodyscanResult $bodyscanResult): array
    {
        $tableRows = [];
        foreach ($bodyscanResult->getLevelResults() as $levelResult) {
            $tableRows[] = [$levelResult->getLevel(), $levelResult->getErrorCount()];
        }

        return $tableRows;
    }
}
