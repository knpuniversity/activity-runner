<?php

namespace KnpU\ActivityRunner\Console\Command;

use Doctrine\Common\Collections\ArrayCollection;
use KnpU\ActivityRunner\Exception\ActivityNotFoundException;
use KnpU\ActivityRunner\Exception\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com
 */
class RunCommand extends PimpleAwareCommand
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
                new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration YAML file'),
                new InputOption('output-format', 'o', InputOption::VALUE_REQUIRED, 'Desired output format'),
                new InputOption('src', 's', InputOption::VALUE_REQUIRED, 'Source directory from where the files must be read'),
            ))
            ->setName('activity:run')
            ->setHelp(<<<EOD
The <info>activity:run</info> command makes it very simple to run activities
from the CLI.

You can change the location from where the input files are read in from by
specifying the <info>src</info> option. The current work direcotry is used by default.

    # Looks for files from the specified <info>src</info> path
    <comment>activity:run foo_actvitiy --src="path/to/input"</comment>

    # Looks for files from the cwd
    <comment>activity:run foo_activity</comment>

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
        $inputFiles   = $this->getInputFiles(
            $activityName,
            $input->getOption('src'),
            $input->getOption('config')
        );

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
        $pimple = $this->getPimple();
        $activityRunner = $pimple['activity_runner'];

        if ($config = $input->getOption('config')) {
            $activityRunner->setConfigPaths($input->getOption('config'));
        }

        $this->activityRunner = $activityRunner;
    }

    /**
     * @param string $activity               Name of the activity
     * @param string|null $inputsPath        Path to the input files (cwd by default)
     * @param string|array|null $configPath  Paths to the configuration files
     *
     * @return ArrayCollection
     */
    protected function getInputFiles($activity, $inputsPath = null, $configPath = null)
    {
        $inputsPath = $inputsPath ?: getcwd();
        $configPath = $configPath ?: $this->get('courses_path');

        $configs = $this
            ->get('config_builder')
            ->build($configPath)
        ;

        if (!array_key_exists($activity, $configs)) {
            throw new ActivityNotFoundException($activity, array_keys($configs));
        }

        $inputFiles = new ArrayCollection();

        foreach (array_keys($configs[$activity]['skeletons']) as $logicalName) {
            $inputFileName = $inputsPath.'/'.$logicalName;

            if (!is_file($inputFileName)) {
                throw new FileNotFoundException($inputFileName);
            }

            $inputFiles->set($logicalName, file_get_contents($inputFileName));
        }

        return $inputFiles;
    }

    /**
     * @param string $serviceOrParameterName
     *
     * @return mixed
     */
    protected function get($serviceOrParameterName)
    {
        return $this->getPimple()->offsetGet($serviceOrParameterName);
    }
}
