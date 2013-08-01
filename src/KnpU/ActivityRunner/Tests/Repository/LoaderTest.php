<?php

namespace KnpU\ActivityRunner\Tests\Repository;

use KnpU\ActivityRunner\Repository\Loader;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadReturnsRepository()
    {
        $loader = new Loader($this->getMockStrategy());
        $loader->setCommandFunc(function () { return 'echo "foo"'; });

        $repository = $loader->load('foo/baz', 'master');

        $this->assertInstanceOf('KnpU\\ActivityRunner\\Repository\\Repository', $repository);
    }

    public function testLoadRunsCommand()
    {
        $strategy = $this->getMockStrategy();
        $this->mockCreate($strategy, 'my/custom/path');

        $test = $this;

        $loader = new Loader($strategy);
        $loader->setCommandFunc(function ($url, $path, $ref) use ($test) {
            $test->assertEquals($url, 'foo/baz');
            $test->assertEquals($path, 'my/custom/path');
            $test->assertEquals($ref, 'master');
        });

        $loader->load('foo/baz', 'master');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadFailsIfCommandNotSuccessful()
    {
        $loader = new Loader($this->getMockStrategy());
        $loader->setCommandFunc(function () {
            return 'false';
        });

        $loader->load('foo', 'baz');
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param string $value
     * @param null|\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     */
    private function mockCreate(
        \PHPUnit_Framework_MockObject_MockObject $mock,
        $value = 'foo',
        \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher = null
    ) {
        return $mock
            ->expects($matcher ?: $this->once())
            ->method('create')
            ->will($this->returnValue($value))
        ;
    }

    /**
     * @return \KnpU\ActivityRunner\Repository\Naming\Strategy|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockStrategy()
    {
        return $this->getMock('KnpU\\ActivityRunner\\Repository\\Naming\\Strategy');
    }
}
