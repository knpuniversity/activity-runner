<?php

namespace KnpU\ActivityRunner\Command;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Exception\FileNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com
 */
class RunCommand extends Command
{
    /**
     * @var \KnpU\ActivityRunner\ActivityRunner
     */
    protected $activityRunner;

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('activity', InputArgument::REQUIRED, 'Name of the activity to be executed'),
                new InputArgument('file', InputArgument::IS_ARRAY, 'Input file paths'),
                new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration YAML file'),
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
You can also simply specify a directory. The command will then recursively try
to find all files named `activities.yml`.

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
        $activityName = $input->getArgument('activity');
        $inputFiles   = $this->getInputFiles($input);

        $result = $this->activityRunner->run($activityName, $inputFiles);
        $result->setVerbosity($output->getVerbosity());
        $result->setFormat($input->getOption('output-format') ?: 'yaml');

        $output->write((string) $result);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $app = require(__DIR__.'/../../../../app/config/services.php');

        if (is_file($paramFile = __DIR__.'/../../../../app/config/parameters.php')) {
            $app = require($paramFile);
        } else {
            $app = require($paramFile.'.dist');
        }

        $activityRunner = $app['activity_runner'];

        if ($config = $input->getOption('config')) {
            $activityRunner->setConfigPaths($input->getOption('config'));
        }

        $this->activityRunner = $activityRunner;
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

        // create one stream for stdin - we'll create individual streams below if we're not using stdin
        $inputStream = $isStdin ? fopen('php://stdin', 'r') : false;

        // if we're stdin, read the one stream and split on the 003 character
        if ($isStdin) {
            $inputContents = $this->readStream($inputStream, $inputFiles->getKeys());

            foreach ($inputContents as $filePath => $fileContents) {
                $inputFiles->set($filePath, $fileContents);
            }

            return $inputFiles;
        }

        foreach ($inputFiles as $filePath => $fileContents) {

            if (!is_file($filePath)) {
                // Resolving relative paths to absolute for easier debugging.
                throw new FileNotFoundException((0 !== strpos($filePath, '/', 0) ? getcwd().'/'.$filePath : $filePath));
            }

            if (!is_readable($filePath)) {
                throw new \LogicException(sprintf('The file `%s` is not readable.', $filePath));
            }

            $inputStream  = fopen($filePath, 'r');
            $fileContents = $this->readStream($inputStream);

            fclose($inputStream);

            $inputFiles->set($filePath, $fileContents);
        }

        return $inputFiles;
    }

    /**
     * Reads a stream
     *
     * This, unfortunately, acts in 2 very different ways depending on if
     * $files is empty or not:
     *
     *      a) If $files is empty, this is just parsing one file and it returns a string
     *
     *      b) If $files is not empty, this is parsing many files. It will go through
     *          the stream and at each 003 char, it will take all content so far and assign
     *          it to the first filename in $files. It then continues until the next 003
     *          and assigns it to the second filename in $files. The return value is an
     *          associative array of filename => the content of that file.
     *
     * @param $inputStream
     * @param array $files
     * @return array|string
     * @throws \RuntimeException
     * @throws \Exception
     */
    private function readStream($inputStream, $files = array())
    {
        $userInput = '';
        $i         = 0;

        $fileContents = array();

        // Read character by character
        $fileIndex = 0;
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
            } elseif ("\003" === $c && !empty($files)) {
                // reached end of file and $files isn't empty, so we're expecting multiple files

                if (!isset($files[$fileIndex])) {
                    throw new \Exception(sprintf('Found "%s" file endings but we\'ve run out of files!', $fileIndex+1));
                }

                $filename = $files[$fileIndex];
                $fileContents[$filename] = $userInput;

                // reset the input, up the file index
                $userInput = '';
                $fileIndex++;

            } else {
                $userInput .= $c;
                $i--;
            }
        }

        // are we returning contents of just one file or many files?
        return empty($files) ? $userInput : $fileContents;
    }
}
