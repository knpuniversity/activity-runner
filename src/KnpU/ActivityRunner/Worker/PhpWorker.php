<?php

namespace KnpU\ActivityRunner\Worker;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Assert\AssertSuite;
use Symfony\Component\Filesystem\Filesystem;
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
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritDoc}
     */
    public function render(ActivityInterface $activity)
    {
        $inputFiles = $activity->getInputFiles();
        $entryPoint = $activity->getEntryPoint();

        $result = new Result();
        $result->setInputFiles($inputFiles);

        $process = $this->execute($inputFiles, $entryPoint);

        if ($process->isSuccessful()) {
            $result->setOutput($process->getOutput());
        } else {
            $result->setLanguageError($process->getErrorOutput());
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
    public function injectInternals(AssertSuite $suite)
    {
        // Nothing to inject at this point.
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
     * @param Collection $files
     * @param string $entryPoint
     *
     * @return Process  The process run; can be used to retrieve output
     */
    private function execute(Collection $files, $entryPoint)
    {
        $baseDir = $this->setUp($files, $this->filesystem);

        $process = $this->createProcess($baseDir, $entryPoint);
        $process->run();

        $this->tearDown($baseDir, $this->filesystem);

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

        return new Process(sprintf('%s %s/%s', $php, $baseDir, $entryPoint));
    }

    /**
     * Sets up the environment for processing user provided files. Basically
     * a random directory is created in `/tmp` and user files are stored in
     * it.
     *
     * @param Collection $files
     * @param Filesystem $filesystem
     * @return string  The newly generated base directory
     */
    private function setUp(Collection $files, Filesystem $filesystem)
    {
        do {
            $baseDir = sys_get_temp_dir().'/knpu_php_'.mt_rand();
        } while (is_dir($baseDir));

        foreach ($files as $path => $contents) {
            $filesystem->dumpFile($baseDir.'/'.$path, $contents);
        }

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
