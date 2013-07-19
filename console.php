#!/usr/bin/env php
<?php

require __DIR__.'/app/autoload.php';

use KnpU\ActivityRunner\Console\Command\RunCommand;
use KnpU\ActivityRunner\Console\PimpleAwareApplication;

$application = new PimpleAwareApplication();
$application->addCommands(array(
    new RunCommand()
));
$application->run();
