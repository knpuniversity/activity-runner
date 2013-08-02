<?php

namespace KnpU\ActivityRunner\Tests\Repository;

use KnpU\ActivityRunner\Repository\Configurator;
use KnpU\ActivityRunner\Repository\Repository;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ConfiguratorTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadSetsFactory()
    {
        $repository = $this->getMockRepository();
        $repository
            ->expects($this->once())
            ->method('setActivityFactory')
        ;

        $loader = $this->getMockLoader();
        $this->mockLoad($loader, $repository);

        $factory = $this->getMockActivityFactory();

        $builder = $this->getMockActivityConfigBuilder();
        $this->mockBuild($builder);

        $configurator = new Configurator($loader, $factory, $builder);
        $configurator->load('foo', 'baz');
    }

    public function testLoadReturnsRepository()
    {
        $repository = $this->getMockRepository();

        $loader = $this->getMockLoader();
        $this->mockLoad($loader, $repository);

        $factory = $this->getMockActivityFactory();

        $builder = $this->getMockActivityConfigBuilder();
        $this->mockBuild($builder);

        $configurator = new Configurator($loader, $factory, $builder);
        $this->assertSame($repository, $configurator->load('foo', 'baz'));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param Repository $value
     * @param null|\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     */
    private function mockLoad(
        \PHPUnit_Framework_MockObject_MockObject $mock,
        Repository $value,
        \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher = null
    ) {
        $mock
            ->expects($matcher ?: $this->once())
            ->method('load')
            ->will($this->returnValue($value))
        ;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $value
     * @param null|\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     */
    private function mockBuild(
        \PHPUnit_Framework_MockObject_MockObject $mock,
        array $value = array(),
        \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher = null
    ) {
        $mock
            ->expects($matcher ?: $this->once())
            ->method('build')
            ->will($this->returnValue($value))
        ;
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
     * @return \KnpU\AcitivityRunner\Factory\ActivityFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockActivityFactory()
    {
        return $this
            ->getMockBuilder('KnpU\\ActivityRunner\\Factory\\ActivityFactory')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \KnpU\ActivityRunner\Configuration\ActivityConfigBuilder|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockActivityConfigBuilder()
    {
        return $this
            ->getMockBuilder('KnpU\\ActivityRunner\\Configuration\\ActivityConfigBuilder')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
