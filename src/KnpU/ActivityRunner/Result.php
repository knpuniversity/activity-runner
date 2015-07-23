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
    protected $validationError;

    /**
     * @var string
     */
    protected $languageError;

    /**
     * Key-value of what files look like after running the process
     *
     * This is useful if the script writes to some files
     *
     * @var array
     */
    protected $finalFileContents = array();

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
     * @param string $validationError
     */
    public function setValidationError($validationError)
    {
        $this->validationError = $validationError;
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
    public function isValid()
    {
        return !$this->validationError && !$this->languageError;
    }

    public function addFinalFileContents($filename, $contents)
    {
        $this->finalFileContents[$filename] = $contents;
    }

    public function getValidationError()
    {
        return $this->validationError;
    }

    public function getLanguageError()
    {
        return $this->languageError;
    }
}
