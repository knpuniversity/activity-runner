<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge\MultipleChoice;

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

}