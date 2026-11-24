<?php

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false) {
            return $default;
        }

        if (!is_string($value)) {
            return $value;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        /** @var \Air\Application $app */
        $app = \Air\Application::getInstance();

        return $app->basePath($path);
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        /** @var \Air\Application $app */
        $app = \Air\Application::getInstance();
        /** @var \Air\Config\Repository $repo */
        $repo = $app->make(\Air\Config\Repository::class);

        if ($key === null) {
            return $repo;
        }

        return $repo->get($key, $default);
    }
}

if (!function_exists('app')) {
    /** @param array<string, mixed> $parameters */
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        $instance = \Air\Application::getInstance();
        if ($abstract === null) {
            return $instance;
        }

        return $instance->make($abstract, $parameters);
    }
}

if (!function_exists('view')) {
    /** @param array<string, mixed> $data */
    function view(string $view, array $data = []): \Air\Http\Response
    {
        /** @var \Air\View\ViewFactory $factory */
        $factory = app(\Air\View\ViewFactory::class);

        return $factory->make($view, $data);
    }
}

if (!function_exists('response')) {
    /** @param array<string, string> $headers */
    function response(string $content = '', int $status = 200, array $headers = []): \Air\Http\Response
    {
        return new \Air\Http\Response($content, $status, $headers);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $to, int $status = 302): \Air\Http\Response
    {
        return \Air\Http\Response::redirect($to, $status);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $base = rtrim((string) config('app.url', ''), '/');
        $path = '/' . ltrim($path, '/');
        $assetPath = '/public/assets' . $path;

        if ($base !== '') {
            return $base . $assetPath;
        }

        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $script = is_string($script) ? $script : '';
        $dir = str_replace('\\', '/', dirname($script));
        $prefix = ($dir === '/' || $dir === '.') ? '' : $dir;

        return $prefix . $assetPath;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        /** @var \Air\Security\Csrf $csrf */
        $csrf = app(\Air\Security\Csrf::class);
        return $csrf->token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('route')) {
    /** @param array<string,string|int> $params */
    function route(string $name, array $params = []): string
    {
        /** @var \Air\Routing\Router $router */
        $router = app(\Air\Routing\Router::class);
        return $router->route($name, $params);
    }
}
