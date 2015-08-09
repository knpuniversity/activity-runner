<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

class File
{
    const TYPE_PHP = 'php';
    const TYPE_TWIG = 'twig';

    private $filename;

    private $contents;

    private $fileType;

    public function __construct($filename, $contents, $fileType)
    {
        $this->filename = $filename;
        $this->contents = $contents;
        $this->fileType = $fileType;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function getFileType()
    {
        return $this->fileType;
    }

    public static function determineFileType($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'php':
                return File::TYPE_PHP;
            case 'twig':
                return File::TYPE_TWIG;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported type for file "%s"', $filename));
        }
    }
}
