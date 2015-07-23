<?php

namespace KnpU\ActivityRunner\Assert\Helper;

class FileSource
{
    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Is this string - case sensitive - in the content?
     *
     * @param string $string
     * @return bool
     */
    public function assertContains($string)
    {
        return stripos($this->content, $string) !== false;
    }
}
