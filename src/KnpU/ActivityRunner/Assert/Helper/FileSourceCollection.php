<?php

namespace KnpU\ActivityRunner\Assert\Helper;

class FileSourceCollection
{
    private $files = array();

    public function addFile($filename, FileSource $fileSource)
    {
        $this->files[$filename] = $fileSource;
    }

    /**
     * @param $filename
     * @return FileSource
     */
    public function getFile($filename)
    {
        if (!isset($this->files[$filename])) {
            throw new \InvalidArgumentException(sprintf('Bad file "%s"', $filename));
        }

        return $this->files[$filename];
    }
}
