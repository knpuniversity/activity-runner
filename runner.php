#!/usr/bin/env php
<?php

if (is_file($autoloadFile = __DIR__.'/vendor/autoload.php')) {
    // Installed as a standalone project
} else if (is_file($autoloadFIle = __DIR__.'/../../autoload.php')) {
    // Installed as a library
} else {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install"?');
}

require $autoloadFile;

use KnpU\ActivityRunner\Application;

$application = new Application();
$application->run();
