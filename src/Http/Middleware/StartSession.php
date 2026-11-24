<?php

declare(strict_types=1);

namespace Air\Http\Middleware;

use Air\Http\Request;
use Air\Http\Response;
use Air\Session\SessionManager;
use Closure;

final class StartSession implements MiddlewareInterface
{
    public function __construct(private SessionManager $session)
    {
    }

    public function process(Request $request, Closure $next): Response
    {
        $this->session->start();
        return $next($request);
    }
}
