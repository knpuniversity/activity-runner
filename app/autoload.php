<?php

if (is_file($autoloadFile = __DIR__.'/../vendor/autoload.php')) {
    // Installed as a standalone project
} else if (is_file($autoloadFile = __DIR__.'/../../../autoload.php')) {
    // Installed as a library
} else {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install"?');
}

$loader = require $autoloadFile;
