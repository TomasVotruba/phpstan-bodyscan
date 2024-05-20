<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use PHPStanBodyscan202405\Symfony\Component\Console\Helper\TableStyle;
use PHPStanBodyscan202405\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
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
        $this->symfonyStyle->createTable()->setHeaders(['Level', 'Error count'])->setRows($tableRows)->setStyle($tableStyle)->render();
    }
    /**
     * @return mixed[]
     */
    private function createRawData(BodyscanResult $bodyscanResult) : array
    {
        $tableRows = [];
        foreach ($bodyscanResult->getLevelResults() as $levelResult) {
            $tableRows[] = [$levelResult->getLevel(), $levelResult->getErrorCount()];
        }
        return $tableRows;
    }
}
