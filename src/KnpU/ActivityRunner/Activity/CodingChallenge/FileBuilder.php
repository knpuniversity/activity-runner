<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

class FileBuilder
{
    private $entryPointFilename;

    private $files = array();

    /**
     * What's the filename that should be executed first
     *
     * How this is used differs on the worker. For twig, obviously a PHP script
     * is executed, but this return value will be the twig template that's rendered.
     *
     * @param $entryPointFilename
     */
    public function setEntryPointFilename($entryPointFilename)
    {
        $this->entryPointFilename = $entryPointFilename;
    }

    /**
     * @param string $filename  The "local" filename - e.g. index.php
     * @param string $path      The full filesystem path to the file - /var/www/files/index.php
     */
    public function addFile($filename, $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found', $path));
        }

        $this->addFileContents($filename, file_get_contents($path));
    }

    /**
     * Add a file by passing its contents directly
     *
     * @param string $filename
     * @param string $contents
     */
    public function addFileContents($filename, $contents)
    {
        $type = self::determineFileType($filename);
        $file = new File($filename, $contents, $type);

        $this->files[] = $file;
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    private static function determineFileType($filename)
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

    public function getEntryPointFilename()
    {
        if ($this->entryPointFilename) {
           return $this->entryPointFilename;
        }

        if (count($this->files) == 1) {
            return $this->files[0]->getFilename();
        }

        throw new \LogicException('No entry point filename given!');
    }
}
