<?php

namespace KnpU\ActivityRunner\Tests\Fixtures;

use KnpU\ActivityRunner\Assert\AssertSuite;
use KnpU\ActivityRunner\Result;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class TestAssertSuite extends AssertSuite
{
    public function runTest(Result $result)
    {
        $crawler = $this->getCrawler($result->getOutput());

        $message = 'The paragraph tag should come right after the heading tag';
        $this->assertEquals(0, $crawler->filter('h1 + p')->count(), $message);
    }
}
