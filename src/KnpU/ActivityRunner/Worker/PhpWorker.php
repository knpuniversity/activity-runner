<?php

namespace KnpU\ActivityRunner\Worker;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\Worker\Executor\CodeExecutor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class PhpWorker implements WorkerInterface
{
    /**
     * @var string
     */
    protected $prefix = 'knpu_php_';

    /**
     * The maximum amount of time the PHP process can take to execute.
     *
     * @var integer
     */
    protected $timeout = 10;

    public function getInlineCodeToExecute(\Twig_Environment $twig, Activity $activity)
    {
        $challenge = $activity->getChallengeObject();

        return $twig->render(
            'php_worker.php.twig',
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
        return 'php_normal';
    }
}
