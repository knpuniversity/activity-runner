<?php

namespace KnpU\ActivityRunner;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Assert\AsserterInterface;
use KnpU\ActivityRunner\Configuration\ActivityConfigBuilder;
use KnpU\ActivityRunner\Factory\ActivityFactory;
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
     * @var ActivityConfigBuilder
     */
    protected $configBuilder;

    /**
     * @var ActivityFactory
     */
    protected $factory;

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
    public function __construct(
        AsserterInterface $asserter,
        ActivityConfigBuilder $configBuilder,
        ActivityFactory $factory,
        WorkerBag $workerBag
    ) {
        $this->asserter      = $asserter;
        $this->configBuilder = $configBuilder;
        $this->factory       = $factory;
        $this->workerBag     = $workerBag;
    }

    /**
     * @param string $activityName
     * @param string $configPath
     * @param Collection $inputFiles
     *
     * @return \KnpU\ActivityRunner\Result
     */
    public function run($activityName, $configPath, Collection $inputFiles)
    {
        $config = $this->buildConfig($configPath);

        $this->factory->setConfig($config);
        $activity = $this->factory->createActivity($activityName, $inputFiles);

        $worker = $this->getWorker($config[$activityName]['worker']);
        $result = $worker->render($activity);

        // Only validate if we're at least somewhat valid.
        if ($result->isValid()) {
            // Verify the output.
            if (!$this->asserter->isValid($result, $activity)) {
                $errors = $this->asserter->getValidationErrors($result, $activity);

                $result->setValidationErrors($errors);
            }
        }

        return $result;
    }

    /**
     * @param string $configPath
     *
     * @return array
     */
    private function buildConfig($configPath)
    {
        return $this->configBuilder->build($configPath);
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

