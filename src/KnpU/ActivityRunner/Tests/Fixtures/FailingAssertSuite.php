<?php

namespace KnpU\ActivityRunner\Tests\Fixtures;

use KnpU\ActivityRunner\Assert\AssertSuite;
use KnpU\ActivityRunner\Result;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class FailingAssertSuite extends AssertSuite
{
    public function runTest(Result $result)
    {
        $this->fail('FooBarBaz');
    }
}
