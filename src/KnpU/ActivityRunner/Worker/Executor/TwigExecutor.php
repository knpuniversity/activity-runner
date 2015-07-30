<?php

namespace KnpU\ActivityRunner\Worker\Executor;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\ErrorHandler\TwigErrorHandler;
use KnpU\ActivityRunner\Exception\TwigException;

/**
 * This is actually used inside the user-executed PHP code that runs Twig
 *
 * See twig_worker.render.php.twig
 *
 * It prevents me from putting a TON of PHP code in that template.
 */
class TwigExecutor
{
    private $rootDir;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function executeTwig($templateName, CodingContext $context, CodingExecutionResult $codingExecutionResult)
    {
        $errorHandler = TwigErrorHandler::register();
        try {
            $output = $this->getTwigEnvironment()->render(
                $templateName,
                $context->getVariables()
            );
            $codingExecutionResult->setOutput($output);
        } catch (TwigException $error) {
            $codingExecutionResult->setLanguageError($error->getMessage());
        } catch (\Twig_Error $error) {
            // not doing anything special here... but in the future, we might
            // fetch more information about line numbers, etc
            $codingExecutionResult->setLanguageError($error->getMessage());
        } catch (\Exception $error) {
            $codingExecutionResult->setLanguageError($error->getMessage());
        } finally {
            $errorHandler->restore();
        }

        return $codingExecutionResult;
    }

    private function getTwigEnvironment()
    {
        if ($this->environment === null) {
            $loader = new \Twig_Loader_Filesystem(array(
                $this->rootDir
            ));
            $environment = new \Twig_Environment($loader, array(
                'cache'            => false,
                'debug'            => true,
                'strict_variables' => true,
            ));

            $environment->addExtension(new \Twig_Extension_Debug());

            $this->environment = $environment;
        }

        return $this->environment;
    }
}