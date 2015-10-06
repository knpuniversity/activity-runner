<?php

use Monolog\Logger;

/** @var $app \Silex\Application */
if (!$app instanceof \Silex\Application) {
    throw new \LogicException(sprintf('Expected $app to be an instance of \\Pimple, got %s instead.', is_object($app) ? get_class($app) : gettype($app)));
}

/*
 * Configuration
 */

$app['root_dir'] = __DIR__.'/../../';
$logFile = $app['debug'] ? 'dev.log' : 'prod.log';

/*
 * Service Providers
 */

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../logs/'.$logFile,
    'monolog.level'   => $app['debug'] ? Logger::DEBUG : Logger::CRITICAL,
));
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());

/*
 * Services
 */

$app['activity_runner'] = $app->share(function ($app) {
    $activityRunner = new KnpU\ActivityRunner\ActivityRunner(
        $app['worker_bag'],
        $app['twig'],
        $app['root_dir']
    );

    return $activityRunner;
});

$app['filesystem'] = $app->share(function () {
    return new Symfony\Component\Filesystem\Filesystem();
});

$app['php_parser'] = $app->share(function () {
    return new \PHPParser_Parser(new \PHPParser_Lexer());
});

$app['worker_bag'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Worker\WorkerBag(array(
        $app['worker.twig'],
        $app['worker.php']
    ));
});

$app['worker.php'] = $app->share(function ($app) {
    $worker = new KnpU\ActivityRunner\Worker\PhpWorker();

    return $worker;
});

$app['worker.twig'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Worker\TwigWorker();
});
$app['twig'] = $app->share(function() {
    $loader = new \Twig_Loader_Filesystem(array(
        __DIR__.'/../templates'
    ));
    $env = new \Twig_Environment($loader, array(
        'cache'            => false,
        'debug'            => true,
        'strict_variables' => true,
    ));

    $env->addExtension(new \Twig_Extension_Debug());

    return $env;
});

$app['yaml'] = $app->share(function () {
    return new Symfony\Component\Yaml\Yaml();
});

$app['logging_exception_listener'] = $app->share(function($app) {
    return new \KnpU\ActivityRunner\EventListener\LoggingExceptionListener($app['logger']);
});

$app->error(array($app['logging_exception_listener'], 'onKernelException'));

return $app;
