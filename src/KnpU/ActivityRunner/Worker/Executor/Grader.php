<?php

namespace KnpU\ActivityRunner\Worker\Executor;

use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\Activity\CodingChallengeInterface;
use KnpU\ActivityRunner\Activity\Exception\GradingException;

class Grader
{
    public function grade(CodingExecutionResult $result, CodingChallengeInterface $codingChallenge)
    {
        try {
            $codingChallenge->grade($result);
        } catch (GradingException $e) {
            $result->setGradingError($e->getMessage());
        }

    }
}