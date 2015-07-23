<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Assert\AssertSuite;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
interface WorkerInterface
{
    /**
     * Renders the activity. Rendering can mean different types of processing
     * - e.g. Twig templates would be rendered whereas PHP files would be
     * processed. Nevertheless, the results are returned in a unified result
     * object.
     *
     * @param Activity $activity  The activity to render
     *
     * @return \KnpU\ActivityRunner\Result
     */
    function execute(Activity $activity);

    /**
     * Does the worker support the file?
     *
     * @param string $fileName  Logical name of the file
     * @param array $context    Current context (e.g. Twig parameters)
     *
     * @return boolean
     */
    function supports($fileName, array $context);

    /**
     * Gets the name of the worker.
     *
     * @return string
     */
    function getName();
}
