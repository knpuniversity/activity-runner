<?php

namespace KnpU\ActivityRunner\Assert;

use KnpU\ActivityRunner\Exception\FileNotFoundException;
use KnpU\ActivityRunner\Exception\ClassNotFoundException;

/**
 * The job of ClassLoader is to load a class using either the autloader or
 * regular file includsion and then insantiate the class
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ClassLoader
{
    /**
     * Loads the source which can either be a FQCN or tries to see whether a
     * class exists in the file and includes it.
     *
     * @param string $source
     *
     * @return string  FQCN of the loaded class
     *
     * @throws FileNotFoundException   if a file was not found
     * @throws ClassNotFoundException  if a file was found but there was no class
     */
    public function load($source)
    {
        if (class_exists($source)) {
            $fqcn = $source;
        } else {
            if (!is_file($source)) {
                throw new FileNotFoundException($source);
            }

            $fqcn = $this->getClassFromFile($source);

            if (false === $fqcn) {
                throw new ClassNotFoundException($source);
            }

            // The file must also be loaded as it doesn't use PSR-0 naming
            // conventions in which case the autoloader wouldn't know how to
            // load the class.

            require_once $source;
        }

        return $fqcn;
    }

    /**
     * @see http://stackoverflow.com/questions/7153000#7153391
     *
     * @param string $source
     *
     * @return string|boolean FQCN or false if no class was defined in the file
     */
    private function getClassFromFile($source)
    {
        $fp     = fopen($source, 'r');
        $class  = '';
        $nspace = '';
        $buffer = '';

        $i = 0;

        while (!$class) {
            if (feof($fp)) {
                return false;
            }

            $buffer .= fread($fp, 512);
            $tokens = token_get_all($buffer);

            // No '{' symbol means most likely the buffer isn't long enough
            // so increase it right away.
            if (strpos($buffer, '{') === false) {
                continue;
            }

            $tokenCount = count($tokens);

            for ($i; $i < $tokenCount; $i++) {

                // Constructs the namespace.
                if (T_NAMESPACE === $tokens[$i][0]) {
                    for ($j = $i + 1; $j < $tokenCount; $j++) {
                        if (T_STRING === $tokens[$j][0]) {
                            $nspace .= '\\'.$tokens[$j][1];
                        } else if ('{' === $tokens[$j] || ';' === $tokens[$j]) {
                            break;
                        }
                    }
                }

                // Constructs the class.
                if (T_CLASS === $tokens[$i][0]) {
                    for ($j = $i + 1; $j < $tokenCount; $j++) {
                        if ('{' === $tokens[$j]) {
                            $class = $tokens[$i + 2][1];
                        }
                    }
                }
            }
        }

        return $class ? $nspace.'\\'.$class : false;
    }
}
