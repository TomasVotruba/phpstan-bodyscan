<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class RunCommand extends Command
{
    /**
     * @var int
     */
    private const MAX_PHPSTAN_LEVEL = 8;

    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('run');
        $this->setDescription('Check classes that are not used in any config and in the code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errorCountByLevel = [];

        // measure phpstan levels
        for ($phpStanLevel = 0; $phpStanLevel < self::MAX_PHPSTAN_LEVEL; ++$phpStanLevel) {
            $this->symfonyStyle->writeln(sprintf('Running PHPStan level %d', $phpStanLevel));

            $errorCountByLevel[$phpStanLevel] = $this->measureErrorCountInLevel($phpStanLevel);
        }

        $this->renderResultInTable($errorCountByLevel);

        return self::SUCCESS;
    }

    private function measureErrorCountInLevel(int $phpstanLevel): int
    {
        // with json format
        $analyseLevelProcess = new Process([
            'vendor/bin/phpstan',
            'analyse',
            '--error-format',
            'json',
            '--level',
            $phpstanLevel,
        ]);

        $analyseLevelProcess->run();
        $jsonResult = $analyseLevelProcess->getOutput();

        try {
            $json = Json::decode($jsonResult, true);
        } catch (JsonException $jsonException) {
            throw new JsonException(sprintf(
                'Could not decode JSON from phpstan: "%s"',
                $jsonResult
            ), 0, $jsonException);
        }

        return (int) $json['totals']['errors'];
    }

    /**
     * @param array<int, int> $errorCountByLevel
     */
    private function renderResultInTable(array $errorCountByLevel): void
    {
        // convert to symfony table data
        $tableRows = [];
        foreach ($errorCountByLevel as $phpstanLevel => $errorCount) {
            $tableRows[] = [$phpstanLevel, $errorCount];
        }

        $tableStyle = new TableStyle();
        $tableStyle->setPadType(STR_PAD_LEFT);

        $this->symfonyStyle->newLine(2);
        $this->symfonyStyle->createTable()
            ->setHeaders(['Level', 'Error count'])
            ->setRows($tableRows)
            // allign right
            ->setStyle($tableStyle)
            ->render();
    }
}
