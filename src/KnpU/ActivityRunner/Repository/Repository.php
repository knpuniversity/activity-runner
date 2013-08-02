<?php

namespace KnpU\ActivityRunner\Repository;

use KnpU\ActivityRunner\Factory\ActivityFactory;

/**
 * Repository represents a single repository containing activities. The end
 * goal is to have a method getActivity that returns an Activity object and
 * that's it. This is currently not possible due to some bad design decisions.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class Repository
{
    /**
     * @var ActivityFactory
     */
    protected $activityFactory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param ActivityFactory $activityFactory
     */
    public function setActivityFactory(ActivityFactory $activityFactory)
    {
        $this->activityFactory = $activityFactory;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets an activity by its name.
     *
     * @param string $activityName
     *
     * @return \KnpU\ActivityRunner\Activity
     */
    public function getActivity($activityName)
    {
        if (!isset($this->activityFactory)) {
            throw new \LogicException('You must first set the activity factory before fetching an activity.');
        }

        return $this->activityFactory->createActivity($activityName);
    }
}
