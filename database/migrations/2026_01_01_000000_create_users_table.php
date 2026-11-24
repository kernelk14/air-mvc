<?php

declare(strict_types=1);

use Air\Database\Migrations\Migration;
use Air\Database\Connection;

return new class implements Migration {
    public function up(): void
    {
        /** @var Connection $db */
        $db = app(Connection::class);
        $db->pdo()->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL)');
    }

    public function down(): void
    {
        /** @var Connection $db */
        $db = app(Connection::class);
        $db->pdo()->exec('DROP TABLE IF EXISTS users');
    }
};