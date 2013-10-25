<?php

namespace KnpU\ActivityRunner\Assert;

use Doctrine\Common\Annotations\Reader;
use KnpU\ActivityRunner\Assert\Suite\RunIf;
use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Result;

/**
 * Handles executing the "Suite" after an activity has been executed to see if it is correct
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class Asserter implements AsserterInterface
{
    /**
     * @var Reader
     */
    protected $annotationsReader;

    /**
     * Used if no annotation is specified
     *
     * @var RunIf
     */
    protected $defaultAnnotation;

    /**
     * Cached test results. Each element consists of a list of outputs run
     * with that specific suite. The key is the object hash of the test suite.
     *
     * Example structure:
     *
     *     array(
     *         'suite_a_hash' => array(
     *             'output_a_hash' => array(...),
     *             'output_b_hash' => array(...),
     *         ),
     *         'suite_b_hash' => array(
     *             'output_a_hash' => array(...),
     *             'output_c_hash' => array(...),
     *         ),
     *     )
     *
     * @var array
     */
    private $cachedResults = array();

    /**
     * @param Reader $annotationsReader
     * @param RunIf $defaultAnnotation
     */
    public function __construct(Reader $annotationsReader, RunIf $defaultAnnotation)
    {
        $this->annotationsReader = $annotationsReader;
        $this->defaultAnnotation = $defaultAnnotation;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(Result $result, ActivityInterface $activity)
    {
        $suite = $activity->getSuite();

        if ($cachedFailures = $this->getCachedFailures($suite, $result->getOutput())) {
            return $cachedFailures;
        }

        $test = new \ReflectionMethod($suite, 'runTest');
        $annotation = $this->annotationsReader->getMethodAnnotation($test, 'KnpU\\ActivityRunner\\Assert\\Suite\\RunIf');

        if (!$annotation) {
            $annotation = $this->defaultAnnotation;
        }

        if (!$annotation->isAllowedToRun($result)) {
            return array();
        }

        $failures = array();

        try {
            $test->invoke($suite, $result);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $failures[] = $e->getMessage();
        } catch (\PHPParser_Error $e) {
            $failures[] = $e->getMessage();
        }

        $this->cacheFailures($suite, $result->getOutput(), $failures);

        return $failures;
    }

    /**
     * Finds all public methods from the suite prefixed with `test`.
     *
     * @param AssertSuiteInterface $suite
     *
     * @return \ReflectionMethod[]
     */
    protected function getSuiteTests(AssertSuite $suite)
    {
        $reflSuite = new \ReflectionClass($suite);
        $tests     = array();

        /** @var $test \ReflectionMethod */
        foreach ($reflSuite->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {

            if ('test' === substr($method->getName(), 0, 4)) {
                $tests[] = $method;
            }
        }

        return $tests;
    }

    /**
     * @param AssertSuiteInterface $suite
     * @param string $output
     *
     * @return array|null  Cached failures or null indicating that the failures have not been cached
     */
    private function getCachedFailures(AssertSuiteInterface $suite, $output)
    {
        $suiteHash  = spl_object_hash($suite);
        $outputHash = crc32($output);

        if (isset($this->cachedResults[$suiteHash], $this->cachedResults[$suiteHash][$outputHash])) {
            return $this->cachedResults[$suiteHash][$outputHash];
        }
    }

    /**
     * @param AssertSuiteInterface $suite
     * @param string $output
     * @param array $failures
     */
    private function cacheFailures(AssertSuiteInterface $suite, $output, array $failures)
    {
        $suiteHash  = spl_object_hash($suite);
        $outputHash = crc32($output);

        if (!isset($this->cachedResults[$suiteHash])) {
            $this->cachedResults[$suiteHash] = array();
        }

        $this->cachedResults[$suiteHash][$outputHash] = $failures;
    }
}
