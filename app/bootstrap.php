<?php

require_once __DIR__.'/autoload.php';

use Silex\Application;

$app = new Application();

// the front controller should set the $debug flag. This is a hacky way of doing this, but the flag
// needs to be set early so we can use it when setting up services
$app['debug'] = isset($debug) ? $debug : false;

require __DIR__.'/config/parameters_default.php';

if (is_file($paramFile = __DIR__.'/config/parameters.php')) {
    require($paramFile);
}

require(__DIR__.'/config/services.php');
require(__DIR__.'/config/routing.php');

return $app;
