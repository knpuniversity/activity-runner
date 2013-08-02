<?php

namespace KnpU\ActivityRunner\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\ActivityInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ActivityController
{
    /**
     * Checks user's answers.
     *
     * Mandatory POST parameters:
     *
     *  -  activity    Name of the activity
     *  -  file[]      Submitted files (e.g. 'file[foo.php]=...&file[foo.php]=...')
     *  -  repository  URL of the repository
     *  -  ref         Repository ref (branch name, commit hash etc.)
     *
     * Optional POST parameters:
     *
     *  -  output-format  Either 'yaml' or 'json'
     *
     * @param Request $request
     * @param Application $app
     */
    public function checkAction(Request $request, Application $app)
    {
        $activityName = $request->request->get('activity');
        $inputFiles = new ArrayCollection($request->request->get('file', array()));

        $url = $request->request->get('repository');
        $ref = $request->request->get('ref');

        $repository = $app['repository.loader']->load($url, $ref);

        $activity = $repository->getActivity($activityName);
        $activity->setInputFiles($inputFiles);

        $result = $app['activity_runner']->run($activity);
        $result->setFormat($request->request->get('output-format', 'yaml'));

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
