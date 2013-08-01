<?php

namespace KnpU\ActivityRunner\Repository;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Loads a repository from the given URL.
     *
     * @param string $url  URL of the repository
     * @param string $ref  reference (e.g. commit hash, branch)
     *
     * @return Repository  repository object
     */
    function load($url, $ref);
}
