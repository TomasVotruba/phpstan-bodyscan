<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\DependencyInjection;

use Illuminate\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\Command\RunCommand;

/**
 * @api
 */
final class ContainerFactory
{
    /**
     * @api
     */
    public function create(): Container
    {
        $container = new Container();

        $container->singleton(
            SymfonyStyle::class,
            static function (): SymfonyStyle {
                // use null output ofr tests to avoid printing
                $consoleOutput = defined('PHPUNIT_COMPOSER_INSTALL') ? new NullOutput() : new ConsoleOutput();
                return new SymfonyStyle(new ArrayInput([]), $consoleOutput);
            }
        );

        $container->singleton(Application::class, function (Container $container): Application {
            /** @var RunCommand $checkCommand */
            $checkCommand = $container->make(RunCommand::class);

            $application = new Application();
            $application->add($checkCommand);

            $application->setDefaultCommand($checkCommand->getName());

            $this->hideDefaultCommands($application);

            return $application;
        });

        return $container;
    }

    /**
     * @see https://tomasvotruba.com/blog/how-make-your-tool-commands-list-easy-to-read
     */
    private function hideDefaultCommands(Application $application): void
    {
        $application->get('completion')
            ->setHidden();
        $application->get('help')
            ->setHidden();
        $application->get('list')
            ->setHidden();
    }
}
