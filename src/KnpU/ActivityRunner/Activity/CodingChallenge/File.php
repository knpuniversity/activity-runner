<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

class File
{
    const TYPE_PHP = 'php';
    const TYPE_TWIG = 'twig';
    const TYPE_JSON = 'json';
    const TYPE_GHERKIN = 'feature';

    private $filename;

    private $contents;

    private $fileType;

    private $readonly;

    public function __construct($filename, $contents, $fileType, $readonly = false)
    {
        $this->filename = $filename;
        $this->contents = $contents;
        $this->fileType = $fileType;
        $this->readonly = $readonly;
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

    public function isReadonly()
    {
        return $this->readonly;
    }

    public static function determineFileType($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'php':
                return File::TYPE_PHP;
            case 'twig':
                return File::TYPE_TWIG;
            case 'json':
                return File::TYPE_JSON;
            case 'gherkin':
                return File::TYPE_GHERKIN;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported type for file "%s"', $filename));
        }
    }
}
