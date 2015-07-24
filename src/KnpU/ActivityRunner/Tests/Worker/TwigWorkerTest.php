<?php

namespace KnpU\ActivityRunner\Tests;

use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Worker\TwigWorker;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class TwigWorkerTest extends \PHPUnit_Framework_TestCase
{
    private static $container;

    public static function setUpBeforeClass()
    {
        self::$container = require __DIR__.'/../../../../../app/bootstrap.php';
    }

    public function testWorkerRendersTemplates()
    {
        $templates = array(
            'hello.html.twig' => 'Hello, {{ hello_name }}!',
            'base.html.twig' => "{% include 'hello.html.twig' with {hello_name: name} only %}",
        );

        $activity = $this->createActivity($templates, 'base.html.twig', "return array('name' => 'world');");

        $worker = $this->getTwigWorker();
        $result = $worker->execute($activity);

        $this->assertEquals('Hello, world!', $result->getOutput());
    }

    /**
     * Even though the success of this test depends on `TwigErrorHandler`
     * being correct, we can somewhat test the behaviour of what happens, if
     * a PHP error occurs inside the worker during the rendering phase.
     */
    public function testWorkerSetsLanguageErrorIfUnknownError()
    {
        $templates = array(
            'test.html.twig' => 'Hello, {{ postedAt }}',
        );

        $activity = $this->createActivity($templates, 'test.html.twig', "return array('postedAt' => new \\DateTime())");

        $worker = $this->getTwigWorker();
        $result = $worker->execute($activity);

        $this->assertEquals(
            'An exception has been thrown during the rendering of a template ("You have to use the `date` filter.") in "test.html.twig" at line 1.',
            $result->getLanguageError()
        );
    }

    public function testSupportsReturnsTrueIfNotTwig()
    {
        $worker = $this->getTwigWorker();

        $this->assertTrue($worker->supports('foo.twig', array()));
    }

    /**
     * @param string $fileName
     *
     * @dataProvider getNotTwigFileNames
     */
    public function testSupportsReturnsFalseIfNotTwig($fileName)
    {
        $worker = $this->getTwigWorker();

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

    /**
     * @param array $inputFiles
     * @param string $entryPoint
     * @param array $contextSource
     *
     * @return \KnpU\ActivityRunner\Activity
     */
    private function createActivity($inputFiles, $entryPoint, $contextSource)
    {
        $activity = new Activity('twig',  $entryPoint);
        $activity->setContextSource($contextSource);

        foreach ($inputFiles as $filename => $inputSource) {
            $activity->addInputFile($filename, $inputSource);
        }

        return $activity;
    }

    /**
     * @return TwigWorker
     */
    private function getTwigWorker()
    {
        return self::$container['worker.twig'];
    }
}
