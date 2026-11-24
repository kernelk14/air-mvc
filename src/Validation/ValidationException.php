<?php

declare(strict_types=1);

namespace Air\Validation;

use Air\Exceptions\HttpException;

final class ValidationException extends HttpException
{
    /** @param array<string,array<int,string>> $errors */
    public function __construct(private array $errors)
    {
        parent::__construct(422, 'Validation failed');
    }

    /** @return array<string,array<int,string>> */
    public function errors(): array
    {
        return $this->errors;
    }
}
