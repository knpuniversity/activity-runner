<?php

namespace KnpU\ActivityRunner\Tests\Assert;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Assert\Asserter;
use KnpU\ActivityRunner\Tests\Fixtures\FailingAssertSuite;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class AsserterTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateReturnsErrors()
    {
        // Mocking the suite would be very difficult since reflection
        // is used internally to invoke class methods and set private
        // properties.
        $activity = $this->getMockActivity();

        $activity
            ->expects($this->any())
            ->method('getSuite')
            ->will($this->returnValue(new FailingAssertSuite()))
        ;

        $runIf = $this->getMockRunIf();
        $runIf
            ->expects($this->any())
            ->method('isAllowedToRun')
            ->will($this->returnValue(true))
        ;

        $asserter = new Asserter($this->getMockReader(), $runIf);

        $errors = $asserter->validate($this->getMockResult(), $activity);

        $this->assertEquals(array('FooBarBaz'), $errors);
    }

    private function getMockActivity()
    {
        $activity = $this->getMock('KnpU\\ActivityRunner\\ActivityInterface');

        return $activity;
    }

    /**
     * @return \KnpU\ActivityRunner\Result|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockResult()
    {
        return $this
            ->getMockBuilder('KnpU\\ActivityRunner\\Result')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \Doctrine\Common\Annotations\Reader|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockReader()
    {
        return $this->getMock('Doctrine\\Common\\Annotations\\Reader');
    }

    /**
     * @return \Knpu\ActivityRunner\Assert\Suite\RunIf|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockRunIf()
    {
        return $this
            ->getMockBuilder('KnpU\\ActivityRunner\\Assert\\Suite\\RunIf')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
