<?php

namespace KnpU\ActivityRunner\Tests\Worker;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Worker\PhpWorker;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class PhpWorkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The prefix used for environments to the /tmp directory.
     *
     * @var string
     */
    protected $prefix = 'activity_runner_test_';

    public function testWorkerExecutesPhpFiles()
    {
        $base = <<<EOD
<?php

include_once __DIR__.'/included.php';
EOD;

        $included = <<<EOD
<?php

echo 'Hello, world!';
EOD;

        $files = new ArrayCollection(array(
            'base.php' => $base,
            'included.php' => $included,
        ));

        $activity = $this->getMockActivity($files, 'base.php');

        $worker = $this->createWorker();

        $result = $worker->render($activity);

        $this->assertContains('Hello, world!', $result->getOutput());
    }

    public function testLanguageErrorIfTimeOutReached()
    {
        $code  = '<?php while (true);';
        $files = new ArrayCollection(array('index.php' => $code));

        $activity = $this->getMockActivity($files, 'index.php');

        $worker = $this->createWorker();
        $worker->setTimeout(0.2);

        $result = $worker->render($activity)->toArray();

        $this->assertContains('too long time', $result['errors']['language']);
    }

    public function testChildProcessTerminatedIfTimeoutReached()
    {
        $code  = '<?php while (true);';
        $files = new ArrayCollection(array('index.php' => $code));

        $activity = $this->getMockActivity($files, 'index.php');

        $mockFs = $this->getMockFilesystem();

        $mockFs
            ->expects($this->once())
            ->method('dumpFile')
            ->will($this->returnCallback(function ($filename, $content) {
                $filesystem = new Filesystem();
                $filesystem->dumpFile($filename, $content);
            }))
        ;

        // The critical expectation for this test to pass.
        $mockFs
            ->expects($this->once())
            ->method('remove')
        ;

        $worker = $this->createWorker($mockFs);
        $worker->setTimeout(0.2);
        $worker->render($activity);
    }

    public function testNoChildProcessesLeftDangling()
    {
        $code  = '<?php while (true);';
        $files = new ArrayCollection(array('index.php' => $code));

        $activity = $this->getMockActivity($files, 'index.php');

        // Search for running processes that have a "activity_runner_test" in it.
        exec('ps aux | grep [a]ctivity_runner_test', $output);
        $runningScriptCount = count($output);

        $worker = $this->createWorker();
        $worker->setTimeout(0.2);
        $worker->render($activity);

        // The worker should have killed the newly created process. Let's look
        // at running processes again - we should have exactly the same number
        // of them as prior to starting the worker.
        exec('ps aux | grep [a]ctivity_runner_test', $output);

        $this->assertCount($runningScriptCount, $output);
    }

    /**
     * @group ini
     */
    public function testChangingIniSettingsHasNoEffectOnTimeout()
    {
        $code = <<<EOD
<?php

ini_set('max_execution_time', 2);
while (true);
EOD;

        $files = new ArrayCollection(array('index.php' => $code));

        $activity = $this->getMockActivity($files, 'index.php');


        $worker = $this->createWorker();
        $worker->setTimeout(0.2);

        $timeStart = microtime(true);
        $worker->render($activity);
        $timeStop = microtime(true);

        $this->assertEquals(0.2, $timeStop - $timeStart, '', 0.1);
    }

    public function testSupportsReturnsTrueIfPhp()
    {
        $worker = $this->createWorker();

        $this->assertTrue($worker->supports('foo.php', array()));
    }

    /**
     * @param string $fileName
     *
     * @dataProvider getNotPhpFileNames
     */
    public function testSupportsReturnsFalseIfNotPhp($fileName)
    {
        $worker = $this->createWorker();

        $this->assertFalse($worker->supports($fileName, array()));
    }

    public function getNotPhpFileNames()
    {
        return array(
            array('php.foo'),
            array('foo.php.twig'),
            array('baz.xml'),
            array('php'),
        );
    }

    protected function tearDown()
    {
        $filesystem  = new Filesystem();
        $directories = glob(sys_get_temp_dir().'/'.$this->prefix.'*');

        foreach ($directories as $directory) {
            $filesystem->remove($directory);
        }
    }

    /**
     * Creates a new worker. Used for keeping the tests compact.
     *
     * @param Filesystem|null $filesystem
     *
     * @return PhpWorker
     */
    private function createWorker(Filesystem $filesystem = null)
    {
        $worker = new PhpWorker($filesystem ?: new Filesystem(), $this->getMockParser());
        $worker->setPrefix($this->prefix);

        return $worker;
    }

    /**
     * @param ArrayCollection $inputFiles
     * @param string $entryPoint
     *
     * @return \KnpU\ActivityRunner\ActivityInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockActivity(ArrayCollection $inputFiles, $entryPoint)
    {
        $activity = $this->getMock('KnpU\\ActivityRunner\\ActivityInterface');

        $activity
            ->expects($this->any())
            ->method('getInputFiles')
            ->will($this->returnValue($inputFiles))
        ;

        $activity
            ->expects($this->any())
            ->method('getEntryPoint')
            ->will($this->returnValue($entryPoint))
        ;

        return $activity;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFilesystem()
    {
        return $this->getMock('Symfony\\Component\\Filesystem\\Filesystem');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockParser()
    {
        return $this
            ->getMockBuilder('PHPParser_Parser')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
