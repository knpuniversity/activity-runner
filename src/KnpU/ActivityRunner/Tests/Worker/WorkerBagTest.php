<?php

namespace KnpU\ActivityRunner\Tests\Worker;

use KnpU\ActivityRunner\Worker\WorkerBag;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class WorkerBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testAddWorkerFailsIfNameIsSanme()
    {
        $worker = $this->getMockWorker('foo');

        $bag = new WorkerBag();
        $bag->addWorker($worker);
        $bag->addWorker($worker);
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetWorkerFailsIfNoWorkerNamedAsSuch()
    {
        $bag = new WorkerBag();
        $bag->get('foo');
    }

    public function testGetWorkerReturnsWorker()
    {
        $worker = $this->getMockWorker('foo');

        $bag = new WorkerBag();
        $bag->addWorker($worker);

        $this->assertSame($worker, $bag->get('foo'));
    }

    public function testAddMultipleWorkers()
    {
        $workerA = $this->getMockWorker('foo');
        $workerB = $this->getMockWorker('baz');

        $bag = new WorkerBag();
        $bag->addWorkers(array($workerA, $workerB));

        $this->assertNotNull($bag->get('foo'));
        $this->assertNotNull($bag->get('baz'));
    }

    /**
     * @return \KnpU\ActivityRunner\Worker\WorkerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockWorker($name = null)
    {
        $worker = $this->getMock('KnpU\\ActivityRunner\\Worker\\WorkerInterface');

        if ($name) {
            $worker
                ->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($name))
            ;
        }

        return $worker;
    }
}
