<?php

use Monolog\Logger;

/** @var $app \Silex\Application */
if (!$app instanceof \Silex\Application) {
    throw new \LogicException(sprintf('Expected $app to be an instance of \\Pimple, got %s instead.', is_object($app) ? get_class($app) : gettype($app)));
}

$logFile = $app['debug'] ? 'dev.log' : 'prod.log';
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../logs/'.$logFile,
    'monolog.level'   => $app['debug'] ? Logger::DEBUG : Logger::CRITICAL,
));

$app['activity_factory'] = function ($app) {
    return new KnpU\ActivityRunner\Factory\ActivityFactory(
        $app['assert_loader']
    );
};

$app['activity_runner'] = $app->share(function ($app) {
    $activityRunner = new KnpU\ActivityRunner\ActivityRunner(
        $app['worker_bag']
    );

    return $activityRunner;
});

$app['annotation_reader'] = $app->share(function () {
    return new Doctrine\Common\Annotations\AnnotationReader();
});

$app['assert_loader'] = $app->share(function () {
    return new KnpU\ActivityRunner\Assert\ClassLoader();
});

$app['config_builder'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Configuration\ActivityConfigBuilder(
        $app['config_processor'],
        $app['config_definition'],
        $app['yaml'],
        $app['path_expander']
    );
});

$app['config_definition'] = $app->share(function () {
    return new KnpU\ActivityRunner\Configuration\ActivityConfiguration();
});

$app['config_processor'] = $app->share(function () {
    return new Symfony\Component\Config\Definition\Processor();
});

$app['filesystem'] = $app->share(function () {
    return new Symfony\Component\Filesystem\Filesystem();
});

$app['path_expander'] = $app->share(function () {
    return new KnpU\ActivityRunner\Configuration\PathExpander();
});

$app['php_parser'] = $app->share(function () {
    return new \PHPParser_Parser(new \PHPParser_Lexer());
});

$app['repository.loader'] = $app->share(function ($app) {
    return $app['repository.loader.configurator'];
});

$app['repository.loader.cache'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Repository\Cache(
        $app['repository.loader.simple'],
        $app['repository.naming_strategy']
    );
});

$app['repository.loader.configurator'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Repository\Configurator(
        $app['repository.loader.cache'],
        $app['activity_factory'],
        $app['config_builder']
    );
});

$app['repository.loader.simple'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Repository\Loader(
        $app['repository.naming_strategy']
    );
});

$app['repository.naming_strategy'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Repository\Naming\Hyphened($app['courses_path']);
});

$app['worker_bag'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Worker\WorkerBag(array(
        $app['worker.twig'],
        $app['worker.php']
    ));
});

$app['worker.php'] = $app->share(function ($app) {
    $worker = new KnpU\ActivityRunner\Worker\PhpWorker(
        $app['filesystem'],
        $app['php_parser']
    );

    return $worker;
});

$app['worker.twig'] = $app->share(function () {
    return new KnpU\ActivityRunner\Worker\TwigWorker();
});

$app['yaml'] = $app->share(function () {
    return new Symfony\Component\Yaml\Yaml();
});

$app['logging_exception_listener'] = $app->share(function($app) {
    return new \KnpU\ActivityRunner\EventListener\LoggingExceptionListener($app['logger']);
});

$app->error(array($app['logging_exception_listener'], 'onKernelException'));

return $app;
