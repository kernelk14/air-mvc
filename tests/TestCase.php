<?php

declare(strict_types=1);

namespace Tests;

use Air\Application;
use Air\Auth\AuthManager;
use Air\Config\Repository;
use Air\Database\Connection;
use Air\Exceptions\Handler;
use Air\Http\Kernel;
use Air\Log\Logger;
use Air\Routing\RouteCollection;
use Air\Routing\Router;
use Air\Security\Csrf;
use Air\Session\SessionManager;
use Air\View\ViewFactory;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        parent::setUp();
        $base = dirname(__DIR__);
        $this->app = new Application($base);
        $config = $this->app->make(Repository::class);
        $config->merge([
            'app' => ['name' => 'Air MVC', 'env' => 'testing', 'debug' => true, 'url' => '', 'timezone' => 'UTC'],
            'database' => ['default' => 'mysql', 'connections' => []],
        ]);
        $this->app->singleton(Repository::class, fn () => $config);
        $this->app->singleton(Logger::class, fn (Application $app) => new Logger($app->basePath('storage/logs/test.log')));
        $this->app->singleton(SessionManager::class, fn () => new SessionManager());
        $this->app->singleton(Csrf::class, fn (Application $app) => new Csrf($app->make(SessionManager::class)));
        $this->app->singleton(Connection::class, fn (Application $app) => new Connection($app->make(Repository::class)));
        $this->app->singleton(AuthManager::class, fn (Application $app) => new AuthManager($app->make(SessionManager::class), $app->make(Connection::class)));
        $this->app->singleton(RouteCollection::class, fn () => new RouteCollection());
        $this->app->singleton(Router::class, fn (Application $app) => new Router($app->make(RouteCollection::class)));
        $this->app->singleton(ViewFactory::class, fn (Application $app) => new ViewFactory($app));
        $this->app->singleton(Handler::class, fn (Application $app) => new Handler($app));
        $this->app->singleton(Kernel::class, fn (Application $app) => new Kernel($app, $app->make(Handler::class)));
        $router = $this->app->make(Router::class);
        $routes = $base . '/config/routes.php';
        if (is_file($routes)) {
            require $routes;
        }
        $this->app->boot();
    }
}
