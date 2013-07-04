<?php

namespace KnpU\ActivityRunner\Tests\Assert;

use KnpU\ActivityRunner\Assert\ClassLoader;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadPsr0()
    {
        $expected = 'KnpU\\ActivityRunner\\Tests\\Fixtures\\TestAssertSuite';

        $loader = new ClassLoader();

        $actual = $loader->load($expected);

        $this->assertEquals($expected, $actual);
    }

    public function testLoadByFileName()
    {
        $expected = '\\KnpU\\ActivityRunner\\Tests\\Fixtures\\NotPsrClass';

        $loader = new ClassLoader();

        $actual = $loader->load(__DIR__.'/../Fixtures/notpsrclass.php');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\FileNotFoundException
     */
    public function testNonExistentFileThrowsException()
    {
        $loader = new ClassLoader();
        $loader->load('/path/to/nowhere');
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\ClassNotFoundException
     */
    public function testClassNotDefinedInFileThrowsException()
    {
        $loader = new ClassLoader();
        $loader->load(__DIR__.'/../Fixtures/context.php');
    }


}
