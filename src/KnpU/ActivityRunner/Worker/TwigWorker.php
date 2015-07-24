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

    /**
     * {@inheritDoc}
     */
    public function execute(Activity $activity)
    {
        $inputFiles = $activity->getInputFiles();
        $entryPointFilename = $activity->getEntryPointFilename();

        $twigRenderingCode = $this->twig->render('twig_worker.render.php.twig', array(
            'context' => $activity->getContextSource(),
            'entryPointFilename' => $entryPointFilename,
            'projectPath'  => $this->projectRootDir
        ));
        $inputFiles['twigWorkerRender.php'] = $twigRenderingCode;

        $codeExecutor = new CodeExecutor($inputFiles, 'twigWorkerRender.php');
        $executionResult = $codeExecutor->executePhpProcess();

        // unless we had a syntax error, the output should be a serialized ExecutionResult
        // of what happened internally. If so, we use that - it'll contain Twig error
        // details, etc
        if (!$executionResult->getOutput()) {
            throw new \Exception(sprintf(
                'Problem running Twig! "%s"',
                $executionResult->getLanguageError()
            ));
        }

        $executionResult = unserialize($executionResult->getOutput());
        if ($executionResult === false) {
            throw new \Exception(sprintf(
                'Problem running Twig! Got non-serialized output: "%s"'
            ));
        }

        $result = new Result($activity);
        $result->setLanguageError($executionResult->getLanguageError());
        $result->setOutput($executionResult->getOutput());

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'twig';
    }

    /**
     * {@inheritDoc}
     */
    public function supports($fileName, array $context)
    {
        return substr($fileName, -5) === '.twig';
    }
}
