<?php

namespace KnpU\ActivityRunner;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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
     * @var integer
     */
    protected $verbosity;

    /**
     * @var string
     */
    protected $format;

    public function __construct($output = '')
    {
        $this->inputFiles = new ArrayCollection();
        $this->output = $output;
        $this->verbosity = OutputInterface::VERBOSITY_NORMAL;
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
        if ($this->verbosity <= OutputInterface::VERBOSITY_NORMAL) {
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
     * @param integer $verbosity
     */
    public function setVerbosity($verbosity)
    {
        if (OutputInterface::VERBOSITY_QUIET > $verbosity) {
            throw new \InvalidArgumentException(sprintf("Got unknown verbosity level %s, expected it to be more than %d.", $verbosity, OutputInterface::VERBOSITY_QUIET));
        }

        $this->verbosity = $verbosity;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $allowed = array(
            'yaml',
            'array',
            'json',
        );

        if (!in_array($format, $allowed)) {
            throw new \InvalidArgumentException(sprintf("The `%s` format is not supported, but the following are: `%s`.", $format, implode('`, `', $allowed)));
        }

        $this->format = $format;
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
        $outputText = '';

        switch ($this->format) {
            case 'yaml':
                $outputText = Yaml::dump($result);
                break;
            case 'array':
                $outputText = print_r($result, true);
                break;
            case 'json':
                $options = 0;

                if (defined('JSON_PRETTY_PRINT')) {
                    $options |= JSON_PRETTY_PRINT;
                }

                $outputText = json_encode($result, $options);
                break;
        }

        return $outputText;
    }
}
