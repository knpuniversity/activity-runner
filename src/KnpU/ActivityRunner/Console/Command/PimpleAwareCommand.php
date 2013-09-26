<?php

namespace KnpU\ActivityRunner\Console\Command;

use KnpU\ActivityRunner\Pimple\PimpleAwareInterface;
use Symfony\Component\Console\Command\Command;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
abstract class PimpleAwareCommand extends Command implements PimpleAwareInterface
{
    /**
     * @var \Pimple
     */
    private $pimple;

    /**
     * {@inheritDoc}
     */
    public function setPimple(\Pimple $pimple)
    {
        $this->pimple = $pimple;
    }

    /**
     * @return \Pimple
     */
    protected function getPimple()
    {
        return $this->pimple;
    }

    protected function getService($service)
    {
        return $this->pimple[$service];
    }
}
