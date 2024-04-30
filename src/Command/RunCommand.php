<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Component\Console\Command\Command;
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
            $this->symfonyStyle->title(sprintf('Level %d', $phpStanLevel));
            $errorCount = $this->measureErrorsInLevel($phpStanLevel);

            $errorCountByLevel[$phpStanLevel] = $errorCount;
        }

        $this->symfonyStyle->table(['Level', 'Errors'], $errorCountByLevel);
    }

    private function measureErrorsInLevel(int $phpstanLevel): int
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
}
