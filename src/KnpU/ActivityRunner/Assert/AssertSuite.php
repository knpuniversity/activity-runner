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
     * Returns the submitting input
     *
     * You can leave filename blank if there is only one file
     *
     * @param null|string $filename
     * @return string
     * @throws \LogicException
     */
    protected function getInput($filename = null)
    {
        $inputs = $this->getActivity()->getInputFiles();
        if ($filename === null) {
            if (count($inputs) > 1) {
                throw new \InvalidArgumentException(sprintf('If your input contains more than 1 file, you must specify the filename.'));
            }

            return $inputs->first();
        }

        if (!isset($inputs[$filename])) {
            throw new \LogicException(sprintf(
                'No file named `%s` found as an input file, possible values are: `%s`',
                $filename,
                implode('`, `', $inputs->getKeys())
            ));
        }

        return $inputs[$filename];
    }
}
