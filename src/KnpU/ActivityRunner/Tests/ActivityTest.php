<?php

namespace KnpU\ActivityRunner\Test;

use KnpU\ActivityRunner\Activity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityTest extends \PHPUnit_Framework_TestCase
{
    private static $tmpFiles = array();

    public static function tearDownAfterClass()
    {
        foreach (self::$tmpFiles as $tmpFile) {
            unlink($tmpFile);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetSkeletonsFailsIfNoPathsSpecified()
    {
        $activity = new Activity($this->getMockClassLoader());
        $activity->setSkeletons(array());
    }

    public function testGetContextReturnsFileReturnValue()
    {
        $contextPath = __DIR__.'/Fixtures/context.php';

        $activity = new Activity($this->getMockClassLoader());
        $activity->setContext($contextPath);

        $expected = require $contextPath;

        $this->assertEquals($expected, $activity->getContext());
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetContextCalledBeforeSetFails()
    {
        $activity = new Activity($this->getMockClassLoader());
        $activity->getContext();
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\UnexpectedTypeException
     */
    public function testGetContextFailsIfFileNotReturnsArray()
    {
        $contextPath = __DIR__.'/Fixtures/context_invalid.php';

        $activity = new Activity($this->getMockClassLoader());
        $activity->setContext($contextPath);

        $activity->getContext();
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetSuiteFailsIfNoSuiteClassSet()
    {
        $activity = new Activity($this->getMockClassLoader());
        $activity->getSuite();
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\UnexpectedTypeException
     */
    public function testGetSuiteFailsIfSuiteSourceIncorrect()
    {
        $source = 'stdClass';

        $classLoader = $this->getMockClassLoader();
        $classLoader
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValue($source))
        ;

        $activity = new Activity($classLoader);
        $activity->setSuiteSource($source);
        $activity->getSuite();
    }

    public function testGetSuiteInstantiatesNewObject()
    {
        $source = 'KnpU\\ActivityRunner\\Tests\\Fixtures\\TestAssertSuite';

        $classLoader = $this->getMockClassLoader();
        $classLoader
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValue($source))
        ;

        $activity = new Activity($classLoader);
        $activity->setSuiteSource($source);

        $this->assertInstanceOf($source, $activity->getSuite());
    }

    public function testGetSuiteAlwaysReturnsSameObject()
    {
        $source = 'KnpU\\ActivityRunner\\Tests\\Fixtures\\TestAssertSuite';

        $classLoader = $this->getMockClassLoader();
        $classLoader
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValue($source))
        ;

        $activity = new Activity($classLoader);
        $activity->setSuiteSource($source);

        $this->assertSame($activity->getSuite(), $activity->getSuite());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetInputFilesFailsIfExtraFiles()
    {
        $activity = new Activity($this->getMockClassLoader());

        $activity->setSkeletons(array(
            'foo.html.twig' => '/foo/file.html.twig'
        ));

        $activity->setInputFiles(new ArrayCollection(array(
            'foo.html.twig'  => '<h1>{{ blah }}</h1>',
            'evil.html.twig' => 'muhaha, execute my secret file!',
        )));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetInputFilesFailsIfFilesMissing()
    {
        $activity = new Activity($this->getMockClassLoader());

        $activity->setSkeletons(array(
            'foo.html.twig' => 'foo.html.twig',
            'baz.php'       => 'bazfoo.php'
        ));

        $activity->setInputFiles(new ArrayCollection(array(
            'baz.php' => '<?php echo $foo;',
        )));
    }

    /**
     * Creates a temporary file.
     *
     * @return string
     */
    private static function createTmpFile()
    {
        return self::$tmpFiles[] = tempnam('/tmp', 'KNPUT');
    }

    /**
     * @param string|null $className
     *
     * @return \KnpU\ActivityRunner\Assert\ClassLoader|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockClassLoader($className = null)
    {
        $classLoader = $this->getMock('KnpU\\ActivityRunner\\Assert\\ClassLoader');

        if ($className) {
            $classLoader
                ->expects($this->any())
                ->method('load')
                ->will($this->returnValue($className))
            ;
        }

        return $classLoader;
    }
}
