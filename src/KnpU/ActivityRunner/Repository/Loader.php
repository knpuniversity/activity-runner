<?php

namespace KnpU\ActivityRunner\Repository;

use KnpU\ActivityRunner\Repository\Naming\Hyphened;
use KnpU\ActivityRunner\Repository\Naming\Strategy;
use Symfony\Component\Process\Process;

/**
 * Loads Git repositories.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class Loader implements LoaderInterface
{
    /**
     * @var callable|null
     */
    protected $commandFunc;

    /**
     * @var Strategy
     */
    protected $namingStrategy;

    /**
     * @param Strategy $namingStrategy  Strategy for repository filesystem name generation
     */
    public function __construct(Strategy $namingStrategy, Filesystem $filesystem = null)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function load($url, $ref)
    {
        $name    = $this->namingStrategy->create($url, $ref);
        $command = $this->makeCommand($url, $name, $ref);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                sprintf('Execution of command "%s" failed. %s', $command, $process->getErrorOutput()),
                $process->getExitCode()
            );
        }

        return new Repository($name);
    }

    /**
     * Sets a custom callback for generating the command line, which will be
     * used  for doing the actual loading. The following arguments are passed
     * to the callback:
     *
     *  -  string $url   URL of the repository
     *  -  string $path  path to where the repository should be checked out
     *  -  string $ref   reference (e.g. commit hash, branch)
     *
     * @param callable|null $commandFunc
     *
     * @throws \LogicException if the command is neither callable nor null
     */
    public function setCommandFunc($commandFunc)
    {
        if (!is_null($commandFunc) && !is_callable($commandFunc)) {
            $type = is_object($commandFunc) ? get_class($commandFunc) : gettype($commandFunc);

            throw new \LogicException(sprintf('Argument must be callable, got "%s" instead.', $type));
        }

        $this->commandFunc = $commandFunc;
    }

    /**
     * By default a callback creates the command line for cloning a Git
     * repository. This can be overriden to perhaps allow other VCS-s as
     * well as simplify testing.
     *
     * @param string $url
     * @param string $path
     * @param string $ref
     *
     * @return string
     */
    protected function makeCommand($url, $path, $ref)
    {
        $commandFunc = $this->commandFunc ?: function ($url, $path, $ref) {
            return sprintf('git clone %s %s && cd %2$s && git fetch origin && git checkout %3$s', $url, $path, $ref);
        };

        return $commandFunc($url, $path, $ref);
    }
}