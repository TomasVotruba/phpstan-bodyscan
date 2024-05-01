<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Command\RunCommand;

if (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    // project's autoload
    require_once __DIR__ . '/../../../../vendor/autoload.php';
} else {
    // B. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}

$symfonyStyle = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());

$runCommand = new RunCommand($symfonyStyle);

$application = new Application();
$application->add($runCommand);
$application->setDefaultCommand('run');

// hide default commands
$application->get('completion')
    ->setHidden();
$application->get('help')
    ->setHidden();
$application->get('list')
    ->setHidden();

$exitCode = $application->run(new ArgvInput(), new ConsoleOutput());
exit($exitCode);
