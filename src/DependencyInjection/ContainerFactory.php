<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\DependencyInjection;

use Illuminate\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Command\RunCommand;
use TomasVotruba\PHPStanBodyscan\Command\TypeCoverageCommand;

final class ContainerFactory
{
    public function create(): Container
    {
        $container = new Container();

        // console
        $container->singleton(Application::class, function (Container $container): Application {
            $application = new Application('PHPStan Bodyscan');

            $commands = [$container->make(RunCommand::class)];

            $application->addCommands($commands);
            $application->setDefaultCommand('run');

            // remove basic commands to make output clear
            $this->hideDefaultCommands($application);

            return $application;
        });

        $container->singleton(
            SymfonyStyle::class,
            static fn (): SymfonyStyle => new SymfonyStyle(new ArrayInput([]), new ConsoleOutput())
        );

        return $container;
    }

    public function hideDefaultCommands(Application $application): void
    {
        $application->get('list')
            ->setHidden(true);
        $application->get('completion')
            ->setHidden(true);
        $application->get('help')
            ->setHidden(true);
    }
}
