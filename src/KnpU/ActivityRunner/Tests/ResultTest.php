<?php

namespace KnpU\ActivityRunner\Tests;

use KnpU\ActivityRunner\Result;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider normalOrLessVerbosityProvider
     *
     * @param integer $verbosity
     */
    public function testSetValidationErrorsRemovesPhpUnitText($verbosity)
    {
        $result = new Result('output');
        $result->setVerbosity($verbosity);

        $error = <<<EOD
expected
Failed asserting that Foo is Bar
EOD;
        $result->setValidationErrors(array($error));

        $actualErrors = $result->toArray();

        $this->assertEquals('expected', $actualErrors['errors']['validation'][0]);
    }

    public function normalOrLessVerbosityProvider()
    {
        return array(
            array(OutputInterface::VERBOSITY_NORMAL),
            array(OutputInterface::VERBOSITY_QUIET),
        );
    }

    /**
     * @dataProvider aboveNormalVerbosityProvider
     *
     * @param string $verbosity
     */
    public function testSetValidationErrorsNotRemovesPhpUnitText($verbosity)
    {
        $result = new Result('output');
        $result->setVerbosity($verbosity);

        $error = <<<EOD
expected
Failed asserting that Foo is Bar
EOD;
        $result->setValidationErrors(array($error));

        $actualErrors = $result->toArray();

        $this->assertEquals($error, $actualErrors['errors']['validation'][0]);
    }

    public function aboveNormalVerbosityProvider()
    {
        return array(
            array(OutputInterface::VERBOSITY_VERBOSE),
            array(OutputInterface::VERBOSITY_VERY_VERBOSE),
            array(OutputInterface::VERBOSITY_DEBUG),
        );
    }
}
