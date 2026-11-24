<?php

declare(strict_types=1);

namespace Air\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    /** @param array<string,string> $headers */
    public function __construct(
        private int $statusCode = 500,
        string $message = '',
        private array $headers = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string,string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
