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

        $worker = new PhpWorker(new Filesystem());
        $result = $worker->render($activity);

        $this->assertContains('Hello, world!', $result->getOutput());
    }

    public function testSupportsReturnsTrueIfNotPhp()
    {
        $worker = new PhpWorker(new Filesystem());

        $this->assertTrue($worker->supports('foo.php', array()));
    }

    /**
     * @param string $fileName
     *
     * @dataProvider getNotPhpFileNames
     */
    public function testSupportsReturnsFalseIfNotPhp($fileName)
    {
        $worker = new PhpWorker(new Filesystem());

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
}
