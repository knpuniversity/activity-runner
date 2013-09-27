<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class FileNotFoundException extends \RuntimeException
{
    /**
     * @param string $path
     * @param string|null $message
     */
    public function __construct($path, $message = null)
    {
        if ($message) {
            $msg = sprintf('%s: "%s"', $message, $path);
        } else {
            $msg = sprintf('The file "%s" does not exist', $path);
        }

        parent::__construct($msg);
    }
}
