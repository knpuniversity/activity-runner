<?php

namespace KnpU\ActivityRunner\Assert\Suite;

use KnpU\ActivityRunner\Result;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
interface StateInterface
{
    /**
     * @param Result $result
     *
     * @return boolean
     */
    function isAllowedToRun(Result $result);
}
