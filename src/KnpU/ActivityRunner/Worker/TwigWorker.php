<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\Assert\AssertSuite;
use KnpU\ActivityRunner\Assert\TwigAwareInterface;
use KnpU\ActivityRunner\ErrorHandler\TwigErrorHandler;
use KnpU\ActivityRunner\Exception\TwigException;
use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\Worker\Executor\CodeExecutor;
use Symfony\Component\Debug\Debug;

/**
 * The twig worker is capable of rendering Twig templates and running
 * assertions on it to verify their correctness.
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class TwigWorker implements WorkerInterface
{
    /**
     * Execute the code and modify the CodingExecutionResult
     *
     * @param string $rootDir Where all the files have been placed
     * @param string $entryPointFilename
     * @param CodingContext $context
     * @param CodingExecutionResult $result
     */
    public function executeCode($rootDir, $entryPointFilename, CodingContext $context, CodingExecutionResult $result)
    {
        Debug::enable();

        $errorHandler = TwigErrorHandler::register();
        $twig = $this->getTwigEnvironment($rootDir);
        try {
            $output = $twig->render(
                $entryPointFilename,
                $context->getVariables()
            );
            $result->setOutput($output);
        } catch (TwigException $error) {
            $result->setLanguageError($error->getMessage());
        } catch (\Twig_Error $error) {
            // not doing anything special here... but in the future, we might
            // fetch more information about line numbers, etc
            $result->setLanguageError($error->getMessage());
        } catch (\Exception $error) {
            $result->setLanguageError($error->getMessage());
        }

        $errorHandler->restore();
    }

    private function getTwigEnvironment($rootDir)
    {
        $loader = new \Twig_Loader_Filesystem(array(
            $rootDir
        ));
        $environment = new \Twig_Environment($loader, array(
            'cache' => false,
            'debug' => true,
            'strict_variables' => true,
        ));

        $environment->addExtension(new \Twig_Extension_Debug());

        return $environment;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'twig_normal';
    }
}
