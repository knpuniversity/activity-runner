<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Assert\AssertSuite;
use KnpU\ActivityRunner\Assert\TwigAwareInterface;
use KnpU\ActivityRunner\ErrorHandler\TwigErrorHandler;
use KnpU\ActivityRunner\Exception\TwigException;
use KnpU\ActivityRunner\Worker\WorkerInterface;
use KnpU\ActivityRunner\Result;

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

    public function __construct()
    {
        // At this point we really don't know exactly which loader to use. This
        // will be decided on a per run basis.
        $this->twig = new \Twig_Environment(null, array(
            'cache'            => false,
            'debug'            => true,
            'strict_variables' => true,
        ));

        $this->twig->addExtension(new \Twig_Extension_Debug());
    }

    /**
     * {@inheritDoc}
     */
    public function render(ActivityInterface $activity)
    {
        $inputFiles = $activity->getInputFiles();
        $entryPoint = $activity->getEntryPoint();
        $context    = $activity->getContext();

        $this->twig->setLoader(new \Twig_Loader_Array($inputFiles->toArray()));

        $result = new Result();
        $result->setInputFiles($inputFiles);

        $errorHandler = TwigErrorHandler::register();

        try {
            $output = $this->twig->render($entryPoint, $context);
            $result->setOutput($output);

            $errorHandler->restore();
        } catch (\Twig_Error $error) {
            $errorHandler->restore();

            if (($previous = $error->getPrevious()) && $previous instanceof TwigException) {
                // Treat TwigException errors as validation errors.
                $result->setValidationErrors(array($error->getMessage()));
            } else {
                $result->setLanguageError($error->getMessage());
            }
        } catch (\Exception $error) {
            $errorHandler->restore();

            $result->setLanguageError($error->getMessage());
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function injectInternals(AssertSuite $suite)
    {
        if ($suite instanceof TwigAwareInterface) {
            $suite->setTwig($this->twig);
        }
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
