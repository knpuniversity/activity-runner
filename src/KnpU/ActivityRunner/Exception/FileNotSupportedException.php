<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class FileNotSupportedException extends \Exception
    implements ActivityRunnerException
{
    public function __construct($fileName)
    {
        $message = 'None of the workers support rendering of file `%s`';
        $message = sprintf($message, $fileName);

        parent::__construct($message);
    }
}
