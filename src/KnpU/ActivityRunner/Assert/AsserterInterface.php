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
     * Validates the result given the related activity. An empty array is
     * returned for valid results.
     *
     * @param Result $result
     * @param ActivityInterface $activity
     *
     * @return string[] A list of failure messages
     */
    function validate(Result $result, ActivityInterface $activity);
}
