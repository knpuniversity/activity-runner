<?php

namespace KnpU\ActivityRunner\Repository;

use KnpU\ActivityRunner\Repository\Naming\Strategy;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class Cache implements LoaderInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var Strategy
     */
    protected $namingStrategy;

    /**
     * @param LoaderInterface $loader
     * @param Strategy        $namingStrategy
     * @param Filesystem|null $filesystem
     */
    public function __construct(
        LoaderInterface $loader,
        Strategy $namingStrategy,
        Filesystem $filesystem = null
    ) {
        $this->loader         = $loader;
        $this->namingStrategy = $namingStrategy;
        $this->filesystem     = $filesystem ?: new Filesystem();
    }

    /**
     * {@inheritDoc}
     */
    public function load($url, $ref)
    {
        $name = $this->namingStrategy->create($url, $ref);

        if ($this->filesystem->exists($name)) {
            $repository = new Repository($name);
        } else {
            $repository = $this->loader->load($url, $ref);
        }

        return $repository;
    }
}
