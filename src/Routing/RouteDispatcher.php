<?php

declare(strict_types=1);

namespace Air\Routing;

use Air\Application;
use Air\Exceptions\MethodNotAllowedHttpException;
use Air\Exceptions\NotFoundHttpException;
use Air\Http\Request;
use Air\Http\Response;
use Closure;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

final class RouteDispatcher
{
    public function __construct(private Application $app)
    {
    }

    public function dispatch(Request $request): Response
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $path = $request->path();
        $method = $request->method();

        foreach ($router->routes()->forMethod($method) as $route) {
            $params = Router::matchPath($route->uri, $path);
            if ($params === false) {
                continue;
            }
            $route->parameters = $params;

            return $this->runRoute($request, $route);
        }

        $allowed = $router->routes()->allowedMethodsForPath($path);
        if ($allowed !== []) {
            throw new MethodNotAllowedHttpException($allowed);
        }

        throw new NotFoundHttpException('Not Found');
    }

    private function runRoute(Request $request, Route $route): Response
    {
        $handler = function (Request $request) use ($route): Response {
            $result = $this->callAction($request, $route);

            return $this->toResponse($result);
        };

        $middleware = array_map(function (mixed $mw): mixed {
            if (is_string($mw) && $this->app->has(\Air\Http\Kernel::class)) {
                /** @var \Air\Http\Kernel $kernel */
                $kernel = $this->app->make(\Air\Http\Kernel::class);
                return $kernel->resolveMiddleware($mw);
            }
            return $mw;
        }, $route->middleware);
        $pipeline = array_reduce(
            array_reverse($middleware),
            function (Closure $next, mixed $mw): Closure {
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
            $handler,
        );

        $response = $pipeline($request);
        return $response;
    }

    private function callAction(Request $request, Route $route): mixed
    {
        $action = $route->action;
        $params = $route->parameters;

        if (is_string($action) && str_contains($action, '::')) {
            [$class, $method] = explode('::', $action, 2);
            $action = [$class, $method];
        }

        if (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            $controller = is_object($class) ? $class : $this->app->make((string) $class);
            $arguments = $this->resolveMethodArguments(new ReflectionMethod($controller, (string) $method), $request, $params);

            return $controller->{$method}(...$arguments);
        }

        if ($action instanceof Closure || is_callable($action)) {
            $closure = $action instanceof Closure ? $action : Closure::fromCallable($action);
            $ref = new ReflectionFunction($closure);
            $arguments = $this->resolveFunctionArguments($ref, $request, $params);

            return $closure(...$arguments);
        }

        throw new \RuntimeException('Invalid route action.');
    }

    /**
     * @param array<string,string> $params
     * @return array<int, mixed>
     */
    private function resolveMethodArguments(ReflectionMethod $method, Request $request, array $params): array
    {
        return $this->resolveArguments($method->getParameters(), $request, $params);
    }

    /**
     * @param array<string,string> $params
     * @return array<int, mixed>
     */
    private function resolveFunctionArguments(ReflectionFunction $function, Request $request, array $params): array
    {
        return $this->resolveArguments($function->getParameters(), $request, $params);
    }

    /**
     * @param ReflectionParameter[] $parameters
     * @param array<string,string> $routeParams
     * @return array<int,mixed>
     */
    private function resolveArguments(array $parameters, Request $request, array $routeParams): array
    {
        $args = [];
        $usedRequest = false;
        foreach ($parameters as $param) {
            $type = $param->getType();
            $name = $param->getName();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $typeName = $type->getName();
                if ($typeName === Request::class || is_a($typeName, Request::class, true)) {
                    if ($typeName !== Request::class && is_a($typeName, \Air\Http\FormRequest::class, true)) {
                        /** @var \Air\Http\FormRequest $form */
                        $form = new $typeName($request->query(), $request->post(), $request->cookies(), $request->files(), $request->server(), $request->json(), $request->content());
                        $args[] = $form;
                    } else {
                        $args[] = $request;
                    }
                    $usedRequest = true;
                    continue;
                }
                if ($this->app->has($typeName)) {
                    $args[] = $this->app->make($typeName);
                    continue;
                }
            }

            if (array_key_exists($name, $routeParams)) {
                $args[] = $routeParams[$name];
                continue;
            }

            if (!$usedRequest && $type === null && count($args) === 0) {
                $args[] = $request;
                $usedRequest = true;
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            if ($param->allowsNull()) {
                $args[] = null;
                continue;
            }

            throw new \RuntimeException('Unable to resolve route parameter [' . $name . '].');
        }

        return $args;
    }

    private function toResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        if (is_string($result) || is_numeric($result) || $result === null) {
            return Response::html((string) $result);
        }

        throw new \RuntimeException('Invalid route response type.');
    }
}
