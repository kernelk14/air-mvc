<?php

declare(strict_types=1);

namespace Air\Container;

interface ContainerInterface
{
    public function has(string $id): bool;

    public function get(string $id): mixed;

    /** @param array<string, mixed> $parameters */
    public function make(string $abstract, array $parameters = []): mixed;
}
