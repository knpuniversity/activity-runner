<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Assert\AssertSuite;
use KnpU\ActivityRunner\Exception\FileNotSupportedException;
use KnpU\ActivityRunner\Exception\UnexpectedTypeException;

/**
 * The chained worker makes it possible to use several workers
 * together. It simply tries to find a worker that supports the given
 * entry point file and delegates the work to it.
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ChainedWorker implements WorkerInterface
{
    /**
     * @var WorkerInterface[]
     */
    protected $workers;

    /**
     * @param WorkerInterface[] $workers
     */
    public function __construct(array $workers)
    {
        if (count($workers) < 1) {
            throw new \LogicException('You must specify at least 1 worker');
        }

        foreach ($workers as $worker) {
            if (!($worker instanceof WorkerInterface)) {
                throw new UnexpectedTypeException($worker, 'KnpU\\ActivityRunner\\Worker\\WorkerInterface');
            }
        }

        $this->workers = $workers;
    }

    /**
     * {@inheritDoc}
     */
    public function render(ActivityInterface $activity)
    {
        $entryPoint = $activity->getEntryPoint();
        $context    = $activity->getContext();

        if (!($worker = $this->getSupportingWorker($entryPoint, $context))) {
            throw new FileNotSupportedException($entryPoint);
        }

        return $worker->render($activity);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($fileName, array $context)
    {
        return !is_null($this->getSupportingWorker($fileName, $context));
    }

    /**
     * {@inheritDoc}
     */
    public function injectInternals(AssertSuite $suite)
    {
        foreach ($this->workers as $worker) {
            $worker->injectInternals($suite);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'chained';
    }

    /**
     * @param string $fileName
     * @param array $context
     *
     * @return WorkerInterface|null
     */
    private function getSupportingWorker($fileName, array $context)
    {
        foreach ($this->workers as $worker) {
            if ($worker->supports($fileName, $context)) {
                return $worker;
            }
        }

        return null;
    }
}
