#!/usr/bin/env php
<?php

require __DIR__.'/app/autoload.php';

use KnpU\ActivityRunner\Console\Application;

$application = new Application();
$application->run();
