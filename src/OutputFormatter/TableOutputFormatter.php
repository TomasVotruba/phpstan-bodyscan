<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use PHPStanBodyscan202501\Symfony\Component\Console\Helper\TableStyle;
use PHPStanBodyscan202501\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;
final class TableOutputFormatter implements OutputFormatterInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function outputResult(BodyscanResult $bodyscanResult) : void
    {
        // convert to symfony table data
        $tableRows = $this->createRawData($bodyscanResult);
        $tableStyle = new TableStyle();
        $tableStyle->setPadType(\STR_PAD_LEFT);
        $this->symfonyStyle->newLine(2);
        $this->symfonyStyle->createTable()->setHeaders(['Level', 'Error count', 'Increment'])->setRows($tableRows)->setStyle($tableStyle)->render();
    }
    public function outputTypeCoverageResult(TypeCoverageResult $typeCoverageResult) : void
    {
        $this->symfonyStyle->title('Type Coverage results');
        foreach ($typeCoverageResult->getTypeCoverages() as $typeCoverage) {
            $this->symfonyStyle->writeln(\sprintf('%s coverage is %.1f %%, out of %d items total', \ucfirst($typeCoverage->getCategory()), $typeCoverage->getRelative(), $typeCoverage->getTotalCount()));
        }
        $this->symfonyStyle->newLine();
    }
    /**
     * @return mixed[]
     */
    private function createRawData(BodyscanResult $bodyscanResult) : array
    {
        $tableRows = [];
        foreach ($bodyscanResult->getLevelResults() as $levelResult) {
            $increase = $levelResult->getChangeToPreviousLevel() ? '+ ' . $levelResult->getChangeToPreviousLevel() : '-';
            $tableRows[] = [$levelResult->getLevel(), $levelResult->getErrorCount(), $increase];
        }
        return $tableRows;
    }
}
