<?php

namespace KnpU\ActivityRunner\Console;

use KnpU\ActivityRunner\Console\Command\RunCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Overriding the base application to have a single command application.
 *
 * @see symfony.com/doc/current/components/console/single_command_tool.html
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'activity:run';
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new RunCommand();

        return $defaultCommands;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();

        // Clear out the normal first argument, which is the command name.
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
