<?php

namespace KnpU\ActivityRunner\Tests\ErrorHandler;

use KnpU\ActivityRunner\ErrorHandler\TwigErrorHandler;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class TwigErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    private static $phpUnitHandler;

    /**
     * Temporarily store PHPUnit's error handler so we can restore it after
     * each test.
     *
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!self::$phpUnitHandler) {
            self::$phpUnitHandler = set_error_handler($this->getDummyHandler());

            restore_error_handler();
        }
    }

    /**
     * Restore PHPUnit's own error handler.
     *
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        set_error_handler(self::$phpUnitHandler);
    }

    /**
     * @expectedException KnpU\ActivityRunner\Exception\TwigException
     */
    public function testHandlerThrowsTwigException()
    {
        $handler = TwigErrorHandler::register();

        // Should trigger an array to string conversion error, but since we're
        // using the twig error handler, an exception should be thrown instead.
        $date = new \DateTime();
        $string = (string) $date;
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testHandlerThrowsRuntimeException()
    {
        $handler = TwigErrorHandler::register();

        try {
            // Just something to trigger an error, but is not in the list
            // of known errors.
            $array = array();
            $array[0];
        } catch (\RuntimeException $e) {
            $this->assertNotInstanceOf('KnpU\\ActivityRunner\\Exception\\TwigException', $e);

            throw $e;
        }
    }

    public function testHandlerRestoresHandler()
    {
        $dummyHandler = $this->getDummyHandler();

        $previous = set_error_handler($dummyHandler);
        restore_error_handler();

        $handler = TwigErrorHandler::register();
        $handler->restore();

        $this->assertEquals($previous, set_error_handler($dummyHandler));
    }

    public function testHandlerRestoresDefaultHandler()
    {
        $dummyHandler = $this->getDummyHandler();

        // Restores the default error handler.
        while (set_error_handler($dummyHandler)) {
            // Unset the just set dummy handler.
            restore_error_handler();
            // Unset the previous handler.
            restore_error_handler();
        }

        // Restores the built-in handler (the last dummy handler we set will
        // be removed).
        restore_error_handler();

        $handler = TwigErrorHandler::register();

        try {
            // The test fails, if the Handler throws TwigException.
            $handler->restore();
        } catch (\Exception $e) {
            // The test has failed, but PHPUnit's error handler should be restored.
        }

        set_error_handler(self::$phpUnitHandler);

        if (isset($e)) {
            throw $e;
        }
    }

    /**
     * @return callable
     */
    private function getDummyHandler()
    {
        return function ($level, $message) { return false; };
    }
}
