<?php

namespace KnpU\ActivityRunner;
use Doctrine\Common\Collections\Collection;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
interface ActivityInterface
{
    /**
     * Gets the logical name of the file to be considered as the entry point.
     * This is guaranteed to be the name of one of the input files.
     *
     * @return string
     */
    function getEntryPoint();

    /**
     * Gets the user input files.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    function getInputFiles();

    /**
     * Sets a collection of files
     *
     * The files are a key-value with the key being the filename and the
     * value being the actual contents of that file.
     *
     * @param Collection $collection
     * @return void
     */
    function setInputFiles(Collection $collection);

    /**
     * Gets an array of key-value pairs defining the current context in which.
     * the activity should be run with.
     *
     * @return array
     */
    function getContext();

    /**
     * Gets the activity question.
     *
     * @return string
     */
    function getQuestion();

    /**
     * Gets the assert suite.
     *
     * @return \KnpU\ActivityRunner\Assert\AssertSuiteInterface
     */
    function getSuite();

    /**
     * Gets the name of the worker, which should run this activity.
     *
     * @return string
     */
    function getWorkerName();
}
