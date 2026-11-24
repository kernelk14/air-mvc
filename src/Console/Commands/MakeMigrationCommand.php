<?php

declare(strict_types=1);

namespace Air\Console\Commands;

use Air\Application;
use Air\Console\Command;

final class MakeMigrationCommand extends Command
{
    public function __construct(private Application $app)
    {
    }

    public function name(): string
    {
        return 'make:migration';
    }

    public function description(): string
    {
        return 'Create a new migration file';
    }

    /** @param array<int,string> $argv */
    public function run(array $argv): int
    {
        $name = $argv[0] ?? null;
        if (!is_string($name) || $name === '') {
            fwrite(STDERR, "Usage: php bin/air make:migration create_users_table\n");
            return 1;
        }

        $name = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        $stamp = date('Y_m_d_His');
        $file = $this->app->basePath('database/migrations/' . $stamp . '_' . $name . '.php');
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $template = <<<'PHP'
<?php

declare(strict_types=1);

use Air\Database\Migrations\Migration;
use Air\Database\Connection;

return new class implements Migration {
    public function up(): void
    {
        /** @var Connection $db */
        $db = app(Connection::class);
        $db->pdo()->exec('/* SQL UP */');
    }

    public function down(): void
    {
        /** @var Connection $db */
        $db = app(Connection::class);
        $db->pdo()->exec('/* SQL DOWN */');
    }
};
PHP;
        file_put_contents($file, $template . "\n");
        echo "Created migration: {$file}\n";
        return 0;
    }
}
