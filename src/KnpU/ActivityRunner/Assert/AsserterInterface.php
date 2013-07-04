<?php

namespace KnpU\ActivityRunner\Assert;

use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Result;

/**
 * Asserters are responsible for verifying that the produced output
 * is actually correct.
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
interface AsserterInterface
{
    /**
     * Is the result for the given activity valid?
     *
     * @param Result $result
     * @param ActivityInterface $activity
     *
     * @return boolean
     */
    function isValid(Result $result, ActivityInterface $activity);

    /**
     * Gets the validation errors for a given result & activity.
     *
     * @param Result $result
     * @param ActivityInterface $activity
     *
     * @return string[]  A list of failure messages
     */
    function getValidationErrors(Result $result, ActivityInterface $activity);
}
