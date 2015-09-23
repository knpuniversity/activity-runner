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

$app->get('/', function () use ($app) {
    $url = $app['url_generator']->generate('enter_filename');

    return $app->redirect($url);
})->bind('homepage');
$app->get('/status', 'KnpU\\ActivityRunner\\Controller\\ActivityController::statusAction');
$app->post('/check', 'KnpU\\ActivityRunner\\Controller\\ActivityController::checkAction');

$app->get(
    '/author',
    'KnpU\\ActivityRunner\\Controller\\AuthorController::enterFilenameAction'
)->bind('enter_filename');
$app->get(
    '/author/activity',
    'KnpU\\ActivityRunner\\Controller\\AuthorController::renderActivityAction'
)->bind('render_activity');
$app->post(
    '/author/activity',
    'KnpU\\ActivityRunner\\Controller\\AuthorController::gradeAction'
)->bind('grade_activity');
