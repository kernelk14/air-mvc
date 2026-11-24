<?php

declare(strict_types=1);

namespace Air\Database;

use Air\Config\Repository;
use InvalidArgumentException;
use PDO;

final class Connection
{
    /** @var array<string, PDO> */
    private array $connections = [];

    public function __construct(private Repository $config)
    {
    }

    public function pdo(?string $name = null): PDO
    {
        $name ??= (string) $this->config->get('database.default', 'mysql');
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        $cfg = $this->config->get("database.connections.{$name}");
        if (!is_array($cfg)) {
            throw new InvalidArgumentException("Database connection [{$name}] is not configured.");
        }

        $driver = (string) ($cfg['driver'] ?? 'mysql');
        $dsn = $this->dsn($driver, $cfg);
        $username = isset($cfg['username']) ? (string) $cfg['username'] : null;
        $password = isset($cfg['password']) ? (string) $cfg['password'] : null;
        $options = is_array($cfg['options'] ?? null) ? $cfg['options'] : [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connections[$name] = new PDO($dsn, $username, $password, $options);

        return $this->connections[$name];
    }

    /** @param array<string,mixed> $cfg */
    private function dsn(string $driver, array $cfg): string
    {
        return match ($driver) {
            'sqlite' => 'sqlite:' . (string) ($cfg['database'] ?? ':memory:'),
            'mysql' => sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                (string) ($cfg['host'] ?? '127.0.0.1'),
                (string) ($cfg['database'] ?? ''),
                (string) ($cfg['charset'] ?? 'utf8mb4'),
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;dbname=%s',
                (string) ($cfg['host'] ?? '127.0.0.1'),
                (string) ($cfg['database'] ?? ''),
            ),
            default => throw new InvalidArgumentException("Unsupported database driver [{$driver}]."),
        };
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this->pdo(), $table);
    }
}
