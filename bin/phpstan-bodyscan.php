<?php

declare (strict_types=1);
namespace PHPStanBodyscan202405;

use PHPStanBodyscan202405\Symfony\Component\Console\Application;
use PHPStanBodyscan202405\Symfony\Component\Console\Input\ArgvInput;
use PHPStanBodyscan202405\Symfony\Component\Console\Output\ConsoleOutput;
use TomasVotruba\PHPStanBodyscan\DependencyInjection\ContainerFactory;
if (\file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    // A. build downgraded package
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
} else {
    // B. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}
$ontainerFactory = new ContainerFactory();
$container = $ontainerFactory->create();
/** @var Application $application */
$application = $container->get(Application::class);
$exitCode = $application->run(new ArgvInput(), new ConsoleOutput());
exit($exitCode);
