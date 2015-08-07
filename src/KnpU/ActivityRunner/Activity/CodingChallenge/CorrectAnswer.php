<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

/**
 * Represents a correct answer to a challenge
 */
class CorrectAnswer
{
    private $files;

    public function setFileContents($filename, $contents)
    {
        $this->files[$filename] = $contents;

        return $this;
    }

    public function getFiles()
    {
        return $this->files;
    }
}