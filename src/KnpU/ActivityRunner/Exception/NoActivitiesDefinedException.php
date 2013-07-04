<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class NoActivitiesDefinedException extends \LogicException
    implements ActivityRunnerException
{
    public function __construct($message = null, $code = null, $previous = null)
    {
        if (!$message) {
            $message = 'Could not find any activities.';
        }

        parent::__construct($message, $code, $previous);
    }
}