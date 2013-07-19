#!/usr/bin/env php
<?php

require __DIR__.'/app/autoload.php';

use KnpU\ActivityRunner\Console\Application;

$configPath = __DIR__.'/app/config/';
$paramsFile = $configPath.'parameters.php';

$pimple = require($configPath.'services.php');
$pimple = require(is_file($paramsFile) ? $paramsFile : $paramsFile.'.dist');

$application = new Application('UNKNOWN', 'UNKNOWN', $pimple);
$application->run();
