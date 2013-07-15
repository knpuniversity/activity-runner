<?php

namespace KnpU\ActivityRunner\Worker;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Assert\AssertSuite;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;

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

        $baseDir = $this->setUp($inputFiles, $this->filesystem);
        $output  = $this->execute($baseDir, $entryPoint);

        $this->tearDown($baseDir, $this->filesystem);

        $result = new Result($output);
        $result->setInputFiles($inputFiles);

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
     * Runs the PHP interpreter on user input.
     *
     * @param string $baseDir     Base directory
     * @param string $entryPoint  Single point of entry; the file that gets executed
     *
     * @return string  Command output
     */
    private function execute($baseDir, $entryPoint)
    {
        $phpFinder = new PhpExecutableFinder();

        $path = $baseDir.'/'.$entryPoint;
        $php  = $phpFinder->find();
        $cmd  = sprintf('%s %s', $php, $path);

        exec($cmd, $output);

        return implode("\n", $output);
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
