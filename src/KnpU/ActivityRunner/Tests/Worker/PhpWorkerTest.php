<?php

namespace KnpU\ActivityRunner\Tests\Worker;

use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\Worker\PhpWorker;

class PhpWorkerTest extends \PHPUnit_Framework_TestCase
{
    private $testDir;

    public function setup()
    {
        $this->testDir = realpath(sys_get_temp_dir()).'/php_test';
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir);
        }
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        system('rm -r '.realpath($this->testDir));
    }

    /**
     * @dataProvider getExecutionTests
     */
    public function testExecuteCode($filename, $fileSource, array $variables, $expectedOutput, $expectedLanguageError)
    {
        $path = $this->testDir.'/'.$filename;
        file_put_contents($path, $fileSource);

        $context = new CodingContext($this->testDir);
        foreach ($variables as $name => $val) {
            $context->addVariable($name, $val);
        }

        $result = new CodingExecutionResult(array($filename => $fileSource));

        $worker = new PhpWorker();
        $worker->executeCode($this->testDir, $filename, $context, $result);

        $this->assertEquals($expectedOutput, $result->getOutput());
        $this->assertEquals($expectedLanguageError, $result->getLanguageError());
    }

    public function getExecutionTests()
    {
        $tests = array();

        $tests[] = array(
            'test_file.php',
            '<?php echo $undefinedVar; ?>',
            array(),
            '',
            'Notice: Undefined variable: undefinedVar in test_file.php on line 1'
        );

        return $tests;
    }
}