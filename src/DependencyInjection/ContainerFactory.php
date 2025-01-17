<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\DependencyInjection;

use PHPStanBodyscan202501\Illuminate\Container\Container;
use PHPStanBodyscan202501\Symfony\Component\Console\Application;
use PHPStanBodyscan202501\Symfony\Component\Console\Input\ArrayInput;
use PHPStanBodyscan202501\Symfony\Component\Console\Output\ConsoleOutput;
use PHPStanBodyscan202501\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Command\RunCommand;
final class ContainerFactory
{
    public function create() : Container
    {
        $container = new Container();
        // console
        $container->singleton(Application::class, function (Container $container) : Application {
            $application = new Application('PHPStan Bodyscan');
            $commands = [$container->make(RunCommand::class)];
            $application->addCommands($commands);
            $application->setDefaultCommand('run');
            // remove basic commands to make output clear
            $this->hideDefaultCommands($application);
            return $application;
        });
        $container->singleton(SymfonyStyle::class, static function () : SymfonyStyle {
            return new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
        });
        return $container;
    }
    public function hideDefaultCommands(Application $application) : void
    {
        $application->get('list')->setHidden(\true);
        $application->get('completion')->setHidden(\true);
        $application->get('help')->setHidden(\true);
    }
}
