<?php

// This check prevents access to debug front controllers that are deployed by
// accident to production servers.
if (!in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1',
))) {
    header('HTTP/1.0 403 Forbidden');
    die('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

$debug = true;
$app = require(__DIR__.'/../app/bootstrap.php');
$app->run();
