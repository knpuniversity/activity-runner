<?php

namespace KnpU\ActivityRunner\Worker;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\ActivityInterface;
use KnpU\ActivityRunner\Assert\AssertSuite;
use KnpU\ActivityRunner\Assert\PhpAwareInterface;
use Symfony\Component\Filesystem\Filesystem;
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
    protected $prefix;

    /**
     * The maximum amount of time the PHP process can take to execute.
     *
     * @var integer
     */
    protected $timeout;

    /**
     * @param Filesystem $filesystem
     * @param \PHPParser_Parser $parser
     */
    public function __construct(Filesystem $filesystem, \PHPParser_Parser $parser)
    {
        $this->filesystem = $filesystem;
        $this->parser     = $parser;

        $this->setTimeout();
        $this->setPrefix();
    }

    /**
     * Sets the timeout in seconds.
     *
     * @param integer|float $timeout
     */
    public function setTimeout($timeout = 0)
    {
        $this->timeout = $timeout;
    }

    /**
     * Sets the prefix that's used when creating a temporary directory.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix = 'knpu_php_')
    {
        $this->prefix = $prefix;
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

        try {

            $process = $this->execute($inputFiles, $entryPoint);

            if ($process->isSuccessful()) {
                $result->setOutput($process->getOutput());
            } else {
                if ($process->getErrorOutput()) {
                    $result->setLanguageError($process->getErrorOutput());
                } else {
                    // from experience, this could be a failure entirely to execute the entry point
                    throw new \LogicException(sprintf(
                        'An error occurred when running "%s": %s',
                        $process->getCommandLine(),
                        $process->getOutput()
                    ));
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
    public function injectInternals(AssertSuite $suite)
    {
        if ($suite instanceof PhpAwareInterface) {
            $suite->setParser($this->parser);
        }
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
     *
     * @throws \Exception if the process fails
     *
     */
    private function execute(Collection $files, $entryPoint)
    {
        $baseDir = $this->setUp($files, $this->filesystem);

        $process = $this->createProcess($baseDir, $entryPoint);
        $process->setTimeout($this->timeout);

        try {
            $process->run();
        } catch (\Exception $e) { }

        $this->tearDown($baseDir, $this->filesystem);

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
        return new Process(sprintf('exec %s %s/%s', $php, $baseDir, $entryPoint));
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
            $baseDir = sys_get_temp_dir().'/'.$this->prefix.mt_rand();
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
