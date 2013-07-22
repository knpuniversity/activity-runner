<?php

namespace KnpU\ActivityRunner;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Assert\AsserterInterface;
use KnpU\ActivityRunner\Configuration\ActivityConfigBuilder;
use KnpU\ActivityRunner\Configuration\PathExpander;
use KnpU\ActivityRunner\Factory\ActivityFactory;
use KnpU\ActivityRunner\Worker\WorkerBag;
use Symfony\Component\Finder\Finder;

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
     * @var array
     */
    protected $configPaths;

    /**
     * @var ActivityConfigBuilder
     */
    protected $configBuilder;

    /**
     * @var ActivityFactory
     */
    protected $factory;

    /**
     * @var PathExpander
     */
    protected $pathExpander;

    /**
     * @var WorkerBag
     */
    protected $workerBag;

    /**
     * @param AsserterInterface $asserter
     * @param ActivityConfigBuilder $configBuilder
     * @param ActivityFactory $factory
     * @param PathExpander $pathExpander
     * @param WorkerBag $workerBag
     */
    public function __construct(
        AsserterInterface $asserter,
        ActivityConfigBuilder $configBuilder,
        ActivityFactory $factory,
        PathExpander $pathExpander,
        WorkerBag $workerBag
    ) {
        $this->asserter      = $asserter;
        $this->configBuilder = $configBuilder;
        $this->factory       = $factory;
        $this->pathExpander  = $pathExpander;
        $this->workerBag     = $workerBag;
    }

    /**
     * Sets the configuration paths. They either point to specific files or
     * even entire directories in which case all files named `activities.yml`
     * are considered to be activity configuration files.
     *
     * @param string|array $paths
     */
    public function setConfigPaths($paths)
    {
        $this->configPaths = $paths;
    }

    /**
     * @param string $activityName
     * @param string $configPath
     * @param Collection $inputFiles
     *
     * @return \KnpU\ActivityRunner\Result
     */
    public function run($activityName, Collection $inputFiles)
    {
        $config = $this->buildConfig();

        $this->factory->setConfig($config);
        $activity = $this->factory->createActivity($activityName, $inputFiles);

        $worker = $this->getWorker($config[$activityName]['worker']);
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
     * @param string $configPath
     *
     * @return array
     */
    private function buildConfig()
    {
        $paths = $this->configPaths;
        $paths = $this->pathExpander->expand($paths, 'activities.yml');

        return $this->configBuilder->build($paths);
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

