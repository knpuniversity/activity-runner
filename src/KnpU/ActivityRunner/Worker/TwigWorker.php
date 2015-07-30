<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Assert\AssertSuite;
use KnpU\ActivityRunner\Assert\TwigAwareInterface;
use KnpU\ActivityRunner\ErrorHandler\TwigErrorHandler;
use KnpU\ActivityRunner\Exception\TwigException;
use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\Worker\Executor\CodeExecutor;

/**
 * The twig worker is capable of rendering Twig templates and running
 * assertions on it to verify their correctness.
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class TwigWorker implements WorkerInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    private $projectRootDir;

    public function __construct(\Twig_Environment $twig, $projectRootDir)
    {
        $this->twig = $twig;
        $this->projectRootDir = $projectRootDir;
    }

    public function getInlineCodeToExecute(\Twig_Environment $twig, Activity $activity)
    {
        $challenge = $activity->getChallengeObject();

        return $twig->render(
            'twig_worker.php.twig',
            array(
                'entryPointFilename' => $challenge->getFileBuilder()->getEntryPointFilename()
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'twig_normal';
    }
}
