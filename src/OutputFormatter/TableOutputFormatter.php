<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;

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
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> feda13b (misc)

        if ($bodyscanResult->getTypeCoverageResults()) {
            $this->symfonyStyle->title('Type coverage');

<<<<<<< HEAD
            foreach ($bodyscanResult->getTypeCoverageResults() as $typeCoverageResult) {
                $this->symfonyStyle->writeln(sprintf(
                    '%s coverage is %f.2, out of %d items total',
                    $typeCoverageResult->getCategory(),
                    $typeCoverageResult->getRelative(),
                    $typeCoverageResult->getTotalCount(),
                ));
            }
        }
>>>>>>> 5de6845 (fixup! misc)
=======
            foreach ($bodyscanResult->getTypeCoverageResults() as $typeCoverageItem) {
            }
        }
>>>>>>> feda13b (misc)
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
