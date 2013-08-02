<?php

namespace KnpU\ActivityRunner\Repository;

use KnpU\ActivityRunner\Configuration\ActivityConfigBuilder;
use KnpU\ActivityRunner\Factory\ActivityFactory;

/**
 * The Configurator facade will basically inject the activity configuration
 * inside the loaded repository.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class Configurator implements LoaderInterface
{
    /**
     * @var ActivityFactory
     */
    protected $activityFactory;

    /**
     * @var ActivityConfigBuilder
     */
    protected $configBuilder;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @param LoaderInterface       $loader
     * @param ActivityFactory       $activityFactory
     * @param ActivityConfigBuilder $configBuilder
     */
    public function __construct(
        LoaderInterface $loader,
        ActivityFactory $activityFactory,
        ActivityConfigBuilder $configBuilder
    ) {
        $this->loader          = $loader;
        $this->activityFactory = $activityFactory;
        $this->configBuilder   = $configBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function load($url, $ref)
    {
        $repository = $this->loader->load($url, $ref);

        // The current design doesn't really impose on cloning the factory
        // Since each repository has a different configuration, we need to
        // inject it every time. This means we can't have the same factory
        // for each repository and its the reason why the factory is cloned.
        //
        // In practise though this is not a problem as a single repository
        // is only ever used in a single request.

        $config = $this->configBuilder->build($repository->getName());

        $activityFactory = clone $this->activityFactory;
        $activityFactory->setConfig($config);

        $repository->setActivityFactory($activityFactory);

        return $repository;
    }
}
