<?php

declare(strict_types=1);

namespace Air\Routing;

class Route
{
    /** @var array<string, string> */
    public array $parameters = [];

    /** @param string[] $methods
     * @param mixed $action
     * @param array<int, string|callable|object> $middleware
     */
    public function __construct(
        public array $methods,
        public string $uri,
        public mixed $action,
        public array $middleware = [],
        public ?string $name = null,
    ) {
        $this->uri = '/' . trim($uri, '/');
        if ($this->uri !== '/') {
            $this->uri = rtrim($this->uri, '/');
        }
    }

    public function name(string $name): self
    {
        $this->name = $name;
        $router = null;
        try {
            $app = \Air\Application::getInstance();
            if ($app->has(\Air\Routing\Router::class)) {
                $router = $app->make(\Air\Routing\Router::class);
            }
        } catch (\Throwable) {
            $router = null;
        }
        if ($router instanceof \Air\Routing\Router) {
            $router->routes()->addNamed($this);
        }

        return $this;
    }

    /** @param string|array<int, string|callable|object> $middleware */
    public function middleware(string|array $middleware): self
    {
        $items = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_values(array_merge($this->middleware, $items));

        return $this;
    }
}
