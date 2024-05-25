<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Command\RunCommand;
use TomasVotruba\PHPStanBodyscan\DependencyInstaller;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter;
use TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory;
use TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory;
use TomasVotruba\PHPStanBodyscan\Process\PHPStanResultResolver;

if (file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    // A. build downgraded package
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
} else {
    // B. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}

// 1. setup dependencies
$symfonyStyle = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());

$jsonOutputFormatter = new JsonOutputFormatter($symfonyStyle);
$tableOutputFormatter = new TableOutputFormatter($symfonyStyle);

$runCommand = new RunCommand(
    $symfonyStyle,
    new AnalyseProcessFactory(),
    new PHPStanConfigFactory(),
    $jsonOutputFormatter,
    $tableOutputFormatter,
    new DependencyInstaller($symfonyStyle),
    new PHPStanResultResolver(),
);

$application = new Application();
$application->add($runCommand);
$application->setDefaultCommand('run');

// 2. hide default commands
$application->get('completion')
    ->setHidden();
$application->get('help')
    ->setHidden();
$application->get('list')
    ->setHidden();

// 3. execute command
$exitCode = $application->run(new ArgvInput(), new ConsoleOutput());
exit($exitCode);
