<?php

namespace KnpU\ActivityRunner\Repository;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
