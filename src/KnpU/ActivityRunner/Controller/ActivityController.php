<?php

namespace KnpU\ActivityRunner\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\ActivityRunner;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use KnpU\ActivityRunner\Repository\Repository;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ActivityController
{
    /**
     * Checks user's answers.
     *
     * @param Request $request
     * @param Application $app
     * @return string
     */
    public function checkAction(Request $request, Application $app)
    {
        // an associative array of filenames => contents
        $inputFiles = $request->request->get('file', array());
        // filename of "file" collection to execute
        $entryPointFilename = $request->request->get('entryPoint');
        // something like php, twig
        $workerName = $request->request->get('worker');
        // expressions to assert against
        $asserts = $request->request->get('asserts');

        $activity = new Activity($workerName, $entryPointFilename);
        foreach ($inputFiles as $filename => $inputFileSource) {
            $activity->addInputFile($filename, $inputFileSource);
        }
        foreach ($asserts as $assertExpression) {
            $activity->addAssertExpression($assertExpression);
        }

        /** @var ActivityRunner $activityRunner */
        $activityRunner = $app['activity_runner'];

        $result = $activityRunner->run($activity);

        $data = array(
            'output' => $result->getOutput(),
            'value'  => $result->isValid(),
            'errors' => array(
                'validation' => $result->getValidationError(),
                'language' => $result->getLanguageError(),
            ),
        );

        return (string) $result;
    }

    /**
     * Used for checking the health of the server.
     */
    public function statusAction()
    {
        return 'OK';
    }
}
