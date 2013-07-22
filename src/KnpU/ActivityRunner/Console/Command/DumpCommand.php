<?php

namespace KnpU\ActivityRunner\Console\Command;

use KnpU\ActivityRunner\Console\Command\PimpleAwareCommand;
use KnpU\ActivityRunner\Exception\ActivityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class DumpCommand extends PimpleAwareCommand
{
    /**
     * @var array
     */
    protected $configs;

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('activity', InputArgument::REQUIRED, 'Name of the activity'),
                new InputArgument('target', InputArgument::OPTIONAL, 'Path to where the files will be dumped', getcwd()),
            ))
            ->setName('activity:dump')
            ->setDescription('Dumps all skeleton files in a directory for testing')
            ->setHelp(<<<EOD
The <info>activity:dump</info> command dumps all skeleton files onto the filesystem. Files
are dumped to the current working directory by default. You can pass an optional
<info>target</info> argument to dump the files to a different diretory.
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
        $targetPath   = $input->getArgument('target');

        if (!array_key_exists($activityName, $this->configs)) {
            throw new ActivityNotFoundException($activityName, array_keys($this->configs));
        }

        /** @var \Symfony\Component\Filesystem\Filesystem */
        $filesystem = $this->get('filesystem');

        if (!is_dir($targetPath)) {
            $filesystem->mkdir($targetPath);
        }

        $activityConfig = $this->configs[$activityName];

        foreach ($activityConfig['skeletons'] as $logicalName => $physicalName) {
            $filesystem->copy($physicalName, $targetPath.'/'.$logicalName);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $builder = $this->get('config_builder');
        $configs = $builder->build($this->get('courses_path'));

        $this->configs = $configs;
    }

    /**
     * @param string $serviceOrParamName
     *
    /* @return mixed
     */
    protected function get($serviceOrParamName)
    {
        return $this->getPimple()->offsetGet($serviceOrParamName);
    }
}
