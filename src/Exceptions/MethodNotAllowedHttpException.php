<?php

declare(strict_types=1);

namespace Air\Exceptions;

class MethodNotAllowedHttpException extends HttpException
{
    /** @param string[] $allowed */
    public function __construct(array $allowed = [], string $message = 'Method Not Allowed', ?\Throwable $previous = null)
    {
        $headers = [];
        if ($allowed !== []) {
            $headers['Allow'] = implode(', ', array_map('strtoupper', $allowed));
        }
        parent::__construct(405, $message, $headers, $previous);
    }
}
