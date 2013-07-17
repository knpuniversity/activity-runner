<?php

namespace KnpU\ActivityRunner\PhpParser\Visitor;

/**
 * The composite visitor can be used to use multiple parentVisitors together. While
 * it's possible to simply add several parentVisitors to a node traverser, you
 * sometimes want to explicitly define the dependencies on other parentVisitors.
 *
 * This comes in handy for example when requiring that most names be resolved
 * to fully qualified class names.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class CompositeVisitor implements \PHPParser_NodeVisitor
{
    /**
     * @var \PHPParser_NodeVisitor[]
     */
    private $parentVisitors = array();

    /**
     * Adds a visitor to the composition.
     *
     * @param \PHPParser_NodeVisitor $visitor
     */
    public function addVisitor(\PHPParser_NodeVisitor $visitor)
    {
        $this->parentVisitors[] = $visitor;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeTraverse(array $nodes)
    {
        foreach ($this->parentVisitors as $visitor) {
            $nodes = $visitor->beforeTraverse($nodes);
        }

        return $nodes;
    }

    /**
     * {@inheritDoc}
     */
    public function enterNode(\PHPParser_Node $node)
    {
        foreach ($this->parentVisitors as $visitor) {
            $node = $visitor->enterNode($node);
        }

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(\PHPParser_Node $node)
    {
        foreach (array_reverse($this->parentVisitors) as $visitor) {
            $node = $visitor->leaveNode($node);
        }

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function afterTraverse(array $nodes)
    {
        foreach (array_reverse($this->parentVisitors) as $visitor) {
            $nodes = $visitor->afterTraverse($nodes);
        }

        return $nodes;
    }
}
