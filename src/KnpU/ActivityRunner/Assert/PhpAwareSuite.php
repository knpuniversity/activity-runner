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
     * @param string $fileName
     *
     * @return \PHPParser_Node[]
     *
     * @throws \LogicException if the parser is not set
     * @throws \LogicException if no such input file exists
     */
    protected function parsePhp($fileName)
    {
        $inputFiles = $this->getActivity()->getInputFiles();

        if (!$this->parser) {
            throw new \LogicException('The parser is not set.');
        }

        if (!$inputFiles->containsKey($fileName)) {
            throw new \LogicException(sprintf('No file named `%s` found as an input file, possible values are: `%s`', $fileName, implode('`, `', $inputFiles->getKeys())));
        }

        return $this->parser->parse($inputFiles->get($fileName));
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
