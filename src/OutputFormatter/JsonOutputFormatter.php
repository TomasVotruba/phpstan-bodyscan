<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;

final readonly class JsonOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private SymfonyStyle $symfonyStyle
    ) {
    }

    public function outputResult(BodyscanResult $bodyscanResult): void
    {
        // restore verbosity
        $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_NORMAL);

        $rawData = $this->createRawData($bodyscanResult);

        $jsonOutput = json_encode($rawData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        $this->symfonyStyle->writeln($jsonOutput);
    }

    /**
     * @return mixed[]
     */
    private function createRawData(BodyscanResult $bodyscanResult): array
    {
        $rawData = [];
        $rawData['levels'] = [];

        foreach ($bodyscanResult->getLevelResults() as $levelResult) {
            $rawData['levels'][] = [
                'level' => $levelResult->getLevel(),
                'error_count' => $levelResult->getErrorCount(),
            ];
        }

        if ($bodyscanResult->getTypeCoverageResults()) {
            $rawData['type_coverage'] = [];

            foreach ($bodyscanResult->getTypeCoverageResults() as $typeCoverageResult) {
                $rawData['type_coverage'][] = [
                    'category' => $typeCoverageResult->getCategory(),
                    'relative_covered' => $typeCoverageResult->getRelative(),
                    'total_count' => $typeCoverageResult->getTotalCount(),
                ];
            }
        }

        return $rawData;
    }
}
