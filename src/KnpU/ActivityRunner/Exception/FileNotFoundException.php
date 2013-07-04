<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class FileNotFoundException extends \RuntimeException
{
    /**
     * @param string $filePath
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('The file "%s" does not exist', $path));
    }
}
