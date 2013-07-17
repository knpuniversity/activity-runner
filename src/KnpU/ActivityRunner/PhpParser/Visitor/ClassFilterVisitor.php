<?php

namespace KnpU\ActivityRunner\PhpParser\Visitor;

/**
 * The class Filter visitor returns all class decarations found in the code. If
 * the optional `$className` argument is passed, the visitor looks for the class
 * declaration that matches this specific name.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ClassFilterVisitor extends \PHPParser_NodeVisitorAbstract
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @param string|null $className
     */
    public function __construct($className = null)
    {
        $this->className = $className;
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(\PHPParser_Node $node)
    {
        if ($node instanceof \PHPParser_Node_Stmt_Class) {

            // Keep classes.
            return null;

        } elseif (is_array($node->stmts)) {
            // Iterates over children and finds all classes, then returns them
            // all. This way the classes bubble up to the top of the tree.
            $classes = array();

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof \PHPParser_Node_Stmt_Class) {
                    $classes[] = $stmt;
                }
            }

            if (count($classes) > 0) {
                return $classes;
            }
        }

        // Remove the node as there were no immediate child nodes as classes.
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function afterTraverse(array $nodes)
    {
        if (!$this->className) {
            return;
        }

        foreach ($nodes as $node) {
            if ($node->name === $this->className) {
                return array($node);
            }
        }

        return array();
    }
}
