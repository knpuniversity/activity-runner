<?php

namespace KnpU\ActivityRunner\Repository\Naming;

/**
 * The hyphened naming strategy creates the path from the url and reference
 * using the following rules:
 *
 *  -  discard everything before the last colon;
 *  -  discard the extension;
 *  -  replace all forward slasees with hyphens;
 *  -  concatenate the string with the reference with a hyphen between them;
 *
 * For example, given a URL `git@github.com:baz/bah.git` and branch `master`,
 * the name would be `baz-bah-master`.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class Hyphened implements Strategy
{
    /**
     * @var string
     */
    protected $base;

    /**
     * @param string $base
     */
    public function __construct($base = '')
    {
        $this->base = (string) $base;
    }

    /**
     * {@inheritDoc}
     */
    public function create($url, $ref)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // A regular URL.
            $name = trim(parse_url($url, PHP_URL_PATH), '/');
        } else {
            // Most likely a Git address (e.g. git@github.com:foo/baz.git.
            $colonPos  = strrpos($url, ':');
            $colonPos += false !== $colonPos ? 1 : 0;

            $name = substr($url, $colonPos);
        }

        if (false !== ($extPos = strrpos($name, '.'))) {
            // Removes the extension (`.git`).
            $name = substr($name, 0, $extPos);
        }

        $name = str_replace('/', '-', $name);
        $name = $name.'-'.$ref;

        if ($base = $this->base) {
            $name = $base.'/'.$name;
        }

        return $name;
    }
}
