<?php

namespace KnpU\ActivityRunner\Assert;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
interface PhpAwareInterface
{
    /**
     * @param \PHPParser_Parser $parser
     */
    function setParser(\PHPParser_Parser $parser);
}
