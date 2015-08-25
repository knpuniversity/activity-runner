<?php

namespace KnpU\ActivityRunner\Controller;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Activity\CodingChallengeInterface;
use KnpU\ActivityRunner\ActivityRunner;
use Silex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthorController
{
    /**
     * Allows the user to enter a directory
     *
     * @param Request $request
     * @param Application $app
     * @return string
     */
    public function enterFilenameAction(Request $request, Application $app)
    {
        if ($dir = $request->query->get('filename')) {
            $url = $app['url_generator']
                ->generate('render_activity').'?path='.$dir;

            return new RedirectResponse($url);

        }

        $html = $this->getTwig($app)->render('author/enterFilename.twig');

        return new Response($html);
    }

    public function renderActivityAction(Request $request, Application $app)
    {
        $path = $request->query->get('path');
        if (!$path) {
            throw new NotFoundHttpException('Missing path!');
        }

        $challenge = $this->createChallengeFromPath($path);

        $fileBuilder = $challenge->getFileBuilder();
        $correctAnswer = Activity\CodingChallenge\CorrectAnswer::createFromFileBuilder($fileBuilder);
        $challenge->configureCorrectAnswer($correctAnswer);

        $html = $this->getTwig($app)->render('author/renderActivity.twig', array(
            'challenge' => $challenge,
            'fileBuilder' => $fileBuilder,
            'path' => $path,
            'gradingUrl' => $app['url_generator']->generate('grade_activity'),
            'correctAnswer' => $correctAnswer,
        ));

        return new Response($html);
    }

    /**
     * Grading endpoint - similar to ActivityController::checkAction(), but
     * loads the file dynamically from the filesystem, which is handy for
     * tweaking grading without refreshing
     *
     * @param Request $request
     * @param Application $app
     * @return JsonResponse
     */
    public function gradeAction(Request $request, Application $app)
    {
        // an associative array of filenames => contents
        $inputFiles = $request->request->get('files', array());
        $path = $request->request->get('path');

        $challenge = $this->createChallengeFromPath($path);

        $activity = new Activity(
            get_class($challenge),
            file_get_contents($path)
        );
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
     * @param Application $app
     * @return \Twig_Environment
     */
    private function getTwig(Application $app)
    {
        return $app['twig'];
    }

    /**
     * Recursively looks up directories until a metadata.yml is spotted
     *
     * @param $path
     * @return bool
     */
    private function findRootMetadataDirectory($path)
    {
        // in case we're passed a file at first
        if (!is_dir($path)) {
            return $this->findRootMetadataDirectory(dirname($path));
        }

        $finder = new Finder();
        $finder->in($path)
            ->name('metadata.yml')
            ->depth(0);

        if (count($finder) > 0) {
            return $path;
        }

        $parentDir = dirname($path);
        if ($parentDir == $path) {
            // we got to the top directory!
            return false;
        }

        return $this->findRootMetadataDirectory($parentDir);
    }

    /**
     * @param $path
     * @return CodingChallengeInterface
     */
    private function createChallengeFromPath($path)
    {
        if (!file_exists($path)) {
            throw new NotFoundHttpException(sprintf('Bad path "%s"', $path));
        }

        $rootDir = $this->findRootMetadataDirectory($path);
        if ($rootDir === false) {
            throw new \LogicException(sprintf(
                'Could not find metadata.yml in any parent directory of %s',
                $path
            ));
        }

        // get the path relative to the root directory
        $relativePath = str_replace($rootDir.'/', '', $path);
        // turn this into a class name
        $class = substr(str_replace('/', '\\', $relativePath), 0, -4);

        require $path;

        if (!class_exists($class)) {
            throw new \LogicException(sprintf(
                'Class "%s" was not found after requiring "%s"',
                $class,
                $path
            ));
        }

        $challenge = new $class();
        if (!$challenge instanceof CodingChallengeInterface) {
            throw new \LogicException(sprintf('"%s" does not implement CodingChallengeInterface', $class));
        }

        return $challenge;
    }
}
