<?php

namespace KnpU\ActivityRunner\Worker\Executor;

use KnpU\ActivityRunner\Result;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class CodeExecutor
{
    const PROCESS_TIMEOUT = 10;

    const PREFIX = 'knpu_php_';

    /**
     * @var array
     */
    private $files;

    private $entryPointFilename;

    private $currentBaseDir;

    private $filesystem;

    public function __construct(array $files, $entryPointFilename)
    {
        $this->files = $files;
        $this->entryPointFilename = $entryPointFilename;
        $this->filesystem = new Filesystem();
    }

    /**
     * Sets up the environment, executes user code and tears the environment
     * down again.
     *
     * @return ExecutionResult
     *
     * @throws \Exception if the process fails
     *
     */
    public function executePhpProcess()
    {
        $this->currentBaseDir = $this->setUp($this->files, $this->filesystem);
        $process = $this->createProcess($this->currentBaseDir, $this->entryPointFilename);
        $process->setTimeout(self::PROCESS_TIMEOUT);

        $result = new ExecutionResult($this->currentBaseDir);

        try {
            $process->run();

            if ($process->isSuccessful()) {
                $result->setOutput($process->getOutput());
            } else {
                if ($process->getErrorOutput()) {
                    $result->setLanguageError($process->getErrorOutput());
                } else {
                    // from experience, this could be a failure entirely to execute the entry point
                    // or it could be a parse error
                    // if it were the former, we should throw a large exception to help debugging
                    // but if it's a parse error, we should set it
                    // unfortunately, I'm not sure if we can really be sure which it is for now
//                    throw new \LogicException(sprintf(
//                        'An error occurred when running "%s": %s',
//                        $process->getCommandLine(),
//                        $process->getOutput()
//                    ));

                    $result->setLanguageError($process->getOutput());
                }
            }
        } catch (RuntimeException $e) {
            // A timeout is not an exceptional case. Since the validation
            // errors would be overwritten by the asserter, the message has
            // to be defined as a language error.
            if (strpos($e->getMessage(), 'timed-out') !== false) {
                $result->setLanguageError('It took too long time to execute your code.');
            } else {
                throw $e;
            }
        }

        /*
        // add in all the finished contents of the directory
        $finder = new Finder();
        $finder->in($this->currentBaseDir)
            ->files()
            ->ignoreVCS(true)
        ;
        foreach ($finder as $file) {
            // get something like layout/header.php
            $relativePath = str_replace($this->currentBaseDir, '', $file->getPathname());
            // strip off the opening slash
            $relativePath = trim($relativePath, '/');

            $result->addFinalFileContents(
                $relativePath,
                file_get_contents($file->getPathname())
            );
        }
        */

        $this->tearDown($this->currentBaseDir, $this->filesystem);

        return $result;
    }

    /**
     * Creates a new PHP process to execute the script.
     *
     * @param string $baseDir     Base directory
     * @param string $entryPoint  Single point of entry; the file that gets executed
     *
     * @return Process
     */
    private function createProcess($baseDir, $entryPoint)
    {
        $phpFinder = new PhpExecutableFinder();
        $php       = $phpFinder->find();

        // See http://symfony.com/doc/2.3/components/process.html#process-signals
        // for why exec is used here.
        // notice we move into the directory, so that paths are relative
        return new Process(sprintf('cd %s; exec %s %s', $baseDir, $php, $entryPoint));
    }

    /**
     * Sets up the environment for processing user provided files. Basically
     * a random directory is created in `/tmp` and user files are stored in
     * it.
     *
     * @param array $files
     * @param Filesystem $filesystem
     * @return string  The newly generated base directory
     */
    private function setUp(array $files, Filesystem $filesystem)
    {
        do {
            $baseDir = sys_get_temp_dir().'/'.self::PREFIX.mt_rand();
        } while (is_dir($baseDir));

        foreach ($files as $path => $contents) {
            $filesystem->dumpFile($baseDir.'/'.$path, $contents);
        }

        // resolve symlinks
        $baseDir = realpath($baseDir);

        return $baseDir;
    }

    /**
     * @param string $dirName
     * @param Filesystem $filesystem
     */
    private function tearDown($dirName, Filesystem $filesystem)
    {
        $filesystem->remove($dirName);
    }
}