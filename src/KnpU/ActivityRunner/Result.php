<?php

namespace KnpU\ActivityRunner;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class Result
{
    protected $activity;

    /**
     * @var string
     */
    protected $output;

    /**
     * @var array
     */
    protected $gradingError;

    /**
     * @var string
     */
    protected $languageError;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
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
     * @param string $gradingError
     */
    public function setGradingError($gradingError)
    {
        $this->gradingError = $gradingError;
    }

    /**
     * @param string $syntaxError
     */
    public function setLanguageError($syntaxError)
    {
        $this->languageError = $syntaxError;
    }

    /**
     * Does this result appear to be valid
     *
     * @return bool
     */
    public function isCorrect()
    {
        return !$this->gradingError && !$this->languageError;
    }

    public function getGradingError()
    {
        return $this->gradingError;
    }

    public function getLanguageError()
    {
        return $this->languageError;
    }
}
