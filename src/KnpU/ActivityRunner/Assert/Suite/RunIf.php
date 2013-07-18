<?php

namespace KnpU\ActivityRunner\Assert\Suite;

use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\Exception\UnexpectedTypeException;

/**
 * @Annotation
 * @Target({"METHOD"})
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class RunIf
{
    /**
     * @var ActivityState[]
     */
    protected $states;

    /**
     * @param array $states Allowed states under the `value` key
     *
     * @throws \LogicException
     * @throws UnexpectedTypeException
     */
    public function __construct(array $values)
    {
        if (!isset($values['value'])) {
            throw new \LogicException('You must provide at least 1 state.');
        }

        $states = is_array($values['value']) ? $values['value'] : array($values['value']);

        foreach ($states as $state) {
            if (!($state instanceof StateInterface)) {
                throw new UnexpectedTypeException($state, 'KnpU\\ActivityRunner\\Assert\\Suite\\StateInterfac');
            }
        }

        $this->states = $states;
    }

    /**
     * @param Result $result
     *
     * @return boolean
     */
    public function isAllowedToRun(Result $result)
    {
        foreach ($this->states as $state) {
            if ($state->isAllowedToRun($result)) {
                return true;
            }
        }

        return false;
    }
}