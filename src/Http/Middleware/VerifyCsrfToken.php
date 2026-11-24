<?php

declare(strict_types=1);

namespace Air\Http\Middleware;

use Air\Http\Request;
use Air\Http\Response;
use Air\Security\Csrf;
use Closure;

final class VerifyCsrfToken implements MiddlewareInterface
{
    /** @var array<int,string> */
    protected array $except = [];

    public function __construct(private Csrf $csrf)
    {
    }

    public function process(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true) || $this->isExcepted($request->path())) {
            return $next($request);
        }

        $token = $request->input('_token');
        if (!is_string($token)) {
            $token = $request->header('X-CSRF-TOKEN');
            $token = is_string($token) ? $token : null;
        }

        if (!$this->csrf->validate($token)) {
            return Response::html('Page Expired', 419);
        }

        return $next($request);
    }

    private function isExcepted(string $path): bool
    {
        foreach ($this->except as $except) {
            if ($except === $path) {
                return true;
            }
        }
        return false;
    }
}
