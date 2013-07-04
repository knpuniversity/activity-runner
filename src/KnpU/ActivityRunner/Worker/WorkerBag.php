<?php

namespace KnpU\ActivityRunner\Worker;

/**
 * Just a container for different workers.
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class WorkerBag
{
    /**
     * @var array
     */
    protected $workers;

    /**
     * @param array $workers
     */
    public function __construct(array $workers = array())
    {
        $this->workers = array();

        $this->addWorkers($workers);
    }

    /**
     * @param string $name
     *
     * @throws \LogicException if no such worker exists
     */
    public function get($name)
    {
        if (!isset($this->workers[$name])) {
            throw new \LogicException(sprintf('No workers named `%s` exist, the following workers are available: `%s`', $name, implode('`, `', array_keys($this->workers))));
        }

        return $this->workers[$name];
    }

    /**
     * @param WorkerInterface $worker
     *
     * @throws \LogicException if a worker with the name exists
     */
    public function addWorker(WorkerInterface $worker)
    {
        $name = $worker->getName();

        if (isset($this->workers[$name])) {
            throw new \LogicException(sprintf('A worker named `%s` already exists', $name));
        }

        $this->workers[$name] = $worker;
    }

    /**
     * @param WorkerInterface[] $workers  A key-value pair of workers and their names
     */
    public function addWorkers(array $workers)
    {
        foreach ($workers as $worker) {
            $this->addWorker($worker);
        }
    }
}
