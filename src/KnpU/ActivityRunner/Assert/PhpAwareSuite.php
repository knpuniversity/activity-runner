<?php

namespace KnpU\ActivityRunner\Assert;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
abstract class PhpAwareSuite extends AssertSuite implements PhpAwareInterface
{
    /**
     * @var \PHPParser_Parser
     */
    protected $parser;

    /**
     * {@inheritDoc}
     */
    public function setParser(\PHPParser_Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Parses an input file given its name.
     *
     * @param string|null $fileName
     *
     * @return \PHPParser_Node[]
     *
     * @throws \LogicException if the parser is not set
     * @throws \LogicException if no such input file exists
     */
    protected function parsePhp($fileName = null)
    {
        return $this->parser->parse($this->getInput($fileName));
    }

    /**
     * Creates a new node traverser.
     *
     * @return \PHPParser_NodeTraverser
     */
    protected function createPhpTraverser()
    {
        return new \PHPParser_NodeTraverser();
    }
}
