<?php

declare(strict_types=1);

namespace Air\Database\Migrations;

use Air\Application;
use Air\Database\Connection;
use PDO;

final class Migrator
{
    public function __construct(private Application $app, private Connection $connection)
    {
    }

    /** @return array<int,string> */
    public function migrate(): array
    {
        $this->ensureMigrationsTable();
        $ran = $this->ran();
        $batch = $this->nextBatch();
        $applied = [];

        foreach ($this->migrationFiles() as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran, true)) {
                continue;
            }
            $migration = $this->resolve($file);
            $migration->up();
            $this->record($name, $batch);
            $applied[] = $name;
        }

        return $applied;
    }

    /** @return array<int,string> */
    public function rollback(): array
    {
        $this->ensureMigrationsTable();
        $batch = $this->lastBatch();
        if ($batch === 0) {
            return [];
        }

        $rows = $this->connection->pdo()->prepare('SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC');
        $rows->execute([$batch]);
        $names = $rows->fetchAll(PDO::FETCH_COLUMN);
        $rolled = [];

        foreach ($names as $name) {
            $file = $this->app->basePath('database/migrations/' . $name . '.php');
            if (is_file($file)) {
                $migration = $this->resolve($file);
                $migration->down();
            }
            $stmt = $this->connection->pdo()->prepare('DELETE FROM migrations WHERE migration = ?');
            $stmt->execute([$name]);
            $rolled[] = (string) $name;
        }

        return $rolled;
    }

    /** @return array<int,string> */
    private function migrationFiles(): array
    {
        $dir = $this->app->basePath('database/migrations');
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/*.php') ?: [];
        sort($files);
        return $files;
    }

    /** @return array<int,string> */
    private function ran(): array
    {
        $rows = $this->connection->pdo()->query('SELECT migration FROM migrations ORDER BY id ASC');
        if ($rows === false) {
            return [];
        }
        /** @var array<int,string> $all */
        $all = $rows->fetchAll(PDO::FETCH_COLUMN);
        return array_map('strval', $all);
    }

    private function nextBatch(): int
    {
        return $this->lastBatch() + 1;
    }

    private function lastBatch(): int
    {
        $row = $this->connection->pdo()->query('SELECT MAX(batch) AS batch FROM migrations');
        $value = $row === false ? false : $row->fetch(PDO::FETCH_ASSOC);
        return is_array($value) ? (int) ($value['batch'] ?? 0) : 0;
    }

    private function record(string $name, int $batch): void
    {
        $stmt = $this->connection->pdo()->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
        $stmt->execute([$name, $batch]);
    }

    private function ensureMigrationsTable(): void
    {
        $this->connection->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration VARCHAR(255) NOT NULL, batch INTEGER NOT NULL)',
        );
    }

    private function resolve(string $file): Migration
    {
        $resolved = require $file;
        if ($resolved instanceof Migration) {
            return $resolved;
        }
        if (is_object($resolved) && method_exists($resolved, 'up') && method_exists($resolved, 'down')) {
            /** @var Migration $resolved */
            return $resolved;
        }

        throw new \RuntimeException('Migration must return an object implementing Air\\Database\\Migrations\\Migration: ' . $file);
    }
}
