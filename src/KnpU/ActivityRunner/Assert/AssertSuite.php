<?php

namespace KnpU\ActivityRunner\Assert;

use KnpU\ActivityRunner\ActivityInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
abstract class AssertSuite extends \PHPUnit_Framework_Assert
{
    /**
     * @var ActivityInterface
     */
    private $activity;

    /**
     * @var string
     */
    private $output;

    /**
     * @var \Exception
     */
    private $error;

    /**
     * @return ActivityInterface
     */
    protected function getActivity()
    {
        return $this->activity;
    }

    /**
     * Gets the crawler.
     *
     * @return Crawler
     */
    protected function getCrawler()
    {
        return new Crawler($this->getOutput());
    }

    /**
     * @return string
     */
    protected function getOutput()
    {
        return $this->output;
    }

    /**
     * @return \Exception|null
     */
    protected function getError()
    {
        return $this->error;
    }
}
