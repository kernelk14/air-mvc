<?php

declare(strict_types=1);

namespace Air\Http\Middleware;

use Air\Auth\AuthManager;
use Air\Http\Request;
use Air\Http\Response;
use Closure;

final class Authenticate implements MiddlewareInterface
{
    public function __construct(private AuthManager $auth)
    {
    }

    public function process(Request $request, Closure $next): Response
    {
        if (!$this->auth->check()) {
            return Response::redirect('/login');
        }
        return $next($request);
    }
}
