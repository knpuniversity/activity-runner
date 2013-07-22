<?php

namespace KnpU\ActivityRunner\Configuration;

use KnpU\ActivityRunner\Exception\UnexpectedTypeException;
use Symfony\Component\Finder\Finder;

/**
 * PathExpander expands a mix of directories and files into a list of files.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class PathExpander
{
    /**
     * Expands a list of paths into a list of files.
     *
     * $expander->expand($paths, '*.php')
     * $expander->expand($paths, '/\.php$/') // same as above
     * $expander->expand($paths, 'test.php')
     *
     * @param string|array $paths
     * @param string       $filePattern A pattern (a regexp, a glob, or a string)
     *
     * @throws UnexpectedTypeException if $paths is neither string nor array
     *
     * @return string[]
     */
    public function expand($paths, $filePattern = null)
    {
        // Supports both arrays & strings.
        if (is_string($paths)) {
            $paths = array($paths);
        } elseif (!is_array($paths)) {
            throw new UnexpectedTypeException($paths, 'string|array');
        }

        $dirs     = array();
        $resolved = array();

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $dirs[] = $path;
            } else {
                // Must be a path, no extra work needed to be done.
                $resolved[] = $path;
            }
        }

        if (!empty($dirs)) {

            // Recursively finds all files in the directories.
            $finder = new Finder();
            $finder->files()->in($dirs);

            if ($filePattern) {
                // Finds files matching the given pattern.
                $finder->name($filePattern);
            }

            foreach ($finder as $file) {
                $resolved[] = $file;
            }
        }

        return $resolved;
    }
}
