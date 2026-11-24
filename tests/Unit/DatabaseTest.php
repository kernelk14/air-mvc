<?php

declare(strict_types=1);

namespace Tests\Unit;

use Air\Database\Connection;
use Air\Database\Migrations\Migrator;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    private function sqliteApp(): \Air\Application
    {
        $base = dirname(__DIR__, 2);
        $app = new \Air\Application($base);
        $config = $app->make(\Air\Config\Repository::class);
        $config->merge([
            'app' => ['env' => 'testing', 'debug' => true, 'timezone' => 'UTC'],
            'database' => [
                'default' => 'sqlite',
                'connections' => [
                    'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'options' => [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]],
                ],
            ],
        ]);
        $app->singleton(\Air\Config\Repository::class, fn () => $config);
        $app->singleton(Connection::class, fn (\Air\Application $app) => new Connection($app->make(\Air\Config\Repository::class)));
        $app->singleton(Migrator::class, fn (\Air\Application $app) => new Migrator($app, $app->make(Connection::class)));
        return $app;
    }

    public function testQueryBuilderInsertAndSelect(): void
    {
        $app = $this->sqliteApp();
        /** @var Connection $db */
        $db = $app->make(Connection::class);
        $db->pdo()->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255), email VARCHAR(255))');
        $db->table('users')->insert(['name' => 'A', 'email' => 'a@x.com']);
        $rows = $db->table('users')->where('name', '=', 'A')->get();
        $this->assertCount(1, $rows);
        $this->assertSame('a@x.com', $rows[0]['email']);
    }
}
