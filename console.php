#!/usr/bin/env php
<?php

require __DIR__.'/app/autoload.php';

use KnpU\ActivityRunner\Console\Command\DumpCommand;
use KnpU\ActivityRunner\Console\Command\ListCommand;
use KnpU\ActivityRunner\Console\Command\RunCommand;
use KnpU\ActivityRunner\Console\PimpleAwareApplication;

$configPath = __DIR__.'/app/config/';
$paramsFile = $configPath.'parameters.php';

$pimple = require($configPath.'services.php');
$pimple = require(is_file($paramsFile) ? $paramsFile : $paramsFile.'.dist');

$application = new PimpleAwareApplication('UNKNOWN', 'UNKNOWN', $pimple);
$application->addCommands(array(
    new DumpCommand(),
    new ListCommand(),
    new RunCommand(),
));
$application->run();
