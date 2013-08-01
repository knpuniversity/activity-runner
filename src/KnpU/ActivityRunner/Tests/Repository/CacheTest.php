<?php

namespace KnpU\ActivityRunner\Tests\Repository;

use KnpU\ActivityRunner\Repository\Cache;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class CaceTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadReturnsCached()
    {
        $filesystem = $this->getMockFilesystem();
        $this->mockExists($filesystem, true);

        $cache = new Cache(
            $this->getMockLoader(),
            $this->getMockStrategy(),
            $filesystem
        );

        $repository = $cache->load('foo', 'baz');

        $this->assertInstanceOf('KnpU\\ActivityRunner\\Repository\\Repository', $repository);
    }

    public function testLoadUsesParentLoader()
    {
        $repository = $this->getMockRepository();

        $loader = $this->getMockLoader();

        $loader
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($repository))
        ;

        $filesystem = $this->getMockFilesystem();
        $this->mockExists($filesystem, false);

        $cache = new Cache(
            $loader,
            $this->getMockStrategy(),
            $filesystem
        );

        $this->assertSame($repository, $cache->load('foo', 'baz'));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param boolean $value
     * @param null|\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     */
    private function mockExists(
        \PHPUnit_Framework_MockObject_MockObject $mock,
        $value = true,
        \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher = null
    ) {
        return $mock
            ->expects($matcher ?: $this->once())
            ->method('exists')
            ->will($this->returnValue($value))
        ;
    }

    /**
     * @return \Symfony\Component\Filesystem\Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFilesystem()
    {
       return $this->getMock('Symfony\\Component\\Filesystem\\Filesystem');
    }

    /**
     * @return \KnpU\ActivityRunner\Repository\LoaderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockLoader()
    {
        return $this->getMock('KnpU\\ActivityRunner\\Repository\\LoaderInterface');
    }

    /**
     * @return \KnpU\ActivtyRunner\Repository\Repository|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockRepository()
    {
        return $this
            ->getMockBuilder('KnpU\\ActivityRunner\\Repository\\Repository')
            ->disableOriginalConstructor()
            ->getMock()
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
