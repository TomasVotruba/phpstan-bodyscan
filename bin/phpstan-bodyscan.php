<?php

declare(strict_types=1);

use Entropy\Console\ConsoleApplication;
use Entropy\Container\Container;

if (file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    // A. build downgraded package
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
} else {
    // B. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}

$container = new Container();
$container->autodiscover(__DIR__ . '/../src');

$consoleApplication = $container->make(ConsoleApplication::class);
$exitCode = $consoleApplication->run($argv);
exit($exitCode);
