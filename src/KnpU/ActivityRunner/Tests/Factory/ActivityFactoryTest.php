<?php

namespace KnpU\ActivityRunner\Tests\Factory;

use KnpU\ActivityRunner\Factory\ActivityFactory;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException KnpU\ActivityRunner\Exception\NoActivitiesDefinedException
     */
    public function testSetConfigFailsIfConfigIsEmpty()
    {
        $factory = new ActivityFactory($this->getMockClassLoader());
        $factory->setConfig(array());
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\ActivityNotFoundException
     */
    public function testCreateActivityFailsIfNameNotDefined()
    {
        $factory = new ActivityFactory($this->getMockClassLoader());
        $factory->setConfig(array('dummy' => array()));

        $factory->createActivity('missing', new ArrayCollection(array(
            'foo.html.twig' => 'user input',
        )));
    }

    public function testNewActivityCreation()
    {
        $factory = new ActivityFactory($this->getMockClassLoader());
        $factory->setConfig(array(
            'activity_a' => array(
                'question'    => 'What is the answer to life the universe and everything?',
                'worker'      => 'twig',
                'skeletons'   => array(__DIR__.'/../Fixtures/skeleton.html.twig'),
                'entry_point' => 0,
                'context'     => __DIR__.'/../Fixtures/context.php',
                'asserts'     => __DIR__.'/../Fixtures/FailingAssertSuite.php'
            )
        ));

        $this->assertInstanceOf('KnpU\\ActivityRunner\\Activity', $factory->createActivity('activity_a', new ArrayCollection(
            array('user input')
        )));
    }

    public function getMockClassLoader()
    {
        return $this->getMock('KnpU\\ActivityRunner\\Assert\\ClassLoader');
    }
}
