<?php

declare(strict_types=1);

namespace Air\Log;

final class Logger
{
    public function __construct(private string $path)
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    /** @param array<string,mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
    /** @param array<string,mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }
    /** @param array<string,mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /** @param array<string,mixed> $context */
    public function log(string $level, string $message, array $context = []): void
    {
        $line = sprintf('[%s] %s: %s %s', date('Y-m-d H:i:s'), strtoupper($level), $message, $context === [] ? '' : json_encode($context, JSON_UNESCAPED_SLASHES));
        file_put_contents($this->path, rtrim($line) . PHP_EOL, FILE_APPEND);
    }
}
