<?php

namespace KnpU\ActivityRunner\Worker\Executor;

class ExecutionResult
{
    private $codeDirectory;

    /**
     * @var string
     */
    private $output;

    /**
     * @var string
     */
    private $languageError;

    /**
     * @var string
     */
    private $gradingError;

    public function __construct($codeDirectory)
    {
        $this->codeDirectory = $codeDirectory;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param string $syntaxError
     */
    public function setLanguageError($syntaxError)
    {
        $this->languageError = $syntaxError;
    }

    public function getLanguageError()
    {
        return $this->languageError;
    }

    public function getCodeDirectory()
    {
        return $this->codeDirectory;
    }

    public function getGradingError()
    {
        return $this->gradingError;
    }

    public function setGradingError($gradingError)
    {
        $this->gradingError = $gradingError;
    }

    public function isCorrect()
    {
        return !$this->getLanguageError() && !$this->getGradingError();
    }
}
