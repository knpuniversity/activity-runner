<?php

namespace KnpU\ActivityRunner\Pimple;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
interface PimpleAwareInterface
{
    /**
     * @param \Pimple $pimple
     */
    function setPimple(\Pimple $pimple);
}