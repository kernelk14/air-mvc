<?php

declare(strict_types=1);

namespace Air\Routing;

final class RouteCollection
{
    /** @var array<string, array<int, Route>> */
    private array $routes = [];

    /** @var array<string, Route> */
    private array $named = [];

    public function add(Route $route): Route
    {
        foreach ($route->methods as $method) {
            $method = strtoupper($method);
            $this->routes[$method][] = $route;
        }
        if ($route->name !== null) {
            $this->named[$route->name] = $route;
        }

        return $route;
    }

    /** @return array<int, Route> */
    public function all(): array
    {
        $all = [];
        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                $all[spl_object_id($route)] = $route;
            }
        }

        return array_values($all);
    }

    public function getByName(string $name): ?Route
    {
        return $this->named[$name] ?? null;
    }

    public function addNamed(Route $route): void
    {
        if ($route->name !== null) {
            $this->named[$route->name] = $route;
        }
    }

    /** @return array<int, Route> */
    public function forMethod(string $method): array
    {
        return $this->routes[strtoupper($method)] ?? [];
    }

    /** @return string[] */
    public function allowedMethodsForPath(string $path): array
    {
        $allowed = [];
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route) {
                if (Router::matchPath($route->uri, $path) !== false) {
                    $allowed[] = $method;
                }
            }
        }

        return array_values(array_unique($allowed));
    }
}
