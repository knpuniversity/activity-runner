<?php

namespace KnpU\ActivityRunner\Tests;

use KnpU\ActivityRunner\Result;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testSetValidationErrorsRemovesPhpUnitText()
    {
        $result = new Result('output');

        $error = <<<EOD
expected
Failed asserting that Foo is Bar
EOD;
        $result->setValidationErrors(array($error));

        $actualErrors = $result->toArray();

        $this->assertEquals('expected', $actualErrors['errors']['validation'][0]);
    }
}
