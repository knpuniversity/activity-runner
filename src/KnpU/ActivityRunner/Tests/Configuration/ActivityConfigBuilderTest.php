<?php

namespace KnpU\ActivityRunner\Tests\Configuration;

use KnpU\ActivityRunner\Configuration\ActivityConfigBuilder;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider notStringOrArrayProvider
     * @expectedException KnpU\ActivityRunner\Exception\UnexpectedTypeException
     */
    public function testBuildFailsIfArgumentNotArray($paths)
    {
        $builder = new ActivityConfigBuilder(
            $this->getMockProcessor(),
            $this->getMockConfiguration(),
            $this->getMockYaml()
        );

        $builder->build($paths);
    }

    public function notStringOrArrayProvider()
    {
        return array(
            array(false),
            array(new \stdClass()),
            array(null),
        );
    }

    /**
     * @dataProvider entryPointProvider
     *
     * @param string $configEntryPoint
     * @param string $expectedEntryPoint
     */
    public function testResolvingEntryPoints($configEntryPoint, $expectedEntryPoint)
    {
        $baseDir = __DIR__.'/../Fixtures/';
        $config = array(
            'child' => array(
                'skeletons'   => array('foo.html.twig' => 'skeleton.html.twig'),
                'entry_point' => $configEntryPoint,
                'context'     => 'baz.php',
            )
        );

        $builder = $this->bootBuilder($config);

        $config = $builder->build($baseDir.'metadata.yml');

        $this->assertEquals($expectedEntryPoint, $config['child']['entry_point']);
    }

    public function entryPointProvider()
    {
        return array(
            array('foo.html.twig', 'foo.html.twig'),
            array('context.php', 'context.php'),
            array(__DIR__.'/../Fixtures/context.php', __DIR__.'/../Fixtures/context.php'),
        );
    }

    private function bootBuilder(array $config)
    {
        $processor     = $this->getMockProcessor();
        $configuration = $this->getMockConfiguration();
        $yaml          = $this->getMockYaml();

        $yaml
            ::staticExpects($this->any())
            ->method('parse')
            ->will($this->returnValue($config))
        ;

        $processor
            ->expects($this->any())
            ->method('processConfiguration')
            ->will($this->returnCallback(function ($ignored, array $config) {
                return $config[0];
            }))
        ;

        return new ActivityConfigBuilder($processor, $configuration, $yaml);
    }

    private function getMockProcessor()
    {
        return $this->getMock('Symfony\\Component\\Config\\Definition\\Processor');
    }

    private function getMockConfiguration()
    {
        return $this->getMock('Symfony\\Component\\Config\\Definition\\ConfigurationInterface');
    }

    private function getMockYaml()
    {
        return $this->getMock('Symfony\\Component\\Yaml\\Yaml');
    }
}
