<?php

// Service definitions. If `$app` is not defined, a new instance of Pimple is
// created. Alternatively, if the $app is already a Pimple instance, it
// is used instead.

if (!isset($app)) {
    $app = new \Pimple();
}

if (!$app instanceof \Pimple) {
    throw new \LogicExcpetion(sprintf('Expected $app to be an instance of \\Pimple, got %s instead.', is_object($app) ? get_class($app) : gettype($app)));
}

$app['activity_factory'] = function ($app) {
    return new KnpU\ActivityRunner\Factory\ActivityFactory(
        $app['assert_loader']
    );
};

$app['activity_runner'] = $app->share(function ($app) {
    $activityRunner = new KnpU\ActivityRunner\ActivityRunner(
        $app['asserter'],
        $app['config_builder'],
        $app['activity_factory'],
        $app['worker_bag']
    );

    $activityRunner->setConfigPaths($app['courses_path']);

    return $activityRunner;
});

$app['annotation_reader'] = $app->share(function () {
    return new Doctrine\Common\Annotations\AnnotationReader();
});

$app['annotation_default_run_state'] = function () {
    return new KnpU\ActivityRunner\Assert\Suite\RunIf(array(
        'value' => new KnpU\ActivityRunner\Assert\Suite\Passed()
    ));
};

$app['asserter'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Assert\Asserter(
        $app['annotation_reader'],
        $app['annotation_default_run_state']
    );
});

$app['assert_loader'] = $app->share(function () {
    return new KnpU\ActivityRunner\Assert\ClassLoader();
});

$app['config_builder'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Configuration\ActivityConfigBuilder(
        $app['config_processor'],
        $app['config_definition'],
        $app['yaml']
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

$app['php_parser'] = $app->share(function () {
    return new \PHPParser_Parser(new \PHPParser_Lexer());
});

$app['worker_bag'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Worker\WorkerBag(array(
        $app['worker.twig'],
        $app['worker.php'],
        $app['worker.chained'],
    ));
});

$app['worker.chained'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Worker\ChainedWorker(array(
        $app['worker.twig'],
        $app['worker.php'],
    ));
});

$app['worker.php'] = $app->share(function ($app) {
    return new KnpU\ActivityRunner\Worker\PhpWorker(
        $app['filesystem'],
        $app['php_parser']
    );
});

$app['worker.twig'] = $app->share(function () {
    return new KnpU\ActivityRunner\Worker\TwigWorker();
});

$app['yaml'] = $app->share(function () {
    return new Symfony\Component\Yaml\Yaml();
});

return $app;
