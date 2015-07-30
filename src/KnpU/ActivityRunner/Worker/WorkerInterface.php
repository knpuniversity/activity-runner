<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Assert\AssertSuite;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
interface WorkerInterface
{
    function getInlineCodeToExecute(\Twig_Environment $twig, Activity $activity);

    /**
     * Gets the name of the worker.
     *
     * @return string
     */
    function getName();
}
