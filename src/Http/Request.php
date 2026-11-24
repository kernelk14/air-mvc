<?php

declare(strict_types=1);

namespace Air\Http;

final class Request
{
    /**
     * @param array<string,mixed> $query
     * @param array<string,mixed> $request
     * @param array<string,mixed> $cookies
     * @param array<string,mixed> $files
     * @param array<string,mixed> $server
     * @param array<string,mixed> $json
     */
    public function __construct(
        private array $query = [],
        private array $request = [],
        private array $cookies = [],
        private array $files = [],
        private array $server = [],
        private array $json = [],
        private string $content = '',
    ) {
    }

    public static function capture(): self
    {
        $content = (string) file_get_contents('php://input');
        $json = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (is_string($contentType) && str_contains($contentType, 'application/json') && $content !== '') {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $json = $decoded;
            }
        }

        return new self($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, $json, $content);
    }

    public function method(): string
    {
        $method = strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
        if ($method === 'POST') {
            $override = $this->request['_method'] ?? $this->header('X-HTTP-Method-Override');
            if (is_string($override) && $override !== '') {
                $method = strtoupper($override);
            }
        }

        return $method;
    }

    public function path(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : '/';

        $scriptName = str_replace('\\', '/', (string) ($this->server['SCRIPT_NAME'] ?? ''));
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseDir !== '' && $baseDir !== '/' && str_starts_with($path, $baseDir)) {
            $path = substr($path, strlen($baseDir)) ?: '/';
        }

        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $this->query[$key] ?? $this->json[$key] ?? $default;
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->request, $this->json);
    }

    /** @return array<string,mixed> */
    public function query(): array
    {
        return $this->query;
    }

    /** @return array<string,mixed> */
    public function post(): array
    {
        return $this->request;
    }

    /** @return array<string,mixed> */
    public function json(): array
    {
        return $this->json;
    }

    /** @return array<string,mixed> */
    public function files(): array
    {
        return $this->files;
    }

    /** @return array<string,mixed> */
    public function server(): array
    {
        return $this->server;
    }

    /** @return array<string,mixed> */
    public function cookies(): array
    {
        return $this->cookies;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (array_key_exists($key, $this->server)) {
            return $this->server[$key];
        }

        $normalized = strtolower($name);
        foreach ($this->server as $k => $v) {
            if (strtolower((string) $k) === $normalized) {
                return $v;
            }
        }

        return $default;
    }

    public function expectsJson(): bool
    {
        $accept = (string) $this->header('Accept', '');

        return str_contains($accept, 'application/json') || str_contains($accept, '+json');
    }

    public function isJson(): bool
    {
        $contentType = (string) ($this->server['CONTENT_TYPE'] ?? $this->server['HTTP_CONTENT_TYPE'] ?? '');

        return str_contains($contentType, 'application/json');
    }

    public function content(): string
    {
        return $this->content;
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }
}
