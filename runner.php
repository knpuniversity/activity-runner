#!/usr/bin/env php
<?php

use KnpU\ActivityRunner\Console\Application;

$app = require(__DIR__.'/app/bootstrap.php');

$application = new Application('UNKNOWN', 'UNKNOWN', $app);
$application->run();
