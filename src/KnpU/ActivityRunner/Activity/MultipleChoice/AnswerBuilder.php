<?php

namespace KnpU\ActivityRunner\Activity\MultipleChoice;

class AnswerBuilder
{
    private $answers = array();

    private $correctAnswerIndex;

    /**
     * @param string $text
     * @param bool $isCorrect
     * @returns AnswerBuilder
     * @throws \Exception
     */
    public function addAnswer($text, $isCorrect = false)
    {
        $text = $this->textifyAnswer($text);
        $key = md5($text);
        $this->answers[$key] = $text;

        if ($isCorrect) {
            if ($this->correctAnswerIndex !== null) {
                throw new \LogicException(sprintf(
                    'Another answer is already marked as being correct: "%s"',
                    $this->getCorrectAnswerText()
                ));
            }

            $this->correctAnswerIndex = $key;
        }

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getCorrectAnswerText()
    {
        if ($this->correctAnswerIndex === null) {
            throw new \Exception('No answers are correct!');
        }

        return $this->answers[$this->correctAnswerIndex];
    }

    /**
     * @param string $sha
     * @return bool
     */
    public function isAnswerCorrect($sha)
    {
        return $this->correctAnswerIndex == $sha;
    }

    public function getCorrectAnswerIndex()
    {
        return $this->correctAnswerIndex;
    }

    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Tries to make sure answers are in a decent format for displaying
     *
     * @param $answer
     * @return string
     */
    private function textifyAnswer($answer)
    {
        if ($answer === true) {
            return 'true';
        }

        if ($answer === false) {
            return 'false';
        }

        if ($answer === 0) {
            // even returning the string 0 isn't printing well
            return '0&nbsp;';
        }

        return (string) $answer;
    }

}