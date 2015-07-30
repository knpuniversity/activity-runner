<?php
/**
 * Created by PhpStorm.
 * User: weaverryan
 * Date: 7/23/15
 * Time: 7:06 PM
 */

namespace KnpU\ActivityRunner\Tests;


use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\ActivityRunner;
use KnpU\ActivityRunner\Result;

class ActivityRunnerTest extends \PHPUnit_Framework_TestCase
{
    private static $container;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
        self::$container = require_once __DIR__.'/../../../../app/bootstrap.php';
    }

    /**
     * @dataProvider getIntegrationTests
     */
    public function testIntegration(Activity $activity, Result $expectedResult)
    {
        /** @var ActivityRunner $runner */
        $runner = self::$container['activity_runner'];

        $result = $runner->run($activity);

        $description = sprintf(
            'Result not expected. Output "%s". Language Error: "%s". Grading Error: "%s"',
            $result->getOutput(),
            $result->getLanguageError(),
            $result->getGradingError()
        );

        $this->assertEquals(
            $expectedResult->getOutput(),
            $result->getOutput(),
            $description
        );
        $this->assertEquals(
            $expectedResult->isCorrect(),
            $result->isCorrect(),
            $description
        );
        $this->assertEquals(
            $expectedResult->getLanguageError(),
            $result->getLanguageError(),
            $description
        );
        $this->assertEquals(
            $expectedResult->getGradingError(),
            $result->getGradingError(),
            $description
        );
    }

    public function getIntegrationTests()
    {
        $tests = array();

        $activity = $this->createActivity('CreateVariableCoding');
        $activity->addInputFile('index.php', <<<EOF
<?php
\$airpupTag = 'I luv kittens';
?>

<h2><?php echo \$airpupTag; ?></h2>
EOF
        );

        $result = new Result($activity);
        $result->setOutput("\n<h2>I luv kittens</h2>");

        $tests['correct_simple'] = array($activity, $result);


        /* TEST START: Undefined variable */
        $activity = $this->createActivity('CreateVariableCoding');
        $activity->addInputFile('index.php', <<<EOF
Hello!

<h2><?php echo \$airpupTag; ?></h2>
EOF
        );

        $result = new Result($activity);
        $result->setOutput("Hello!\n\n<h2>"); // prints this before die'ing
        $result->setLanguageError('Notice: Undefined variable: airpupTag in index.php on line 3');

        $tests['php_undefined_variable'] = array($activity, $result);

return $tests;
        /* TEST START: Multiple files */
        $activity = $this->createActivity('MultipleFilesCoding');
        $activity->addInputFile('index.php', <<<EOF
<h2><?php echo \$whatILove; ?></h2>
EOF
        );
        // don't send bootstrap.php - that should use the default

        $result = new Result($activity);
        $result->setOutput('<h2>Puppies</h2>');

        $tests['correct_multiple_files'] = array($activity, $result);


        /* TEST START */
        $activity = $this->createActivity('CreateVariableCoding');
        $activity->addInputFile('index.php', <<<EOF
<?php

echo 'I luv dogs';
EOF
        );
        $result = new Result($activity);
        $result->setOutput('I luv dogs');
        $result->setGradingError('I don\'t see "I luv kittens" in the output.');

        $tests['incorrect_simple'] = array($activity, $result);


        /* TEST START */
        $activity = $this->createActivity('CreateVariableCoding');
        $activity->addInputFile('index.php', <<<EOF
<?php

echo 'I <3 Puppies!!!'
EOF
        );
        $result = new Result($activity);
        $result->setOutput(null);
        $result->setLanguageError("PHP Parse error:  syntax error, unexpected end of file, expecting ',' or ';' in index.php on line 3");

        $tests['php_syntax_error'] = array($activity, $result);


        /* TEST START */
        $activity = $this->createActivity('TwigPrintVariable');
        $activity->addInputFile('homepage.twig', <<<EOF
<h2>{{ whatIWantForXmas }}</h2>
EOF
        );
        $result = new Result($activity);
        $result->setOutput('<h2>Puppy</h2>');

        $tests['twig_correct_simple'] = array($activity, $result);


        /* TEST START */
        $activity = $this->createActivity('TwigPrintVariable');
        $activity->addInputFile('homepage.twig', <<<EOF
<h2>{{ bacon }}</h2>
EOF
        );
        $result = new Result($activity);
        $result->setOutput('');
        $result->setLanguageError('Variable "bacon" does not exist in "homepage.twig" at line 1');
        $tests['twig_bad_variable'] = array($activity, $result);


        /* TEST START */
        $activity = $this->createActivity('TwigPrintVariable');
        $activity->addInputFile('homepage.twig', <<<EOF
<h1>{{ 'foo }}</h1>
EOF
        );
        $result = new Result($activity);
        $result->setOutput('');
        $result->setLanguageError('Unexpected character "\'" in "homepage.twig" at line 1');
        $tests['twig_syntax_error'] = array($activity, $result);


        /* TEST START */
        $activity = $this->createActivity('TwigPrintVariable');
        $activity->addInputFile('homepage.twig', <<<EOF
<h1>{{ whatIWantForXmas }}</h1>
EOF
        );
        $result = new Result($activity);
        $result->setOutput('<h1>Puppy</h1>');
        $result->setGradingError('I don\'t see any "h2" HTML element with the text "Puppy" in it.');
        $tests['twig_grading_error'] = array($activity, $result);

        return $tests;
    }

    /**
     * Shortcut to create a challenge from the Fixtures/Challenges directory
     *
     * @param $className
     * @return Activity
     */
    private function createActivity($className)
    {
        $createVariableCodingPath = __DIR__.sprintf('/Fixtures/Challenges/%s.php', $className);
        require_once $createVariableCodingPath;

        return new Activity(
            'Challenge\\'.$className,
            file_get_contents($createVariableCodingPath)
        );
    }
}
