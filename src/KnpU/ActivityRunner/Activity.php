<?php

namespace KnpU\ActivityRunner;

use Doctrine\Common\Collections\Collection;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class Activity
{
    /**
     * Logical name of the file to be considered as the entry point.
     *
     * @var string
     */
    private $entryPointFilename;

    /**
     * @var Collection
     */
    private $inputFiles = array();

    /**
     * @var string
     */
    private $workerName;

    private $assertExpressions = array();

    /**
     * @var string Any PHP code that should be run beforehand
     *
     * This exact implementation of this is left up to each worker
     */
    private $contextSource;

    public function __construct($workerName, $entryPointFilename)
    {
        $this->workerName = $workerName;
        $this->entryPointFilename = $entryPointFilename;
    }

    public function addInputFile($filename, $source)
    {
        $this->inputFiles[$filename] = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntryPointFilename()
    {
        return $this->entryPointFilename;
    }

    public function getInputFiles()
    {
        return $this->inputFiles;
    }

    public function getWorkerName()
    {
        return $this->workerName;
    }

    public function addAssertExpression($assertExpression)
    {
        $this->assertExpressions[] = $assertExpression;

        return $this;
    }

    public function getAssertExpressions()
    {
        return $this->assertExpressions;
    }

    public function getContextSource()
    {
        return $this->contextSource;
    }

    public function setContextSource($contextSource)
    {
        $this->contextSource = $contextSource;

        return $this;
    }
}
