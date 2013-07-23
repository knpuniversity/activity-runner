<?php

// Routing configuration. You should have the `$app` variable set to be
// an instance of Silex Application.

use Silex\Application;

if (!isset($app)) {
    throw new \LogicException('The `$app` varialbe is not set.');
}

if (!$app instanceof Application) {
    throw new \LogicExcpetion(sprintf('Expected $app to be an instance of Silex\\Application, got %s instead.', is_object($app) ? get_class($app) : gettype($app)));
}

$app->get('/status', controller('activity/status'));
$app->post('/check', controller('activity/check'));

/**
 * Turns a short controller name into a FQCN and the proper action method. For
 * example, "foo/baz" would become "My\Fqcn\FooController::bazAction".
 *
 * @param string $shortName
 *
 * @return string
 */
function controller($shortName)
{
    list($shortClass, $shortMethod) = explode('/', $shortName);

    return sprintf('KnpU\\ActivityRunner\\Controller\\%sController::%sAction', ucfirst($shortClass), $shortMethod);
}
