<?php

namespace KnpU\ActivityRunner\Tests\PhpParser\Visitor;

use KnpU\ActivityRunner\PhpParser\Visitor\ClassFilterVisitor;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ClassFilterVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testVisitorKeepsOnlyClasses()
    {
        $filePath = __DIR__.'/../../Fixtures/PhpParser/classes_namespaced.php';

        $traverser = new \PHPParser_NodeTraverser();
        $traverser->addVisitor(new ClassFilterVisitor());

        $nodes = $traverser->traverse($this->parse($filePath));

        $this->assertCount(2, $nodes);

        foreach ($nodes as $node) {
            $this->assertInstanceOf('PHPParser_Node_Stmt_Class', $node);
        }
    }

    public function testVisitorFiltersByClassName()
    {
        $filePath  = __DIR__.'/../../Fixtures/PhpParser/classes_namespaced.php';
        $className = 'Bah';

        $traverser = new \PHPParser_NodeTraverser();
        $traverser->addVisitor(new ClassFilterVisitor($className));

        $nodes = $traverser->traverse($this->parse($filePath));

        $this->assertCount(1, $nodes);
        $this->assertEquals($className, $nodes[0]->name);
    }

    /**
     * Shortcut for parsing files
     *
     * @param string $filePath
     *
     * @return \PHPParser_Node[]
     */
    private function parse($filePath)
    {
        $parser = new \PHPParser_Parser(new \PHPParser_Lexer());

        return $parser->parse(file_get_contents($filePath));
    }
}
