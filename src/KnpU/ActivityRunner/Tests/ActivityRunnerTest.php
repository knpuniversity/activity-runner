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
            'Result not expected. Output "%s". Language Error: "%s"',
            $result->getOutput(),
            $result->getLanguageError()
        );

        $this->assertEquals(
            $expectedResult->getOutput(),
            $result->getOutput(),
            $description
        );
        $this->assertEquals(
            $expectedResult->isValid(),
            $result->isValid(),
            $description
        );
        $this->assertEquals($expectedResult->getValidationError(), $result->getValidationError());
        $this->assertEquals($expectedResult->getLanguageError(), $result->getLanguageError());
    }

    public function getIntegrationTests()
    {
        $tests = array();

        $activity = new Activity('php', 'index.php');
        $activity->addInputFile('index.php', <<<EOF
<?php

echo 'I <3 Puppies!!!';
EOF
        )->addAssertExpression(
            "source('index.php').assertContains('<?php')"
        )->addAssertExpression(
            "source('index.php').assertContains('echo')"
        )->addAssertExpression(
            "output.assertContains('I <3 Puppies')"
        );
        $result = new Result($activity);
        $result->setOutput('I <3 Puppies!!!');

        $tests[] = array($activity, $result);


        /* TEST START: Multiple files */
        $activity = new Activity('php', 'bootstrap.php');
        $activity->addInputFile('index.php', <<<EOF
<?php
echo 'I <3 '.\$whatILove.'!';
EOF
        )
        // add a bootstrap file that then runs our file
        ->addInputFile('bootstrap.php', '<?php $whatILove = "Puppies"; require("index.php");')
        ->addAssertExpression(
            "output.assertContains('I <3 Puppies')"
        );
        $result = new Result($activity);
        $result->setOutput('I <3 Puppies!');

        $tests['multiple_files'] = array($activity, $result);


        /* TEST START */
        $activity = new Activity('php', 'index.php');
        $activity->addInputFile('index.php', <<<EOF
<?php

echo 'I <3 Puppies!!!';
EOF
        )->addAssertExpression(
            "source('index.php').assertContains('BLAH')"
        );
        $result = new Result($activity);
        $result->setOutput('I <3 Puppies!!!');
        $result->setValidationError('Incorrect');

        $tests[] = array($activity, $result);


        /* TEST START */
        $activity = new Activity('php', 'index.php');
        $activity->addInputFile('index.php', <<<EOF
<?php

echo 'I <3 Puppies!!!'
EOF
        );
        $result = new Result($activity);
        $result->setOutput(null);
        $result->setLanguageError("PHP Parse error:  syntax error, unexpected end of file, expecting ',' or ';' in index.php on line 3\n");

        $tests[] = array($activity, $result);


        /* TEST START */
        $activity = new Activity('twig', 'show.twig');
        $activity->addInputFile('show.twig', <<<EOF
<h1>{{ name|upper }}</h1>
EOF
        );
        $result = new Result($activity);
        $result->setOutput(null);
        $result->setLanguageError("PHP Parse error:  syntax error, unexpected end of file, expecting ',' or ';' in index.php on line 3\n");

        $tests[] = array($activity, $result);

        return $tests;
    }
}
