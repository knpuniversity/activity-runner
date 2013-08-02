<?php

namespace KnpU\ActivityRunner;

use KnpU\ActivityRunner\Assert\AsserterInterface;
use KnpU\ActivityRunner\Worker\WorkerBag;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ActivityRunner
{
    /**
     * @var AsserterInterface
     */
    protected $asserter;

    /**
     * @var WorkerBag
     */
    protected $workerBag;

    /**
     * @param AsserterInterface $asserter
     * @param ActivityConfigBuilder $configBuilder
     * @param ActivityFactory $factory
     * @param WorkerBag $workerBag
     */
    public function __construct(AsserterInterface $asserter, WorkerBag $workerBag) {
        $this->asserter  = $asserter;
        $this->workerBag = $workerBag;
    }

    /**
     * @param Activity $activity
     *
     * @return \KnpU\ActivityRunner\Result
     */
    public function run(Activity $activity)
    {
        $worker = $this->getWorker($activity->getWorkerName());

        $result = $worker->render($activity);

        $worker->injectInternals($activity->getSuite());

        // Validates the result regardless of whether the activity failed
        // completely or ran successfully.
        $errors = $this->asserter->validate($result, $activity);

        if ($errors) {
            $result->setValidationErrors($errors);
        }

        return $result;
    }

    /**
     * @param string $workerName
     *
     * @return \KnpU\ActivityRunner\Worker\WorkerInterface
     */
    private function getWorker($workerName)
    {
        return $this->workerBag->get($workerName);
    }
}

