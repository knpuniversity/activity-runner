<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityNotFoundException extends \RuntimeException implements
    ActivityRunnerException
{
    /**
     * @param string $name         Name of the searched activity
     * @param array $allowedNames  A list of allowed activity names
     */
    public function __construct($name, array $allowedNames)
    {
        $message = 'Failed to find an activity configuration named `%s`, the following are available: `%s`.';
        $allowedNames = implode('`, `', $allowedNames);

        parent::__construct(sprintf($message, $name, $allowedNames));
    }
}
