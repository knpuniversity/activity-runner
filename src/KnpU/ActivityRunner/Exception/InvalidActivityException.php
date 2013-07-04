<?php

namespace KnpU\ActivityRunner\Exception;

use KnpU\ActivityRunner\ActivityInterface;
/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class InvalidActivityException extends \Exception implements ActivityRunnerException
{
    /**
     * @var array
     */
    protected $errors;

    /**
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('Activity did not pass all of the assertions');
    }

    /**
     * Gets all of the validation errors.
     *
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->errors;
    }
}
