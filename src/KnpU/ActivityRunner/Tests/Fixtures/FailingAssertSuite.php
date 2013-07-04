<?php

namespace KnpU\ActivityRunner\Tests\Fixtures;

use KnpU\ActivityRunner\Assert\AssertSuite;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class FailingAssertSuite extends AssertSuite
{
    public function testFailsAlways()
    {
        $this->fail('FooBarBaz');
    }
}
