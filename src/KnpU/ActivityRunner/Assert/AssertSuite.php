<?php

namespace KnpU\ActivityRunner\Assert;

use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Result;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
abstract class AssertSuite extends \PHPUnit_Framework_Assert implements AssertSuiteInterface
{
    /**
     * A helper function so that you can run your suite with PHPUnit
     *
     * @dataProvider provideTests
     */
    public function testActivity(Result $result)
    {
        // being able to run with PHPUnit
        $this->markTestSkipped();
        $debug = true;
        $app = require(__DIR__.'/../../../../app/bootstrap.php');

        var_dump($app);

        $this->runTest($result);
    }

    /**
     * To create automated tests for your suite, override this and return
     * the array of tests (like a normal @dataProvider function)
     *
     * @return Result[]
     */
    public function provideTests()
    {
        return array();
    }

    /**
     * Creates a Symfony crawler.
     *
     * @param string $output
     * @return Crawler
     */
    protected function getCrawler($output)
    {
        return new Crawler($output);
    }
}
