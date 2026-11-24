<?php

declare(strict_types=1);

namespace Tests\Unit;

use Air\Http\Kernel;
use Air\Http\Middleware\MiddlewareInterface;
use Air\Http\Request;
use Air\Http\Response;
use Air\Routing\Router;
use Tests\TestCase as BaseTestCase;

require_once __DIR__ . '/../TestCase.php';

final class MiddlewareTest extends BaseTestCase
{
    public function testMiddlewareOrderAndShortCircuit(): void
    {
        $log = [];
        $this->app->bind('mw.a', function () use (&$log) {
            return new class ($log) implements MiddlewareInterface {
                public array $log;
                public function __construct(&$log)
                {
                    $this->log = &$log;
                }
                public function process(Request $request, \Closure $next): Response
                {
                    $GLOBALS['mwlog'][] = 'a.before';
                    $res = $next($request);
                    $GLOBALS['mwlog'][] = 'a.after';
                    return $res;
                }
            };
        });
        $this->app->bind('mw.b', function () {
            return new class () implements MiddlewareInterface {
                public function process(Request $request, \Closure $next): Response
                {
                    $GLOBALS['mwlog'][] = 'b.before';
                    $res = $next($request);
                    $GLOBALS['mwlog'][] = 'b.after';
                    return $res;
                }
            };
        });

        $GLOBALS['mwlog'] = [];
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->get('/mw', fn () => Response::html('ok'))->middleware(['mw.a', 'mw.b']);

        /** @var Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/mw']);
        $res = $kernel->handle($request);

        $this->assertSame('ok', $res->content());
        $this->assertSame(['a.before', 'b.before', 'b.after', 'a.after'], $GLOBALS['mwlog']);
    }
}
