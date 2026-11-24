<?php

declare(strict_types=1);

namespace Air\Session;

final class SessionManager
{
    private bool $started = false;

    public function start(): void
    {
        if ($this->started) {
            return;
        }
        $status = session_status();
        if ($status === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }
        if (!headers_sent() && $status !== PHP_SESSION_DISABLED) {
            @session_start();
        }
        $this->started = session_status() === PHP_SESSION_ACTIVE;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->start();
        return array_key_exists($key, $_SESSION);
    }

    public function forget(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        $this->start();
        /** @var array<string,mixed> $session */
        $session = $_SESSION;
        return $session;
    }
}
