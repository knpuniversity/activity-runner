<?php

namespace KnpU\ActivityRunner;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class Result
{
    /**
     * @var Collection
     */
    protected $inputFiles;

    /**
     * @var string
     */
    protected $output;

    /**
     * @var array
     */
    protected $validationErrors = array();

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

    public function __construct($output = '')
    {
        $this->inputFiles = new ArrayCollection();
        $this->output = $output;
    }

    /**
     * @param Collection $inputFiles
     */
    public function setInputFiles(Collection $files)
    {
        $this->inputFiles = $files;
    }

    /**
     * @return Collection
     */
    public function getInputFiles()
    {
        return $this->inputFiles;
    }

    /**
     * Returns the submitting input
     *
     * You can leave filename blank if there is only one file
     *
     * @param null|string $filename
     * @return string
     * @throws \LogicException
     */
    public function getInput($filename = null)
    {
        $inputs = $this->getInputFiles();
        if ($filename === null) {
            if (count($inputs) > 1) {
                throw new \InvalidArgumentException(sprintf('TestSuite: You must call getInput() with a filename because there are multiple files.'));
            }

            return $inputs->first();
        }

        if (!isset($inputs[$filename])) {
            throw new \LogicException(sprintf(
                'No file named `%s` found as an input file, possible values are: `%s`',
                $filename,
                implode('`, `', $inputs->getKeys())
            ));
        }

        return $inputs[$filename];
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
     * @param array $validationErrors
     */
    public function setValidationErrors(array $validationErrors)
    {
        // Remove PHPUnit generated automatic 'Failed asserting that ...'
        // validation messages. I tried using composer to override
        // PHPUnit_Framework_Constraint and implement our own version of
        // the `fail` method, but some constraints implement their own
        // version of it, so that solution would have been even dirtier.

        $pattern = "\nFailed asserting that";

        foreach ($validationErrors as $key => $validationError) {
            $endPos = strrpos($validationError, $pattern);

            // Only removes the text, if its not the only part of the
            // validation error message.
            if (false !== $endPos && 0 !== $endPos) {
                $validationErrors[$key] = substr($validationError, 0, $endPos);
            }
        }

        $this->validationErrors = $validationErrors;
    }

    /**
     * @param string $syntaxError
     */
    public function setLanguageError($syntaxError)
    {
        $this->languageError = $syntaxError;
    }

    /**
     * @return boolean
     */
    public function hasLanguageError()
    {
        return (boolean) $this->languageError;
    }

    /**
     * Does this result appear to be valid
     *
     * @return bool
     */
    public function isValid()
    {
        return count($this->validationErrors) < 1 && !$this->languageError;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'input'  => $this->inputFiles->toArray(),
            'output' => $this->output,
            'valid'  => $this->isValid(),
            'errors' => array(
                'validation' => $this->validationErrors,
                'language'   => $this->languageError
            ),
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result     = $this->toArray();

        $options = 0;

        if (defined('JSON_PRETTY_PRINT')) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($result, $options);
    }

    /**
     * @return array
     */
    public function getFinalFileContents()
    {
        return $this->finalFileContents;
    }

    /**
     * @param array $finalFileContents
     */
    public function setFinalFileContents($finalFileContents)
    {
        $this->finalFileContents = $finalFileContents;
    }
}
