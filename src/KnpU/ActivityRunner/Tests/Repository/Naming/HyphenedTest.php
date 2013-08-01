<?php

namespace KnpU\ActivityRunner\Tests\Repository\Naming;

use KnpU\ActivityRunner\Repository\Naming\Hyphened;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class HyphenedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $base
     * @param string $url
     * @param string $ref
     * @param string $expected
     *
     * @dataProvider createProvider
     */
    public function testCreate($base, $url, $ref, $expected)
    {
        $strategy = new Hyphened($base);

        $this->assertEquals($expected, $strategy->create($url, $ref));
    }

    public function createProvider()
    {
        return array(
            array('', 'git@github.com:foo/baz.git', 'master', 'foo-baz-master'),
            array('', 'https://github.com/foo/baz', 'master', 'foo-baz-master'),
            array('', 'http://example.com/foo/baz/bar/', 'master', 'foo-baz-bar-master'),
            array('my/base', 'foo/baz.git', 'master', 'my/base/foo-baz-master')
        );
    }
}
