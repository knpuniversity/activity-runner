<?php

namespace KnpU\ActivityRunner\Test\Assert\Suite;

use KnpU\ActivityRunner\Assert\Suite\RunIf;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class RunIfTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testConstructFailsIfNoValueKey()
    {
        $runIf = new RunIf(array());
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\UnexpectedTypeException
     */
    public function testConstructFailsIfIncorrectStateType()
    {
        $runIf = new RunIf(array(
            'value' => new \stdClass(),
        ));
    }

    public function testNotAllowedToRunIfAllDeny()
    {
        $runIf = new RunIf(array(
            'value' => $this->getMockState(false),
        ));

        $this->assertFalse($runIf->isAllowedToRun($this->getMockResult()));
    }

    public function testAllowedToRunIfAtLeastOneAllows()
    {
        $runIf = new RunIf(array(
            'value' => array(
                $this->getMockState(false),
                $this->getMockState(true),
            )
        ));

        $this->assertTrue($runIf->isAllowedToRun($this->getMockResult()));
    }

    /**
     * @param boolean|null $allowedToRun
     *
     * @return \KnpUActivityRunner\Assert\Suite\StateInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockState($allowedToRun = null)
    {
        $state = $this->getMock('KnpU\\ActivityRunner\\Assert\\Suite\\StateInterface');

        if (!is_null($allowedToRun)) {
            $state
                ->expects($this->once())
                ->method('isAllowedToRun')
                ->will($this->returnValue($allowedToRun))
            ;
        }

        return $state;
    }

    /**
     * @return \KnpU\ActivityRunner\Result|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockResult()
    {
        return $this->getMock('KnpU\\ActivityRunner\\Result');
    }
}
