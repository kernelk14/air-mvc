<?php

declare(strict_types=1);

namespace Air\Http\Middleware;

use Air\Http\Request;
use Air\Http\Response;
use Closure;

interface MiddlewareInterface
{
    /** @param Closure(Request): Response $next */
    public function process(Request $request, Closure $next): Response;
}
