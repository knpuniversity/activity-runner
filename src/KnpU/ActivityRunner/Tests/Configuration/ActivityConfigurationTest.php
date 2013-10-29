<?php

namespace KnpU\ActivityRunner\Tests\Configuration;

use KnpU\ActivityRunner\Configuration\ActivityConfiguration;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testFullConfiguration()
    {
        $rawConfig = array(
            'activities' => array(
                'act_a' => array(
                    'question'    => 'What time is it?',
                    'worker'      => 'php',
                    'skeletons'   => array('foo.html.twig', 'baz.html.twig'),
                    'entry_point' => 'foo.html.twig',
                    'context'     => 'KnpU\\ActivityRunner\\Tests\\Context',
                    'asserts'     => 'KnpU\\ActivityRunner\\Tests\\Assert',
                )
            )
        );

        $activities = $this->process($rawConfig);

        $this->assertEquals($rawConfig['activities'], $activities);
    }

    /**
     * In this test we make sure that the missing configuration values are
     * filled with defaults when omitted.
     *
     * @dataProvider defaultKeyProvider
     *
     * @param string $missingKey
     */
    public function testDefaults($missingKey, $defaultValue = null)
    {
        $rawConfig = array(
            'activities' => array(
                'act_a' => array(
                    'question'    => 'What time is it?',
                    'worker'      => 'php',
                    'skeletons'   => array('foo.html.twig', 'baz.html.twig'),
                    'entry_point' => 'skeletons[0]',
                    'context'     => 'KnpU\\ActivityRunner\\Tests\\Context',
                    'asserts'     => 'KnpU\\ActivityRunner\\Tests\\Assert',
                )
            )
        );

        unset($rawConfig['activities']['act_a'][$missingKey]);

        $activities = $this->process($rawConfig);

        $this->assertArrayHasKey($missingKey, $activities['act_a']);
        if ($defaultValue === null) {
            $this->assertNotEmpty($activities['act_a'][$missingKey]);
        } else {
            $this->assertEquals($defaultValue, $activities['act_a'][$missingKey]);
        }
    }

    public function defaultKeyProvider()
    {
        return array(
            array('skeletons'),
            array('context', false),
            array('asserts'),
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationFailsIfWorkerInvalid()
    {
        $rawConfig = array(
            'activities' => array(
                'act_a' => array(
                    'question'    => 'What time is it?',
                    'worker'      => 'invalid',
                    'skeletons'   => array('foo.html.twig', 'baz.html.twig'),
                    'entry_point' => 'skeletons[0]',
                    'context'     => 'KnpU\\ActivityRunner\\Tests\\Context',
                    'asserts'     => 'KnpU\\ActivityRunner\\Tests\\Assert',
                )
            )
        );

        $this->process($rawConfig);
    }

    /**
     * Processes configurations. Not mocking out this part as the test on
     * configurations would lose the point.
     *
     * @param array $configs
     *
     * @return array
     */
    private function process(array $configs)
    {
        $processor = new Processor();
        $configuration = new ActivityConfiguration();

        return $processor->processConfiguration(
            $configuration,
            $configs
        );
    }
}
