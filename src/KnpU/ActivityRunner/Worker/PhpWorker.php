<?php

namespace KnpU\ActivityRunner\Worker;

use Doctrine\Common\Collections\Collection;
use KnpU\ActivityRunner\Activity;
use KnpU\ActivityRunner\Result;
use KnpU\ActivityRunner\Worker\Executor\CodeExecutor;
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
        $entryPointFilename = $activity->getEntryPointFilename();

        $codeExecutor = new CodeExecutor($inputFiles, $entryPointFilename);
        $executionResult = $codeExecutor->executePhpProcess();

        $result = new Result($activity);
        $result->setLanguageError(
            $this->cleanError(
                $executionResult->getLanguageError(),
                $executionResult->getCodeDirectory()
            )
        );
        $result->setOutput($executionResult->getOutput());

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
     * Cleans up error messages
     *
     * Specifically, we might have a syntax error on /var/tmp/ABCD/index.php,
     * but we really want to just show "index.php"
     *
     * @param string $output
     * @return string
     */
    private function cleanError($output, $codeDirectory)
    {
        $output = str_replace($codeDirectory.'/', '', $output);
        $output = str_replace($codeDirectory, '', $output);

        return $output;
    }
}
