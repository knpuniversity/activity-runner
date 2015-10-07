<?php

namespace KnpU\ActivityRunner\Activity;

use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\Activity\CodingChallenge\CorrectAnswer;
use KnpU\ActivityRunner\Activity\Exception\GradingException;
use KnpU\ActivityRunner\Activity\CodingChallenge\FileBuilder;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;

/**
 * All activities will implement this interface
 */
interface CodingChallengeInterface extends ChallengeInterface
{
    const EXECUTION_MODE_PHP_NORMAL     = 'php_normal';
    const EXECUTION_MODE_TWIG_NORMAL    = 'twig_normal';
    const EXECUTION_MODE_GHERKIN        = 'gherkin';

    /**
     * Add files and set the entry point
     *
     * @return FileBuilder
     */
    public function getFileBuilder();

    /**
     * Choose an EXECUTION_MODE_* constant
     *
     * This is how the code will be executed
     *
     * @return string
     */
    public function getExecutionMode();

    /**
     * Configure the context for the code
     *
     * @param CodingContext $context
     * @return void
     */
    public function setupContext(CodingContext $context);

    /**
     * @param CodingExecutionResult $result
     * @return void
     * @throws GradingException If there are any grading problems
     */
    public function grade(CodingExecutionResult $result);

    /**
     * @param CorrectAnswer $correctAnswer A correct answer already filled in
     *                                     with the starting files from getFileBuilder()
     * @return void
     */
    public function configureCorrectAnswer(CorrectAnswer $correctAnswer);
}
