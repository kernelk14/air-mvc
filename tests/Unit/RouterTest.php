<?php

declare(strict_types=1);

namespace Tests\Unit;

use Air\Routing\RouteCollection;
use Air\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testRegistersAndMatchesRoute(): void
    {
        $router = new Router(new RouteCollection());
        $router->get('/users/{id}', fn () => 'ok')->name('users.show');

        $route = $router->routes()->forMethod('GET')[0];
        $params = Router::matchPath($route->uri, '/users/42');

        $this->assertSame(['id' => '42'], $params);
        $this->assertSame('users.show', $route->name);
    }

    public function testNormalizesTrailingSlash(): void
    {
        $this->assertSame([], Router::matchPath('/login/', '/login'));
        $this->assertFalse(Router::matchPath('/login', '/missing'));
    }

    public function testAllowedMethodsForPath(): void
    {
        $router = new Router(new RouteCollection());
        $router->get('/post', fn () => 'ok');
        $router->post('/post', fn () => 'ok');

        $allowed = $router->routes()->allowedMethodsForPath('/post');
        sort($allowed);
        $this->assertSame(['GET', 'POST'], $allowed);
    }
}
