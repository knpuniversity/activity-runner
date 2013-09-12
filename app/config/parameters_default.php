<?php

/*
 * This file holds configuration for the application.
 *
 * If you'd like to override any of these on any server, just create a parameters.php file
 * and override anything you want. This file is always loaded first, and then a parameters.php
 * file opened if it exists.
 */

// Absolute path to the course metadata. This is used as a fallback location to
// search for activity configurations. It may be a string or an array.
$app['courses_path'] = __DIR__.'/../courses/';

// Maximum amount of time in seconds a worker can take to execute user code.
$app['worker.time_limit'] = 10;

return $app;
