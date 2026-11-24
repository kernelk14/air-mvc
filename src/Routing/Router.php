<?php

declare(strict_types=1);

namespace Air\Routing;

use Closure;

final class Router
{
    /** @var array{prefix:string,middleware:array<int,string|callable|object>}[] */
    private array $groupStack = [];

    public function __construct(private RouteCollection $routes)
    {
    }

    public function routes(): RouteCollection
    {
        return $this->routes;
    }

    /** @param array<string,string|int> $params */
    public function route(string $name, array $params = []): string
    {
        $route = $this->routes->getByName($name);
        if ($route === null) {
            throw new \InvalidArgumentException("Route [{$name}] not found.");
        }
        $uri = $route->uri;
        foreach ($params as $key => $value) {
            $uri = preg_replace('/\{' . preg_quote((string) $key, '/') . '\}/', (string) $value, $uri) ?? $uri;
        }
        return $uri;
    }

    public function get(string $uri, mixed $action): Route
    {
        return $this->add(['GET'], $uri, $action);
    }

    public function post(string $uri, mixed $action): Route
    {
        return $this->add(['POST'], $uri, $action);
    }

    public function put(string $uri, mixed $action): Route
    {
        return $this->add(['PUT'], $uri, $action);
    }

    public function patch(string $uri, mixed $action): Route
    {
        return $this->add(['PATCH'], $uri, $action);
    }

    public function delete(string $uri, mixed $action): Route
    {
        return $this->add(['DELETE'], $uri, $action);
    }

    /** @param array{prefix?:string,middleware?:string|array<int,string|callable|object>} $attributes */
    public function group(array $attributes, Closure $callback): void
    {
        $prefix = trim((string) ($attributes['prefix'] ?? ''), '/');
        $middleware = $attributes['middleware'] ?? [];
        $middleware = is_array($middleware) ? $middleware : [$middleware];

        $parent = $this->groupStack[array_key_last($this->groupStack)] ?? ['prefix' => '', 'middleware' => []];
        $mergedPrefix = trim($parent['prefix'] . '/' . $prefix, '/');
        $mergedMiddleware = array_values(array_merge($parent['middleware'], $middleware));

        $this->groupStack[] = ['prefix' => $mergedPrefix, 'middleware' => $mergedMiddleware];
        $callback($this);
        array_pop($this->groupStack);
    }

    /** @param string[] $methods */
    public function add(array $methods, string $uri, mixed $action): Route
    {
        $group = $this->groupStack[array_key_last($this->groupStack)] ?? ['prefix' => '', 'middleware' => []];
        $prefix = $group['prefix'];
        $uri = '/' . trim($prefix . '/' . trim($uri, '/'), '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $route = new Route(array_map('strtoupper', $methods), $uri, $action, $group['middleware']);
        $this->routes->add($route);

        return $route;
    }

    /** @return array<string,mixed>|false */
    public static function matchPath(string $routeUri, string $path): array|false
    {
        $routeUri = '/' . trim($routeUri, '/');
        if ($routeUri !== '/') {
            $routeUri = rtrim($routeUri, '/');
        }
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_-]*)\}#', '(?P<$1>[^/]+)', $routeUri);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $path, $matches) !== 1) {
            return false;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
