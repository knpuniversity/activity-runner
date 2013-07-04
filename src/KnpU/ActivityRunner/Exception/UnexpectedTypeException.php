<?php

namespace KnpU\ActivityRunner\Exception;

class UnexpectedTypeException extends \Exception
    implements ActivityRunnerException
{
    public function __construct($value, $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type "%s", "%s" given', $expectedType, is_object($value) ? get_class($value) : gettype($value)));
    }
}