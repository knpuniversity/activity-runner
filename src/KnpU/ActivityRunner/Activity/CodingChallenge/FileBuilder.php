<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

class FileBuilder
{
    private $entryPointFilename;

    /**
     * @var File[]
     */
    private $files = array();

    /**
     * @var array with keys "path" and "readonly"
     */
    private $pendingFileDetails = array();

    /**
     * What's the filename that should be executed first
     *
     * How this is used differs on the worker. For twig, obviously a PHP script
     * is executed, but this return value will be the twig template that's rendered.
     *
     * @param $entryPointFilename
     *
     * @return $this
     */
    public function setEntryPointFilename($entryPointFilename)
    {
        $this->entryPointFilename = $entryPointFilename;

        return $this;
    }

    /**
     * @param string $filename  The "local" filename - e.g. index.php
     * @param string $path      The full filesystem path to the file - /var/www/files/index.php
     * @param bool   $readonly  Should this file be readonly?
     *
     * @return $this
     */
    public function addFile($filename, $path, $readonly = false)
    {
        // add this as a file, but don't set its contents automatically
        $this->files[$filename] = null;
        // record where the file *should* be on the filesystem, in case we want to read it
        $this->pendingFileDetails[$filename] = array(
            'path' => $path,
            'readonly' => $readonly
        );

        return $this;
    }

    /**
     * Add a file by passing its contents directly
     *
     * @param string $filename
     * @param string $contents
     * @param bool   $readonly
     *
     * @return $this
     */
    public function addFileContents($filename, $contents, $readonly = false)
    {
        $type = File::determineFileType($filename);
        $file = new File($filename, $contents, $type, $readonly);

        $this->files[$filename] = $file;

        return $this;
    }

    public function getFileObject($filename)
    {
        if (!array_key_exists($filename, $this->files)) {
            throw new \LogicException(sprintf('Unknown file "%s"', $filename));
        }

        $this->initializeFileObject($filename);

        return $this->files[$filename];
    }

    public function getFilenames()
    {
        return array_keys($this->files);
    }

    public function getEntryPointFilename()
    {
        if ($this->entryPointFilename) {
           return $this->entryPointFilename;
        }

        if (count($this->files) == 1) {
            $files = $this->files;
            reset($files);
            return key($files);
        }

        throw new \LogicException('No entry point filename given!');
    }

    /**
     * Forces all the file contents to be loaded and returns all the File objects
     *
     * @return File[]
     */
    public function getAllFiles()
    {
        foreach ($this->getFilenames() as $filename) {
            $this->initializeFileObject($filename);
        }

        return $this->files;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    private function initializeFileObject($filename)
    {
        if ($this->files[$filename] === null) {
            // initialize the contents!
            $path = $this->pendingFileDetails[$filename]['path'];
            $this->addFileContents(
                $filename,
                file_get_contents($path),
                $this->pendingFileDetails[$filename]['readonly']
            );
        }

        return $this;
    }
}
