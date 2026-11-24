<?php

declare(strict_types=1);

namespace Air\Auth;

use Air\Session\SessionManager;

final class AuthManager
{
    public function __construct(private SessionManager $session)
    {
    }

    public function check(): bool
    {
        return $this->session->get('auth_user_id') !== null;
    }

    public function id(): mixed
    {
        return $this->session->get('auth_user_id');
    }

    public function login(mixed $userId): void
    {
        $this->session->put('auth_user_id', $userId);
    }

    public function logout(): void
    {
        $this->session->forget('auth_user_id');
    }
}
