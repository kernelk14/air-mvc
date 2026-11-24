<?php

declare(strict_types=1);

namespace Air\Http;

use Air\Application;
use Air\Exceptions\Handler;
use Air\Routing\RouteDispatcher;
use Throwable;

final class Kernel
{
    /** @var array<int, string|callable|object> */
    private array $middleware = [];

    public function __construct(private Application $app, private Handler $handler)
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $dispatcher = $this->app->make(RouteDispatcher::class);

            $core = function (Request $request) use ($dispatcher): Response {
                $response = $dispatcher->dispatch($request);
                return $response;
            };

            $pipeline = array_reduce(
                array_reverse($this->middleware),
                function (\Closure $next, mixed $mw): \Closure {
                    return function (Request $request) use ($next, $mw): Response {
                        $instance = is_string($mw) ? $this->app->make($mw) : $mw;
                        if (!is_object($instance) || !method_exists($instance, 'process')) {
                            throw new \RuntimeException('Invalid middleware.');
                        }
                        $response = $instance->process($request, $next);
                        if (!$response instanceof Response) {
                            throw new \RuntimeException('Middleware must return a Response.');
                        }

                        return $response;
                    };
                },
                $core,
            );

            /** @var mixed $response */
            $response = $pipeline($request);
            if ($response instanceof Response) {
                return $response;
            }
            return $this->handler->render($request, new \RuntimeException('Invalid response.'));
        } catch (Throwable $e) {
            return $this->handler->render($request, $e);
        }
    }
}
