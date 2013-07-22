<?php

namespace KnpU\ActivityRunner\Console;

use KnpU\ActivityRunner\Pimple\PimpleAwareInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class PimpleAwareApplication extends Application implements PimpleAwareInterface
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', \Pimple $pimple = null)
    {
        if ($pimple) {
            $this->setPimple($pimple);
        }

        parent::__construct($name, $version);
    }

    /**
     * @var \Pimple
     */
    private $pimple;

    /**
     * {@inheritDoc}
     */
    public function add(Command $command)
    {
        if ($this->pimple && ($command instanceof PimpleAwareInterface)) {
            $command->setPimple($this->pimple);
        }

        return parent::add($command);
    }

    /**
     * {@inheritDoc}
     */
    public function setPimple(\Pimple $pimple)
    {
        $this->pimple = $pimple;
    }
}