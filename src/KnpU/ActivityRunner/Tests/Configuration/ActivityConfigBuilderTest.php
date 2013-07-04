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

    public function testBuildPrefixesRelativePathsCorrectly()
    {
        $baseDir = __DIR__.'/../Fixtures/';
        $config = array(
            'child' => array(
                'skeletons'   => array('foo.html.twig', 'baz.html.twig'),
                'entry_point' => 0,
                'context'     => 'baz.php',
                'no_change'   => 'this should not change',
            )
        );

        $expected = array(
            'child' => array(
                'skeletons'   => array($baseDir.$config['child']['skeletons'][0], $baseDir.$config['child']['skeletons'][1]),
                'entry_point' => $config['child']['entry_point'],
                'context'     => $baseDir.$config['child']['context'],
                'no_change'   => 'this should not change',
            )
        );

        $builder = $this->bootBuilder($config);

        // We don't actually use the metadata file, just need an existing file
        // in the $baseDir directory.
        $config = $builder->build($baseDir.'metadata.yml');

        $this->assertEquals($expected, $config);
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
            array('context.php', __DIR__.'/../Fixtures/context.php'),
            array(__DIR__.'/../Fixtures/context.php', __DIR__.'/../Fixtures/context.php'),
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testResolvingEntryPointsFailsIfFileNotFound()
    {
        $baseDir = __DIR__.'/../Fixtures/';
        $config = array(
            'child' => array(
                'skeletons'   => array('foo.html.twig' => 'skeleton.html.twig'),
                'entry_point' => 'some nonsense',
                'context'     => 'baz.php',
            )
        );

        $builder = $this->bootBuilder($config);
        $builder->build($baseDir.'metadata.yml');
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
            ->will($this->returnValue($config))
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
