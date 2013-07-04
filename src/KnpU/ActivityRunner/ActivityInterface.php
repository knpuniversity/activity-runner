<?php

namespace KnpU\ActivityRunner;

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
     * Gets the file contents of the specified skeleton.
     *
     * @param string $logicalName  Logical name of a skeleton file (e.g. `foo.html.twig`)
     *
     * @return string
     */
    function getSkeletonContents($logicalName);

    /**
     * Gets the assert suite.
     *
     * @return KnpU\ActivityRunner\Assert\AssertSuite
     */
    function getSuite();
}
