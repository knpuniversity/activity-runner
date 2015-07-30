<?php

namespace KnpU\ActivityRunner\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\ActivityRunner;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $inputFiles = $request->request->get('files', array());

        $challengeClassContents = $request->request->get('challengeClassContents');
        $challengeClassName = $request->request->get('challengeClassName');

        $activity = new Activity($challengeClassName, $challengeClassContents);
        foreach ($inputFiles as $filename => $inputFileSource) {
            $activity->addInputFile($filename, $inputFileSource);
        }

        /** @var ActivityRunner $activityRunner */
        $activityRunner = $app['activity_runner'];

        $result = $activityRunner->run($activity);

        $data = array(
            'output' => $result->getOutput(),
            'isCorrect' => $result->isCorrect(),
            'errors' => array(
                'validation' => $result->getGradingError(),
                'language' => $result->getLanguageError(),
            ),
        );

        return new JsonResponse($data);
    }

    /**
     * Used for checking the health of the server.
     */
    public function statusAction()
    {
        return 'OK';
    }
}
