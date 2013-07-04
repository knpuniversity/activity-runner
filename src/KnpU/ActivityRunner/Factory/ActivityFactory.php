<?php

namespace KnpU\ActivityRunner\Factory;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Exception\ActivityNotFoundException;
use KnpU\ActivityRunner\Exception\NoActivitiesDefinedException;
use KnpU\ActivityRunner\Assert\ClassLoader;

/**
 * This factory builds Activity objects.
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityFactory
{
    /**
     * @var ClassLoader
     */
    protected $classLoader;

    /**
     * @var array
     */
    protected $config;

    /**
     * @see \KnpU\ActivityRunner\Configuration\ActivityConfiguration
     *
     * @param array $config  Configuration for creating activities
     *
     * @throws NoActivitiesDefinedException if the config array is empty
     */
    public function __construct(array $config)
    {
        if (0 === count($config)) {
            throw new NoActivitiesDefinedException();
        }

        $this->classLoader = new ClassLoader();
        $this->config = $config;
    }

    /**
     * Creates a new activity.
     *
     * @param string $name            The name of the activity
     * @param Collection $inputFiles  Input from the user
     *
     * @return ActivityInterface
     *
     * @throws ActivityNotFoundException if no activity was found with the name
     */
    public function createActivity($name, Collection $inputFiles)
    {
        if (!array_key_exists($name, $this->config)) {
            throw new ActivityNotFoundException($name, array_keys($this->config));
        }

        $activityConfig = $this->config[$name];

        $activity = new Activity($this->classLoader);

        $activity->setQuestion($activityConfig['question']);
        $activity->setSkeletons($activityConfig['skeletons']);

        // The input files can be set only after the skeletons are set, but must
        // be set before setting entry_point.
        $activity->setInputFiles($inputFiles);

        $activity->setEntryPoint($activityConfig['entry_point']);
        $activity->setContext($activityConfig['context']);
        $activity->setSuiteSource($activityConfig['asserts']);

        return $activity;
    }
}
