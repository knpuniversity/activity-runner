<?php

namespace KnpU\ActivityRunner\Grading;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\Activity\Exception\GradingException;

class GherkinGradingTool
{
    private $result;

    public function __construct(CodingExecutionResult $result)
    {
        $this->result = $result;

        $keywords = new ArrayKeywords(array(
            'en' => array(
                'feature'          => 'Feature',
                'background'       => 'Background',
                'scenario'         => 'Scenario',
                'scenario_outline' => 'Scenario Outline|Scenario Template',
                'examples'         => 'Examples|Scenarios',
                'given'            => 'Given',
                'when'             => 'When',
                'then'             => 'Then',
                'and'              => 'And',
                'but'              => 'But'
            ),
        ));
        $lexer = new Lexer($keywords);
        $this->parser = new Parser($lexer);
    }

    /**
     * @param $filename
     * @return FeatureNode
     * @throws GradingException
     * @throws \Behat\Gherkin\Exception\ParserException
     */
    public function getFeature($filename)
    {
        $ret = $this->parser->parse($this->result->getInputFileContents($filename));

        if (!$ret instanceof FeatureNode) {
            throw new GradingException(sprintf(
                'It does not look like %s has a Feature inside of it. Does it start with Feature: ?',
                $filename
            ));
        }

        return $ret;
    }
}
