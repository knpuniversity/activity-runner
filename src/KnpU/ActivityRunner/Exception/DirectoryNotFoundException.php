<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class DirectoryNotFoundException extends \RuntimeException implements
    ActivityRunnerException
{
    /**
     * @param string $dirname
     */
    public function __construct($dirPath)
    {
        $message = 'Directory `%s` does not exist.';

        parent::__construct(sprintf($message, $dirPath));
    }
}
