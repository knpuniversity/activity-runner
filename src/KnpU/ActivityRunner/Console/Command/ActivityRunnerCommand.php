<?php

namespace Knpu\ActivityRunner\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
abstract class ActivityRunnerCommand extends Command
{
    /**
     * @var \Pimple
     */
    private $app;

    /**
     * {@inheritDoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $configPath = __DIR__.'/../../../../../app/config/';
        $paramsFile = $configPath.'parameters.php';

        $app = require($configPath.'services.php');
        $app = require(is_file($paramsFile) ? $paramsFile : $paramsFile.'.dist');

        $this->app = $app;
    }

    /**
     * Gets a service from the DI container.
     *
     * @param string $service
     */
    protected function get($service)
    {
        return $this->app[$service];
    }

    /**
     * @return Pimple
     */
    protected function getApp()
    {
        return $this->app;
    }
}
