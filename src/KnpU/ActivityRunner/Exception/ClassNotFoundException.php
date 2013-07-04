<?php

namespace KnpU\ActivityRunner\Exception;

use KnpU\ActivityRunner\Exception\ActivityRunnerException;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ClassNotFoundException extends \Exception implements ActivityRunnerException
{
    public function __construct($filePath)
    {
        $message = 'No class found in file `%s`.';

        parent::__construct(sprintf($message, $filePath));
    }
}