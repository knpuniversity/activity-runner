<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

use KnpU\ActivityRunner\Activity\Exception\GradingException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Used inside the eval'ed scripts themselves to summarize what happened
 *
 * This is passed to the grader
 */
class CodingExecutionResult
{
    private $inputFiles;

    private $output;

    private $declaredVariables = array();

    private $crawler;

    private $exception;

    /**
     * @var string Sometimes, we can detect language errors at runtime (e.g. Twig)
     */
    private $languageError;

    private $gradingError;

    public function __construct(array $inputFiles)
    {
        $this->inputFiles = $inputFiles;
    }

    /**
     * @param string $variableName
     * @return bool
     */
    public function isVariableDeclared($variableName)
    {
        return isset($this->declaredVariables[$variableName]);
    }

    /**
     * @param string $variableName
     * @return mixed
     */
    public function getDeclaredVariableValue($variableName)
    {
        if (!$this->isVariableDeclared($variableName)) {
            throw new \LogicException(sprintf('Variable "%s" was never defined!', $variableName));
        }

        return $this->declaredVariables[$variableName];
    }

    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $needle
     * @param string $gradingErrorMessage
     * @param bool|false $caseSensitive
     * @throws GradingException
     */
    public function assertOutputContains($needle, $gradingErrorMessage = null, $caseSensitive = false)
    {
        if (!$this->stringContains($this->getOutput(), $needle, $caseSensitive)) {
            if ($gradingErrorMessage === null) {
                $gradingErrorMessage = sprintf('I don\'t see "%s" in the output.', $needle);
            }

            throw new GradingException($gradingErrorMessage);
        }
    }

    /**
     * Assert text is found somewhere inside the CSS selector
     *
     * @param string $cssSelector e.g. h2
     * @param string $needle
     * @param string $gradingErrorMessage
     * @param bool|false $caseSensitive
     * @throws GradingException
     */
    public function assertElementContains($cssSelector, $needle, $gradingErrorMessage = null, $caseSensitive = false)
    {
        /** @var Crawler $nodes */
        $nodes = $this->getCrawler()->filter($cssSelector);

        $result = $this;
        foreach ($nodes as $node) {
            /** @var \DOMElement $node */
            if ($result->stringContains($node->nodeValue, $needle, $caseSensitive)) {
                // we found an element!
                return;
            }
        };

        if ($gradingErrorMessage === null) {
            $gradingErrorMessage = sprintf(
                'I don\'t see any "%s" HTML element with the text "%s" in it.',
                $cssSelector,
                $needle
            );
        }

        throw new GradingException($gradingErrorMessage);
    }

    public function assertInputContains($filename, $string, $gradingErrorMessage = null, $caseSensitive = false)
    {
        $contents = $this->getInputFileContents($filename);
        if (!$this->stringContains($contents, $string, $caseSensitive)) {
            if ($gradingErrorMessage === null) {
                $gradingErrorMessage = sprintf('I don\'t see `%s` used in your code', $string);
            }

            throw new GradingException($gradingErrorMessage);
        }
    }

    /**
     * Assert that the user created this variable
     *
     * @param string $variableName
     * @param null $gradingErrorMessage
     * @throws GradingException
     */
    public function assertVariableExists($variableName, $gradingErrorMessage = null)
    {
        if ($gradingErrorMessage === null) {
            $gradingErrorMessage = sprintf('I don\'t see a variable called `%s` - did you set this variable?', $variableName);
        }

        if (!$this->isVariableDeclared($variableName)) {
            throw new GradingException($gradingErrorMessage);
        }
    }

    public function assertVariableEquals($variableName, $expectedValue, $gradingErrorMessage = null)
    {
        $this->assertVariableExists($variableName);

        if ($gradingErrorMessage === null) {
            $gradingErrorMessage = sprintf(
                'The `%s` variable exists, but is not set to %s',
                $variableName,
                $expectedValue
            );
        }

        if ($this->getDeclaredVariableValue($variableName) != $expectedValue) {
            throw new GradingException($gradingErrorMessage);
        }
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool|false $caseSensitive
     * @return bool
     */
    private function stringContains($haystack, $needle, $caseSensitive = false)
    {
        $needle = (string) $needle;
        if ($caseSensitive) {
            $position = strpos($haystack, $needle);
        } else {
            $position = stripos($haystack, $needle);
        }

        return $position !== false;
    }

    /**
     * @return Crawler
     */
    public function getCrawler()
    {
        if ($this->crawler === null) {
            $this->crawler = new Crawler($this->getOutput());
        }

        return $this->crawler;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getInputFileContents($filename)
    {
        if (!isset($this->inputFiles[$filename])) {
            throw new \InvalidArgumentException(sprintf('Unknown file "%s"!', $filename));
        }

        return $this->inputFiles[$filename];
    }

    /*
     * IGNORE EVERYTHING BELOW HERE - used to setup the object
     */

    public function getException()
    {
        return $this->exception;
    }

    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function getLanguageError()
    {
        return $this->languageError;
    }

    public function setLanguageError($languageError)
    {
        $this->languageError = $languageError;
    }

    public function getGradingError()
    {
        return $this->gradingError;
    }

    public function setGradingError($gradingError)
    {
        $this->gradingError = $gradingError;
    }

    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function setDeclaredVariables($declaredVariables)
    {
        $this->declaredVariables = $declaredVariables;
    }
}
