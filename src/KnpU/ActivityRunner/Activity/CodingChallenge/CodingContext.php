<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

/**
 * Holds information about variables and other things that'll be setup before running code
 */
class CodingContext
{
    private $variables = array();

    /**
     * Add a variable that'll be made available to the scripts
     *
     * @param string $name
     * @param string $value
     */
    public function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function getVariables()
    {
        return $this->variables;
    }
}
