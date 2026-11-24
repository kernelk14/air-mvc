<?php

declare(strict_types=1);

namespace Air\Auth;

use Air\Database\Connection;
use Air\Session\SessionManager;

final class AuthManager
{
    public function __construct(private SessionManager $session, private ?Connection $db = null)
    {
    }

    public function attempt(string $email, string $password, string $table = 'users'): bool
    {
        if ($this->db === null) {
            return false;
        }
        $user = $this->db->table($table)->where('email', '=', $email)->first();
        if ($user === null) {
            return false;
        }
        $hash = (string) ($user['password'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return false;
        }
        $this->login($user['id'] ?? null);
        return true;
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
