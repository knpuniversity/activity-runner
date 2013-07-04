<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class SkeletonNotFoundException extends \LogicException
    implements ActivityRunnerException
{
    /**
     * @param string $logicalName  Logical name of the skeleton file
     * @param array $allowedNames  A list of allowed logical skeleton file names
     */
    public function __construct($logicalName, array $allowedNames)
    {
        $message = 'Could not find `%s` from a list of the following skeletons: `%s`';

        parent::__construct(sprintf($message, $logicalName, implode('`, `', $allowedNames)));
    }
}
