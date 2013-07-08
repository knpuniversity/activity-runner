<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;

$app = new Application();

require(__DIR__.'/config/services.php');
require(__DIR__.'/config/routing.php');

return $app;
