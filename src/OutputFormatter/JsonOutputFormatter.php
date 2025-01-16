<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\OutputFormatter;

use PHPStanBodyscan202501\Symfony\Component\Console\Output\OutputInterface;
use PHPStanBodyscan202501\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Contract\OutputFormatterInterface;
use TomasVotruba\PHPStanBodyscan\ValueObject\BodyscanResult;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;
final class JsonOutputFormatter implements OutputFormatterInterface
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
    public function outputTypeCoverageResult(TypeCoverageResult $typeCoverageResult) : void
    {
        $rawData = [];
        foreach ($typeCoverageResult->getTypeCoverages() as $typeCoverageResult) {
            $rawData[] = ['category' => $typeCoverageResult->getCategory(), 'relative_covered' => $typeCoverageResult->getRelative(), 'total_count' => $typeCoverageResult->getTotalCount()];
        }
        $this->printArrayAsJson($rawData);
    }
    public function outputResult(BodyscanResult $bodyscanResult) : void
    {
        // restore verbosity
        $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $rawData = [];
        foreach ($bodyscanResult->getLevelResults() as $levelResult) {
            $rawData[] = ['level' => $levelResult->getLevel(), 'error_count' => $levelResult->getErrorCount(), 'increment_count' => $levelResult->getChangeToPreviousLevel()];
        }
        $this->printArrayAsJson($rawData);
    }
    /**
     * @param mixed[] $rawData
     */
    private function printArrayAsJson(array $rawData) : void
    {
        $jsonOutput = \json_encode($rawData, \JSON_PRETTY_PRINT);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \Exception(\json_last_error_msg());
        }
        // restore verbosity
        $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $this->symfonyStyle->writeln($jsonOutput);
    }
}
