<?php

namespace KnpU\ActivityRunner\Tests\Worker;

use KnpU\ActivityRunner\Worker\ChainedWorker;
use Doctrine\Common\Collections\Collection;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ChainedWorkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testConstructorFailsIfNoWorkersProvided()
    {
        $worker = new ChainedWorker(array());
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\UnexpectedTypeException
     */
    public function testConstructorFailsIfAllNotWorkers()
    {
        $worker = new ChainedWorker(array(new \stdClass()));
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\FileNotSupportedException
     */
    public function testRenderFailsIfNoWorkersSupport()
    {
        $mockWorker = $this->getMockWorker();

        $mockWorker
            ->expects($this->once())
            ->method('supports')
            ->will($this->returnValue(false))
        ;

        $activity = $this->getMockActivity($this->getMockCollection(), 'unsupported', array());

        $worker = new ChainedWorker(array($mockWorker));
        $worker->render($activity);
    }

    public function testRenderDelegatesToChildWorker()
    {
        $mockWorker = $this->getMockWorker();

        $mockWorker
            ->expects($this->once())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $mockWorker
            ->expects($this->once())
            ->method('render')
        ;

        $activity = $this->getMockActivity($this->getMockCollection(), 'supproted', array());

        $worker = new ChainedWorker(array($mockWorker));
        $worker->render($activity);
    }

    public function testInjectInteralsDelegatesToChildWorker()
    {
        $mockWorker = $this->getMockWorker();

        $mockWorker
            ->expects($this->once())
            ->method('injectInternals')
        ;

        $worker = new ChainedWorker(array($mockWorker));
        $worker->injectInternals($this->getMockSuite());
    }

    /**
     * @return \KnpU\ActivityRunner\Worker\WorkerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockWorker()
    {
        return $this->getMock('KnpU\\ActivityRunner\\Worker\\WorkerInterface');
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockCollection()
    {
        return $this->getMock('Doctrine\\Common\\Collections\\Collection');
    }

    /**
     * @param Collection $inputFiles
     * @param string $entryPoint
     * @param array $context
     *
     * @return \KnpU\ActivityRunner\ActivityInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockActivity(Collection $inputFiles, $entryPoint, array $context)
    {
        $activity = $this->getMock('KnpU\\ActivityRunner\\ActivityInterface');

        $activity
            ->expects($this->any())
            ->method('getInputFiles')
            ->will($this->returnValue($inputFiles))
        ;

        $activity
            ->expects($this->any())
            ->method('getEntryPoint')
            ->will($this->returnValue($entryPoint))
        ;

        $activity
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context))
        ;

        return $activity;
    }

    /**
     * @return \KnpU\ActivityRunner\Assert\AssertSuite|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSuite()
    {
        return $this
            ->getMockBuilder('KnpU\\ActivityRunner\\Assert\\AssertSuite')
            ->getMockForAbstractClass()
        ;
    }
}
