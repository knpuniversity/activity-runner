<?php

namespace KnpU\ActivityRunner\Assert;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
interface TwigAwareInterface
{
    /**
     * @param \Twig_Environment $twig
     */
    function setTwig(\Twig_Environment $twig);
}
