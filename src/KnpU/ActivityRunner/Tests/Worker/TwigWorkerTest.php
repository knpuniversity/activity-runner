<?php

namespace KnpU\ActivityRunner\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Exception\InvalidActivityException;
use KnpU\ActivityRunner\Worker\TwigWorker;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class TwigWorkerTest extends \PHPUnit_Framework_TestCase
{
    public function testWorkerRendersTemplates()
    {
        $templates = new ArrayCollection(array(
            'hello.html.twig' => 'Hello, {{ hello_name }}!',
            'base.html.twig' => "{% include 'hello.html.twig' with {hello_name: name} only %}",
        ));

        $activity = $this->getMockActivity($templates, 'base.html.twig', array('name' => 'world'));

        $worker = new TwigWorker();
        $result = $worker->render($activity);

        $this->assertEquals('Hello, world!', $result->getOutput());
    }

    /**
     * Even though the success of this test depends on `TwigErrorHandler`
     * being correct, we can somewhat test the behaviour of what happens, if
     * a PHP error occurrs inside the worker during the rendering phase.
     */
    public function testWorkerSetsLanguageErrorIfUnknownError()
    {
        $templates = new ArrayCollection(array(
            'test.html.twig' => 'Hello, {{ array }}',
        ));

        $activity = $this->getMockActivity($templates, 'test.html.twig', array('array' => array()));

        $worker = new TwigWorker();
        $result = $worker->render($activity);

        $result = $result->toArray();
        $this->assertCount(1, $result['errors']['validation']);
    }

    public function testSupportsReturnsTrueIfNotTwig()
    {
        $worker = new TwigWorker();

        $this->assertTrue($worker->supports('foo.twig', array()));
    }

    /**
     * @param string $fileName
     *
     * @dataProvider getNotTwigFileNames
     */
    public function testSupportsReturnsFalseIfNotTwig($fileName)
    {
        $worker = new TwigWorker();

        $this->assertFalse($worker->supports($fileName, array()));
    }

    public function getNotTwigFileNames()
    {
        return array(
            array('twig.foo'),
            array('foo.twig.php'),
            array('baz.php'),
            array('twig'),
        );
    }

    public function testInjectsTwigIfTwigAwareSuite()
    {
        $suite = $this->getMockTwigAwareSuite();

        $suite
            ->expects($this->once())
            ->method('setTwig')
            ->with($this->isInstanceOf('Twig_Environment'))
        ;

        $worker = new TwigWorker();
        $worker->injectInternals($suite);
    }

    /**
     * @param ArrayCollection $inputFiles
     * @param string $entryPoint
     * @param array $context
     *
     * @return \KnpU\ActivityRunner\ActivityInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockActivity(ArrayCollection $inputFiles, $entryPoint, array $context)
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

        $activity
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context))
        ;

        return $activity;
    }

    /**
     * @return \KnpU\ActivityRunner\Assert\TwigAwareSuite|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockTwigAwareSuite()
    {
        return $this->getMock('KnpU\\ActivityRunner\\Assert\\TwigAwareSuite');
    }
}
