<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;

$app = new Application();

if (is_file($paramFile = __DIR__.'/config/parameters.php')) {
    require($paramFile);
} else {
    require($paramFile.'.dist');
}

require(__DIR__.'/config/services.php');
require(__DIR__.'/config/routing.php');

return $app;
