<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\ActivityRunner;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\Exception\ContextErrorException;

/**
 * The GherkinWorker is really just a PHP worker, except
 */
class GherkinWorker implements WorkerInterface
{
    /**
     * Execute the code and modify the CodingExecutionResult
     *
     * @param string $rootDir Where all the files have been placed
     * @param string $entryPointFilename
     * @param CodingContext $context
     * @param CodingExecutionResult $result
     */
    public function executeCode($rootDir, $entryPointFilename, CodingContext $context, CodingExecutionResult $result)
    {
        // do nothing! the input is not executed in any way
    }

    /**
     * Gets the name of the worker.
     *
     * @return string
     */
    public function getName()
    {
        return Activity\CodingChallengeInterface::EXECUTION_MODE_GHERKIN;
    }

}
