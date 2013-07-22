<?php

namespace KnpU\ActivityRunner\Tests\Configuration;

use KnpU\ActivityRunner\Configuration\PathExpander;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class PathExpanderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $path;

    /**
     * @dataProvider notStringNorArrayProvider
     * @expectedException \Knpu\ActivityRunner\Exception\UnexpectedTypeException
     */
    public function testExpandUnexpectedTypeFails($paths)
    {
        $expander = new PathExpander($this->getFinder());
        $expander->expand($paths);
    }

    public function notStringNorArrayProvider()
    {
        return array(
            array(false),
            array(null),
            array(new \stdClass()),
            array(2),
        );
    }

    public function testEmptyDirExpandedToNothing()
    {
        $expander = new PathExpander($this->getFinder());

        $this->assertEmpty($expander->expand($this->path));
    }

    public function testExpandFiltersByPattern()
    {
        $expander = new PathExpander($this->getFinder());

        $this->touch(array(
            'fileA.php',
            'fileB.php',
            'fileC.txt'
        ));

        $this->assertEquals(array(
            $this->path.'/fileA.php',
            $this->path.'/fileB.php',
        ), $expander->expand($this->path, 'file*.php'));
    }

    public function testExpandTwice()
    {
        $expander = new PathExpander($this->getFinder());

        $this->touch(array(
            'fileA.php',
            'fileC.txt',
        ));

        $this->assertEquals(array($this->path.'/fileA.php'), $expander->expand($this->path, 'file*.php'));
        $this->assertEquals(array($this->path.'/fileC.txt'), $expander->expand($this->path, 'file*.txt'));
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->mkdir($path = sys_get_temp_dir().'/activity_runner_'.mt_rand());

        $this->fs   = $fs;
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->path);

        $this->fs   = null;
        $this->path = null;
    }

    /**
     * @param string|array $fileName
     */
    private function touch($fileNames)
    {
        if (!is_array($fileNames)) {
            $fileNames = array($fileNames);
        }

        foreach ($fileNames as $key => $fileName) {
            $fileNames[$key] = $this->path.'/'.$fileName;
        }

        $this->fs->touch($fileNames);
    }

    /**
     * Using an actual implementation as mocking it would be too much effort
     * for such a small gain.
     *
     * @return \Symfony\Component\Finder\Finder
     */
    private function getFinder()
    {
        return new Finder();
    }
}
