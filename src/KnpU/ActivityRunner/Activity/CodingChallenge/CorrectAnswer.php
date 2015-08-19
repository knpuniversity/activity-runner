<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

/**
 * Represents a correct answer to a challenge
 */
class CorrectAnswer
{
    private $files;

    public static function createFromFileBuilder(FileBuilder $builder)
    {
        $answer = new static();

        foreach ($builder->getFileContents() as $file) {
            $answer->setFileContents($file->getFilename(), $file->getContents());
        }

        return $answer;
    }

    public function setFileContents($filename, $contents)
    {
        $type = File::determineFileType($filename);
        $file = new File($filename, $contents, $type);

        $this->files[$filename] = $file;

        return $this;
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        return $this->files;
    }
}