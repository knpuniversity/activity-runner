<?php

namespace KnpU\ActivityRunner\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ListCommand extends PimpleAwareCommand
{
    public function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration YAML file')
            ))
            ->setName('activity:list')
            ->setDescription('Lists all activities')
            ->setHelp(<<<EOD
The <info>activity:list</info> command lists all activities by name. You can optionally
specify the location of the configuration file by specifying the <info>config</info> option.
EOD
)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pimple = $this->getPimple();

        $builder = $pimple['config_builder'];
        $path    = $input->getOption('config') ?: $pimple['courses_path'];

        $activities = array_keys($builder->build($path));

        $output->writeln($activities);
    }
}
