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
     * @var array A cache of the parsed PHP statements
     */
    protected $parsedPhpStatements = array();

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
     * @param string $contents
     *
     * @return \PHPParser_Node[]
     *
     * @throws \LogicException if the parser is not set
     * @throws \LogicException if no such input file exists
     */
    protected function getPhpNodeTree($contents)
    {
        $key = sha1($contents);
        if (!isset($this->parsedPhpStatements[$contents])) {
            $this->parsedPhpStatements[$key] = $this->parser->parse($contents);
        }

        return $this->parsedPhpStatements[$contents];
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
