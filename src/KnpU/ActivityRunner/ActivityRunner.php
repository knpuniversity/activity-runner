<?php

namespace KnpU\ActivityRunner;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Assert\AsserterInterface;
use KnpU\ActivityRunner\Configuration\ActivityConfigBuilder;
use KnpU\ActivityRunner\Factory\ActivityFactory;
use KnpU\ActivityRunner\Worker\WorkerBag;
use KnpU\ActivityRunner\Exception\UnexpectedTypeException;
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
     * Sets the configuration paths. They either point to specific files or
     * even entire directories in which case all files named `activities.yml`
     * are considered to be activity configuration files.
     *
     * @param string|array $paths
     *
     * @throws UnexpectedTypeException
     */
    public function setConfigPaths($paths)
    {
        if (is_string($paths)) {
            $paths = array($paths);
        } else if (!is_array($paths)) {
            throw new UnexpectedTypeException($paths, 'string|array');
        }

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
    private function buildConfig()
    {
        $rawPaths      = $this->configPaths;
        $directories   = array();
        $resolvedPaths = array();

        foreach ($rawPaths as $rawPath) {
            if (is_dir($rawPath)) {
                $directories[] = $rawPath;
            } else {
                $resolvedPaths[] = $rawPath;
            }
        }

        if (!empty($directories)) {
            $finder = new Finder();
            $finder->in($directories)->files()->name('activities.yml');

            foreach ($finder as $file) {
                $resolvedPaths[] = $file;
            }
        }

        return $this->configBuilder->build($resolvedPaths);
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

