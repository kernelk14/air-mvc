<?php

declare(strict_types=1);

namespace Air\Exceptions;

class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Not Found', ?\Throwable $previous = null)
    {
        parent::__construct(404, $message, [], $previous);
    }
}
