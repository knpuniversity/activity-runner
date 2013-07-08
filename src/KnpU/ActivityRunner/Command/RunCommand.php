<?php

namespace KnpU\ActivityRunner\Command;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Assert\Asserter;
use KnpU\ActivityRunner\Assert\ClassLoader;
use KnpU\ActivityRunner\Configuration\ActivityConfigBuilder;
use KnpU\ActivityRunner\Configuration\ActivityConfiguration;
use KnpU\ActivityRunner\Exception\FileNotFoundException;
use KnpU\ActivityRunner\Exception\InvalidActivityException;
use KnpU\ActivityRunner\Worker\PhpWorker;
use KnpU\ActivityRunner\Worker\TwigWorker;
use KnpU\ActivityRunner\Worker\WorkerBag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use KnpU\ActivityRunner\Factory\ActivityFactory;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use KnpU\ActivityRunner\Worker\ChainedWorker;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com
 */
class RunCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('activity', InputArgument::REQUIRED, 'Name of the activity to be executed'),
                new InputArgument('file', InputArgument::IS_ARRAY, 'Input file paths'),
                new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration YAML file', './metadata.yml'),
                new InputOption('input-format', 'i', InputOption::VALUE_REQUIRED, 'Desired input format', 'fs'),
                new InputOption('output-format', 'o', InputOption::VALUE_REQUIRED, 'Desired output format')
            ))
            ->setName('activity:run')
            ->setHelp(<<<EOD
The <info>activity:run</info> command makes it very simple to run activities
from the CLI.

You can pass multiple files by specifying them usng the <info>file</info> argument. The
<info>input-format</info> option determines how the files are handled: if the option is
set to <comment>fs</comment> (default), then the files are assumed to be
actually existing on the filesystem. Setting the option to <comment>stdin</comment> makes
it possible to read files straight from STDIN. In that case the file names are expected
to be just the logical names of the files.

The <info>input-format</info> option determines the input format. The only supported
formats are <comment>fs</comment> (reads the input from all files given by the <info>file</info>
argument) and <comment>stdin</comment> (reads the input straight from STDIN). When
passing input from STDIN, separate files by using the "end of text" character ([Ctrl]+[C]).

The <info>output-format</info> option determines the output format. The following
formats are supported: <comment>yaml</comment>, <comment>array</comment>, <comment>json</comment>.

You can use the <info>config</info> option to specify the file containing
configuration for activities. The configuration must be in the following
format:

    <comment>my_first_activity</comment>:
        <comment>question</comment>: <info>Answer to life the universe and everything</info>
        <comment>skeletons</comment>: [<info>base.html.twig</info>, <info>extends.html.twig</info>, ...]
        <comment>context</comment>: <info>path/to/context.php</info>
        <comment>asserts</comment>: <info>Psr\Namespace\To\AssertSuite</info>

All paths can either be absolute or relative. Relative paths are translated to
absolute paths as if the current directory was the configuration file location.

The <comment>context</comment> parameter must point to a PHP script that returns an array of
context elements - the parameters passed down to twig templates.

The <comment>asserts</comment> parameter may additionally be a PSR namespace. However, the
class inside the file must extend <comment>KnpU\ActivityRunner\Assert\AssertSuite</comment>.
EOD
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (array('config') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $activityName = $input->getArgument('activity');
        $configPath   = $input->getOption('config');

        $config = $this->getConfig($configPath);

        $activityFactory = new ActivityFactory(new ClassLoader());
        $activityFactory->setConfig($config);

        $inputFiles = $this->getInputFiles($input);
        $activity   = $activityFactory->createActivity($activityName, $inputFiles);

        $worker = $this->createWorkerBag()->get($config[$activityName]['worker']);

        $result = $worker->render($activity);
        $result->setVerbosity($output->getVerbosity());
        $result->setFormat($input->getOption('output-format') ?: 'yaml');

        // only validate if we're at least somewhat valid
        if ($result->isValid()) {
            // Verify the output.
            $asserter = new Asserter();

            if (!$asserter->isValid($result, $activity)) {
                $result->setValidationErrors(
                    $asserter->getValidationErrors($result, $activity)
                );
            }
        }

        $output->write((string) $result);
    }

    /**
     * Reads input files from either stdin or filesystem. When reading from
     * stdin, simply hit [Ctrl]+[D] after entering a file - this signals the
     * command to start reading input for the next file.
     *
     * @param InputInterface $input
     */
    private function getInputFiles(InputInterface $input)
    {
        $inputFormat = $input->getOption('input-format');

        if ('stdin' === $inputFormat) {
            $isStdin = true;
        } else if ('fs' === $inputFormat) {
            $isStdin = false;
        } else {
            throw new \LogicException(sprintf('Invalid value `%s` for option `input-format`', $inputFormat));
        }

        $inputFiles = new ArrayCollection(array_flip($input->getArgument('file')));

        foreach ($inputFiles as $filePath => $fileContents) {

            if (!$isStdin) {
                if (!is_file($filePath)) {
                    // Resolving relative paths to absolute for easier debugging.
                    throw new FileNotFoundException((0 !== strpos($filePath, '/', 0) ? __DIR__.'/'.$filePath : $filePath));
                }

                if (!is_readable($filePath)) {
                    throw new \LogicException(sprintf('The file `%s` is not readable.', $filePath));
                }
            }

            $inputStream  = fopen($isStdin ? 'php://stdin' : $filePath, 'r');
            $fileContents = $this->readStream($inputStream);

            fclose($inputStream);

            $inputFiles->set($filePath, $fileContents);
        }

        return $inputFiles;
    }

    /**
     * @param unknown $inputStream
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    private function readStream($inputStream)
    {
        $userInput = '';
        $i         = 0;

        // Read chraracter by character
        while (!feof($inputStream)) {
            $c = fread($inputStream, 1);

            if (false === $c) {
                throw new \RuntimeException('Aborted');
            }

            // Backspace Character
            if ("\177" === $c) {

                // Move cursor backwards
                $output->write("\033[1D");

                // Pop the last character off the end of our string
                $userInput = substr($userinput, 0, $i);
            } else {
                $userInput .= $c;
                $i--;
            }
        }

        return $userInput;
    }

    /**
     * Retrieves the config from the specfied file and sanitizes it.
     *
     * @param string $configPath
     *
     * @return array  The sanitized config settings
     */
    private function getConfig($configPath)
    {
        $builder = new ActivityConfigBuilder(
            new Processor(),
            new ActivityConfiguration(),
            new Yaml()
        );

        return $builder->build($configPath);
    }

    /**
     * @return WorkerBag
     */
    private function createWorkerBag()
    {
        $twig = new TwigWorker();
        $php  = new PhpWorker();
        $chained = new ChainedWorker(array($twig, $php));

        return new WorkerBag(array($twig, $php, $chained));
    }
}
