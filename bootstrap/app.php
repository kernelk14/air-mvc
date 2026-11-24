<?php

declare(strict_types=1);

use Air\Application;
use Air\Config\Repository;
use Air\Database\Connection;
use Air\Database\Migrations\Migrator;
use Air\Exceptions\Handler;
use Air\Http\Kernel;
use Air\Routing\RouteCollection;
use Air\Routing\Router;
use Air\Support\Env;
use Air\View\ViewFactory;

$basePath = dirname(__DIR__);

Env::load($basePath);

$app = new Application($basePath);

// Load config
$config = $app->make(Repository::class);
foreach (['app', 'database'] as $file) {
    $path = $app->configPath($file . '.php');
    if (is_file($path)) {
        $config->merge([$file => require $path]);
    }
}

date_default_timezone_set((string) $config->get('app.timezone', 'UTC'));

$app->singleton(Repository::class, fn () => $config);
$app->singleton(Connection::class, fn (Application $app) => new Connection($app->make(Repository::class)));
$app->singleton(Migrator::class, fn (Application $app) => new Migrator($app, $app->make(Connection::class)));
$app->singleton(RouteCollection::class, fn () => new RouteCollection());
$app->singleton(Router::class, fn (Application $app) => new Router($app->make(RouteCollection::class)));
$app->singleton(ViewFactory::class, fn (Application $app) => new ViewFactory($app));
$app->singleton(Handler::class, fn (Application $app) => new Handler($app));
$app->singleton(Kernel::class, fn (Application $app) => new Kernel($app, $app->make(Handler::class)));

$router = $app->make(Router::class);
$routesFile = $app->configPath('routes.php');
if (is_file($routesFile)) {
    require $routesFile;
}

$app->boot();

return $app;
