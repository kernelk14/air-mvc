<?php

declare(strict_types=1);

namespace Air\Security;

use Air\Session\SessionManager;

final class Csrf
{
    public const TOKEN_KEY = '_token';

    public function __construct(private SessionManager $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get(self::TOKEN_KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->put(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    public function validate(?string $token): bool
    {
        $sessionToken = $this->session->get(self::TOKEN_KEY);
        return is_string($token) && is_string($sessionToken) && $sessionToken !== '' && hash_equals($sessionToken, $token);
    }
}
