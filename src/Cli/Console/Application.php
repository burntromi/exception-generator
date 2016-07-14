<?php

namespace Burntromi\ExceptionGenerator\Cli\Console;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Burntromi\ExceptionGenerator\Cli\Command\ExceptionGeneratorCommand;

final class Application extends ConsoleApplication
{
    /**
     * Home directory.
     *
     * @var string
     */
    protected $home;

    /**
     * {@inheritDoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'exception-generator';
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new ExceptionGeneratorCommand();
        return $commands;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();
        return $inputDefinition;
    }

    /**
     * Get path to home directory.
     *
     * @return string
     */
    public function getHome()
    {
        if (null === $this->home) {
            $this->home = getenv('HOME');
        }

        return $this->home;
    }

    /**
     * Set path to home directory.
     *
     * @param string $home
     */
    public function setHome($home)
    {
        $this->home = (string) $home;
    }
}
