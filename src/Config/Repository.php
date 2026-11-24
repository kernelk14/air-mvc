<?php

declare(strict_types=1);

namespace Air\Config;

final class Repository
{
    /** @param array<string, mixed> $items */
    public function __construct(private array $items = [])
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->items;
    }

    public function has(string $key): bool
    {
        return $this->get($key, '__missing__') !== '__missing__';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $ref = &$this->items;
        foreach ($segments as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
        $ref = $value;
    }

    /** @param array<string, mixed> $items */
    public function merge(array $items): void
    {
        $this->items = array_replace_recursive($this->items, $items);
    }
}
