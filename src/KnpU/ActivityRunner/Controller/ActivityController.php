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
     * @param Request $request
     * @param Application $app
     */
    public function checkAction(Request $request, Application $app)
    {
        $activityName = $request->request->get('activity');
        $configPath   = $request->request->get('config');
        $inputFiles   = new ArrayCollection($request->request->get('file'));

        $activityRunner = $app['activity_runner'];

        if ($configPath = $request->request->get('config')) {
            $activityRunner->setConfigPaths($configPath);
        }

        $result = $activityRunner->run($activityName, $inputFiles);
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
