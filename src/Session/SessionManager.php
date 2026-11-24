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
            $this->ageFlash();
            return;
        }
        if (!headers_sent() && $status !== PHP_SESSION_DISABLED) {
            @session_start();
        }
        $this->started = session_status() === PHP_SESSION_ACTIVE;
        if ($this->started) {
            $this->ageFlash();
        }
    }

    private function ageFlash(): void
    {
        $_SESSION['_flash_old'] = $_SESSION['_flash_new'] ?? [];
        $_SESSION['_flash_new'] = [];
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

    /** @param array<string,mixed> $data */
    public function flashInput(array $data): void
    {
        $this->flash('_old_input', $data);
    }

    /** @return array<string,mixed> */
    public function oldInput(): array
    {
        $value = $this->getFlash('_old_input', []);
        return is_array($value) ? $value : [];
    }

    public function flash(string $key, mixed $value): void
    {
        $this->start();
        $_SESSION['_flash_new'][$key] = $value;
        $_SESSION[$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION['_flash_old'][$key] ?? $default;
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
