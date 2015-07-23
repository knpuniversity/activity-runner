<?php

namespace KnpU\ActivityRunner\Worker;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Result;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class PhpWorker implements WorkerInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \PHPParser_Parser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $prefix = 'knpu_php_';

    /**
     * The maximum amount of time the PHP process can take to execute.
     *
     * @var integer
     */
    protected $timeout = 10;

    private $currentBaseDir;

    /**
     * @param Filesystem $filesystem
     * @param \PHPParser_Parser $parser
     */
    public function __construct(Filesystem $filesystem, \PHPParser_Parser $parser)
    {
        $this->filesystem = $filesystem;
        $this->parser     = $parser;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Activity $activity)
    {
        if ($activity->getContextSource()) {
            throw new \LogicException(
                'The php worker does not support a context source. Just include it as an input file!'
            );
        }

        $inputFiles = $activity->getInputFiles();
        $entryPoint = $activity->getEntryPointFilename();

        $result = new Result($activity);

        try {
            $process = $this->executePhpProcess($inputFiles, $entryPoint, $result);

            if ($process->isSuccessful()) {
                $result->setOutput($process->getOutput());
            } else {
                if ($process->getErrorOutput()) {
                    $result->setLanguageError(
                        $this->cleanError($process->getErrorOutput())
                    );
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

                    $result->setLanguageError(
                        $this->cleanError($process->getOutput())
                    );
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

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($fileName, array $context)
    {
        return substr($fileName, -4) === '.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'php';
    }

    /**
     * Sets up the environment, executes user code and tears the environment
     * down again.
     *
     * @param array $files
     * @param string $entryPoint
     * @param Result $result
     *
     * @return Process  The process run; can be used to retrieve output
     *
     * @throws \Exception if the process fails
     *
     */
    private function executePhpProcess(array $files, $entryPoint, Result $result)
    {
        $this->currentBaseDir = $this->setUp($files, $this->filesystem);
        $process = $this->createProcess($this->currentBaseDir, $entryPoint);
        $process->setTimeout($this->timeout);

        try {
            $process->run();
        } catch (\Exception $e) { }

        // add in all the finished contents of the directory
        $finder = new Finder();
        $finder->in($this->currentBaseDir)
            ->files()
            ->ignoreVCS(true)
        ;
        foreach ($finder as $file) {
            /** @var \SplFileInfo $file */
            // get something like layout/header.php
            $relativePath = str_replace($this->currentBaseDir, '', $file->getPathname());
            // strip off the opening slash
            $relativePath = trim($relativePath, '/');

            $result->addFinalFileContents(
                $relativePath,
                file_get_contents($file->getPathname())
            );
        }

        $this->tearDown($this->currentBaseDir, $this->filesystem);

        if (isset($e)) {
            throw $e;
        }

        return $process;
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
            $baseDir = sys_get_temp_dir().'/'.$this->prefix.mt_rand();
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

    /**
     * Cleans up error messages
     *
     * Specifically, we might have a syntax error on /var/tmp/ABCD/index.php,
     * but we really want to just show "index.php"
     *
     * @param string $output
     * @return string
     */
    private function cleanError($output)
    {
        $output = str_replace($this->currentBaseDir.'/', '', $output);
        $output = str_replace($this->currentBaseDir, '', $output);

        return $output;
    }
}
