<?php

namespace KnpU\ActivityRunner\Assert;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
abstract class TwigAwareSuite extends AssertSuite implements TwigAwareInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * {@inheritDoc}
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }
}
