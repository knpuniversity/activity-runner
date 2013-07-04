#!/usr/bin/env php
<?php

require_once __DIR__.'/vendor/autoload.php';

use KnpU\ActivityRunner\Application;

$application = new Application();
$application->run();
