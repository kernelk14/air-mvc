<?php

declare(strict_types=1);

namespace Air\Console;

abstract class Command
{
    abstract public function name(): string;

    abstract public function description(): string;

    /** @param array<int,string> $argv */
    abstract public function run(array $argv): int;

    /**
     * @param array<int,string> $argv
     * @return array<string,string|bool>
     */
    protected function parseOptions(array $argv): array
    {
        $options = [];
        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $part = substr($arg, 2);
                if (str_contains($part, '=')) {
                    [$k, $v] = explode('=', $part, 2);
                    $options[$k] = $v;
                } else {
                    $options[$part] = true;
                }
            }
        }
        return $options;
    }
}
