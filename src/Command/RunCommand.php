<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RunCommand extends Command
{
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
        // @todo
    }
}
