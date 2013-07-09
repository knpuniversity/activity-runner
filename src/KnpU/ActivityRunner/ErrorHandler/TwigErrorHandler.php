<?php

namespace KnpU\ActivityRunner\ErrorHandler;

use KnpU\ActivityRunner\Exception\TwigException;

/**
 * This error handler is used to recover from possible errors caused by
 * malformed user input. Instead of aborting right away, an exception is
 * thrown.
 *
 * The handler is registered simply by creating a new instance. Likewise, the
 * previous handler is restored, if the object is destroyed. However, to be
 * more explicit, you should call `TwigErrorHandler::restore` yourself.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class TwigErrorHandler
{
    /**
     * @var mixed
     */
    private $previousHandler;

    /**
     * @var integer
     */
    private $previousDisplayErrors;

    protected function __construct()
    {
        $this->previousDisplayErrors = ini_set('display_errors', 0);
        $this->previousHandler       = set_error_handler(array($this, 'handle'));
    }

    /**
     * @return TwigErrorHandler
     */
    public static function register()
    {
        return new static();
    }

    public function restore()
    {
        ini_set('display_errors', $this->previousDisplayErrors);

        if ($this->previousHandler) {
            set_error_handler($this->previousHandler);
        } else {
            $this->rewindErrorHandler();
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return boolean
     */
    public function handle($level, $message, $file = '', $line = null, array $context = array())
    {
        $map = array(
            'Array to string conversion' => 'Looks like you\'re trying to print an array. You should be using something along the lines of `{% for var in arrayVar %}{{ var }}{% endfor %}` instead.',
            'Object of class DateTime could not be converted to string' => 'You have to use the `date` filter.',
        );

        foreach ($map as $needle => $replacement) {
            if (false !== strpos($message, $needle)) {

                // We generally know why the specific error has occcurred.
                throw new TwigException($replacement, $level);
            }
        }

        // An unfamiliar exception, resort to a runtime exception.
        throw new \RuntimeException($message, $level);

        // Execute the default error handler, if we've reached here.
        return false;
    }

    /**
     * There is no other way but to use `restore_error_handler` to set the
     * default PHP error handler.
     *
     * @see http://www.php.net/manual/en/function.restore-error-handler.php#93261
     */
    private function rewindErrorHandler()
    {
        $dummyHandler = function ($level, $message) { return false; };

        while (set_error_handler($dummyHandler)) {
            // Unsets the error handler we just set.
            restore_error_handler();
            // Unsets the previous error handler.
            restore_error_handler();
        }

        // Restores the built-in error handler.
        restore_error_handler();
    }
}
