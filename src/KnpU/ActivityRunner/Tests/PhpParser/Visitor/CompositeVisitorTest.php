<?php

namespace KnpU\ActivityRunner\Tests\PhpParser\Visitor;

use KnpU\ActivityRunner\PhpParser\Visitor\CompositeVisitor;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class CompositeVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeTraverseCallsParentVisitorsInOrder()
    {
        $visitor = $this->setupVisitor('beforeTraverse', true);
        $nodes   = $visitor->beforeTraverse(array($this->getMockNode('')));

        $this->assertEquals('01', $nodes[0]->name);
    }

    public function testEnterNodeCallsParentVisitorsInOrder()
    {
        $visitor = $this->setUpVisitor('enterNode', false);
        $node    = $visitor->enterNode($this->getMockNode(''));

        $this->assertEquals('01', $node->name);
    }

    public function testLeaveNodeCallsParentVisitorsInOrder()
    {
        $visitor = $this->setUpVisitor('leaveNode', false);
        $node    = $visitor->leaveNode($this->getMockNode(''));

        $this->assertEquals('10', $node->name);
    }

    public function testAfterTraverseCallsParentVisitorsInOrder()
    {
        $visitor = $this->setupVisitor('afterTraverse', true);
        $nodes   = $visitor->afterTraverse(array($this->getMockNode('')));

        $this->assertEquals('10', $nodes[0]->name);
    }

    /**
     * @param string $methodName
     * @param boolean $multipleNodes
     *
     * @return array
     */
    private function setupVisitor($methodName, $multipleNodes)
    {
        $cbA = $this->createCallback(0);
        $cbB = $this->createCallback(1);

        if ($multipleNodes) {
            $cbA = function (array $nodes) use ($cbA) {
                $nodes[0] = $cbA($nodes[0]);

                return $nodes;
            };

            $cbB = function (array $nodes) use ($cbB) {
                $nodes[0] = $cbB($nodes[0]);

                return $nodes;
            };
        }

        $visitor = new CompositeVisitor();
        $visitor->addVisitor($this->getMockVisitor($methodName, $cbA));
        $visitor->addVisitor($this->getMockVisitor($methodName, $cbB));

        return $visitor;
    }

    /**
     * @param string|null $methodName
     * @param Callable|null $callback
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockVisitor($methodName = null, $callback = null)
    {
        $visitor = $this->getMock('PHPParser_NodeVisitor');

        if ($methodName) {
            $visitor
                ->expects($this->once())
                ->method($methodName)
                ->will($this->returnCallback($callback))
            ;
        }

        return $visitor;
    }

    /**
     * @param string|null $name
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockNode($name = null)
    {
        $node = $this->getMock('\PHPParser_Node');

        if (!is_null($name)) {
            $node->name = $name;
        }

        return $node;
    }

    /**
     * @param string $index
     *
     * @return callback
     */
    private function createCallback($index)
    {
        $cb = function (\PHPParser_Node $node) use ($index) {
            $node->name .= (string) $index;

            return $node;
        };

        return $cb;
    }
}
