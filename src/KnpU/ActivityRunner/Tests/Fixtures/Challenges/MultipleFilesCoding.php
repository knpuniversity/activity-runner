<?php

namespace Challenge;

use KnpU\ActivityRunner\Activity\CodingChallenge\CodingContext;
use KnpU\ActivityRunner\Activity\CodingChallenge\CorrectAnswer;
use KnpU\ActivityRunner\Activity\CodingChallengeInterface;
use KnpU\ActivityRunner\Activity\CodingChallenge\CodingExecutionResult;
use KnpU\ActivityRunner\Activity\Exception\GradingException;
use KnpU\ActivityRunner\Activity\CodingChallenge\FileBuilder;

class MultipleFilesCoding implements CodingChallengeInterface
{
    /**
     * @return string
     */
    public function getQuestion()
    {
        return <<<EOF
test question...
EOF;
    }

    public function getFileBuilder()
    {
        $fileBuilder = new FileBuilder();

        $fileBuilder->addFileContents('index.php', <<<EOF
<h2>
    <!-- print the variable here -->
</h2>
EOF
        );

        $fileBuilder->addFileContents('bootstrap.php', <<<EOF
<?php \$whatILove = "Puppies"; require("index.php");
EOF
        );

        $fileBuilder->setEntryPointFilename('bootstrap.php');

        return $fileBuilder;
    }

    public function getExecutionMode()
    {
        return self::EXECUTION_MODE_PHP_NORMAL;
    }

    public function setupContext(CodingContext $context)
    {
    }

    public function grade(CodingExecutionResult $result)
    {
        $expected = 'Puppies';
        $result->assertOutputContains($expected);
        $result->assertElementContains('h2', $expected);
        $result->assertInputContains('index.php', 'echo');
        $result->assertInputContains('index.php', '$whatILove');
    }

    public function configureCorrectAnswer(CorrectAnswer $correctAnswer)
    {
        $correctAnswer->setFileContents('index.php', <<<EOF
<h2>
    <?php echo \$whatILove; ?>
</h2>
EOF
        );

        return $correctAnswer;
    }
}
