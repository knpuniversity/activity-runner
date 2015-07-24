<?php

namespace KnpU\ActivityRunner\Tests\Worker;

use KnpU\ActivityRunner\Activity;
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

        $files = array(
            'base.php' => $base,
            'included.php' => $included,
        );

        $activity = $this->getMockActivity($files, 'base.php');

        $worker = $this->createWorker();

        $result = $worker->execute($activity);
        //var_dump($result);

        $this->assertContains('Hello, world!', $result->getOutput());
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

        return $worker;
    }

    /**
     * @param array $inputFiles
     * @param string $entryPoint
     *
     * @return \KnpU\ActivityRunner\Activity
     */
    private function getMockActivity(array $inputFiles, $entryPoint)
    {
        $activity = new Activity('php', $entryPoint);

        foreach ($inputFiles as $filename => $inputSource) {
            $activity->addInputFile($filename, $inputSource);
        }

        return $activity;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
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
