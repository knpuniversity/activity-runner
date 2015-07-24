<?php

// Routing configuration. You should have the `$app` variable set to be
// an instance of Silex Application.

use Silex\Application;

if (!isset($app)) {
    throw new \LogicException('The `$app` varialbe is not set.');
}

if (!$app instanceof Application) {
    throw new \LogicException(sprintf('Expected $app to be an instance of Silex\\Application, got %s instead.', is_object($app) ? get_class($app) : gettype($app)));
}

$app->get('/status', 'KnpU\\ActivityRunner\\Controller\\ActivityController::statusAction');
$app->post('/check', 'KnpU\\ActivityRunner\\Controller\\ActivityController::checkAction');
