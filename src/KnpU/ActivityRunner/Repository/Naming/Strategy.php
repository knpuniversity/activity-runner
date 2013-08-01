<?php

namespace KnpU\ActivityRunner\Repository\Naming;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
interface Strategy
{
    /**
     * @param string $url
     * @param string $ref
     *
     * @return string
     */
    function create($url, $ref);
}
