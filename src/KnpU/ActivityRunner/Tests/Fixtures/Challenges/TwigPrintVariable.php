<?php

namespace Challenge;

use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;
use KnpU\ActivityRunner\Activity\CodingChallenge\CorrectAnswer;
use KnpU\ActivityRunner\Activity\CodingChallengeInterface;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\Activity\Exception\GradingException;
use KnpU\ActivityRunner\Activity\CodingChallenge\FileBuilder;

class TwigPrintVariable implements CodingChallengeInterface
{
    /**
     * @return string
     */
    public function getQuestion()
    {
        return <<<EOF
test question... for twig!
EOF;
    }

    public function getFileBuilder()
    {
        $fileBuilder = new FileBuilder();

        $fileBuilder->addFileContents('homepage.twig', <<<EOF
<h2>
    <!-- print the variable here -->
</h2>
EOF
        );

        return $fileBuilder;
    }

    public function getExecutionMode()
    {
        return self::EXECUTION_MODE_TWIG_NORMAL;
    }

    public function setupContext(CodingContext $context)
    {
        $context->addVariable('whatIWantForXmas', 'Puppy');
    }

    public function grade(CodingExecutionResult $result)
    {
        $expected = 'Puppy';
        $result->assertOutputContains($expected);
        $result->assertElementContains('h2', $expected);
        $result->assertInputContains('homepage.twig', 'whatIWantForXmas');
    }

    public function configureCorrectAnswer(CorrectAnswer $correctAnswer)
    {
        $correctAnswer->setFileContents('homepage.twig', <<<EOF
<h2>
    {{ whatIWantForXmas }}
</h2>
EOF
        );

        return $correctAnswer;
    }
}
