<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

use KnpU\ActivityRunner\Activity\Exception\GradingException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
     * @param $variableKey
     * @return mixed
     * @throws GradingException
     */
    public function getDeclaredVariableValue($variableKey)
    {
        list($variableName, $propertyAccessString) = self::splitVariableAndAccessorString($variableKey);

        if (!$this->isVariableDeclared($variableName)) {
            throw new GradingException(sprintf('The variable "%s" is not defined', $variableName));
        }

        $value = $this->declaredVariables[$variableName];

        if (!$propertyAccessString) {
            return $value;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($value, $propertyAccessString);
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

    public function doesOutputContain($needle, $caseSensitive = false)
    {
        return $this->stringContains($this->getOutput(), $needle, $caseSensitive);
    }

    /**
     * @param string $needle
     * @param string $gradingErrorMessage
     * @param bool|false $caseSensitive
     * @throws GradingException
     */
    public function assertOutputDoesNotContain($needle, $gradingErrorMessage = null, $caseSensitive = false)
    {
        if ($this->stringContains($this->getOutput(), $needle, $caseSensitive)) {
            if ($gradingErrorMessage === null) {
                $gradingErrorMessage = sprintf('I see "%s" in the output, but it should not be there!', $needle);
            }

            throw new GradingException($gradingErrorMessage);
        }
    }

    /**
     * Returns the text in the *first* matched element
     *
     * @param $cssSelector
     * @return string|false if the element is not found
     */
    public function getElementText($cssSelector)
    {
        $eles = $this->getCrawler()->filter($cssSelector);

        if (count($eles) === 0) {
            return false;
        }

        return $eles->text();
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
                $gradingErrorMessage = sprintf('I don\'t see `%s` used in `%s`', $string, $filename);
            }

            throw new GradingException($gradingErrorMessage);
        }
    }

    public function assertInputDoesNotContain($filename, $string, $gradingErrorMessage = null, $caseSensitive = false)
    {
        $contents = $this->getInputFileContents($filename);
        if ($this->stringContains($contents, $string, $caseSensitive)) {
            if ($gradingErrorMessage === null) {
                $gradingErrorMessage = sprintf('I see `%s` used in `%`, but it shouldn\'t be there!', $string, $filename);
            }

            throw new GradingException($gradingErrorMessage);
        }
    }

    public function assertFunctionExists($functionName, $gradingErrorMessage = null)
    {
        if ($gradingErrorMessage === null) {
            $gradingErrorMessage = sprintf(
                'The `%s` function does not exist - do you create it?',
                $functionName
            );
        }

        // this works because grading happens in the same PHP thread as execution
        // so if the user created a function, it literally still exists
        if (!function_exists($functionName)) {
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
        if ($gradingErrorMessage === null) {
            $gradingErrorMessage = sprintf(
                'The `$%s` variable exists, but is not set to %s',
                $variableName,
                var_export($expectedValue, true)
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
            // http://localhost:8000 is used here so that the Crawler is happy with
            // selecting forms and links (otherwise it fails in the Link::__construct()
            // I don't think that has any side effects
            $this->crawler = new Crawler($this->getOutput(), 'http://localhost:8000');
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

    /**
     * Helper to split between the variable name and property access spot
     *
     *      foo         => array('foo', null)
     *      foo.bar     => array('foo', 'bar')
     *      foo[bar]    => array('foo', '[bar]')
     *      foo.bar[2]  => array('foo', 'bar[2]')
     *
     * @param string $variableKey
     * @return array
     */
    static private function splitVariableAndAccessorString($variableKey)
    {
        $bracketPos = strpos($variableKey, '[');
        $dotPos = strpos($variableKey, '.');

        // neither appear, so we have a simple variable name
        if ($bracketPos === false && $dotPos === false) {
            return array($variableKey, null);
        }

        // find the position of the first . or [
        if ($bracketPos !== false && $dotPos !== false) {
            $firstPos = min($bracketPos, $dotPos);
        } elseif ($bracketPos !== false) {
            $firstPos = $bracketPos;
        } else {
            $firstPos = $dotPos;
        }

        $variableName = substr($variableKey, 0, $firstPos);
        $propertyAccessKey = substr($variableKey, $firstPos);

        // converts .firstName to just firstName. But leave [firstName] as [firstName]
        if (substr($propertyAccessKey, 0, 1) === '.') {
            $propertyAccessKey = substr($propertyAccessKey, 1);
        }

        return array($variableName, $propertyAccessKey);
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
