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

        $config  = $app['config_builder']->build($configPath);
        $factory = $app['activity_factory'];
        $factory->setConfig($config);

        $activity = $factory->createActivity($activityName, $inputFiles);

        $worker = $app['worker_bag']->get($config[$activityName]['worker']);

        $result = $worker->render($activity);

        // Only validates, if we're at least somewhat valid.
        if ($result->isValid()) {
            $asserter = $app['asserter'];

            // Verify the output.
            if (!$asserter->isValid($result, $activity)) {
                $errors = $asserter->getValidationErrors($result, $activity);

                $result->setValidationErrors($errors);
            }
        }

        $result->setFormat('json');

        return (string) $result;
    }
}
