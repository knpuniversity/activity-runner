<?php

namespace KnpU\ActivityRunner\Worker;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\ActivityRunner;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\Exception\ContextErrorException;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class PhpWorker implements WorkerInterface
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
        // makes all notices/warning into exceptions, which is good!
        Debug::enable();

        $languageError = null;

        extract($context->getVariables());
        ob_start();
        try {
            require $rootDir.'/'.$entryPointFilename;
        } catch (\ErrorException $e) {
            $message = sprintf(
                // matches normal syntax error phrasing
                '%s in %s on line %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
            $languageError = $message;
        }
        $contents = ob_get_contents();
        ob_end_clean();

        $result->setOutput($contents);
        $result->setLanguageError(
            ActivityRunner::cleanError($languageError, $rootDir)
        );
        $result->setDeclaredVariables(get_defined_vars());
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'php_normal';
    }
}
