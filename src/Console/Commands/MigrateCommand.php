<?php

declare(strict_types=1);

namespace Air\Console\Commands;

use Air\Application;
use Air\Console\Command;
use Air\Database\Migrations\Migrator;

final class MigrateCommand extends Command
{
    public function __construct(private Application $app)
    {
    }

    public function name(): string
    {
        return 'migrate';
    }

    public function description(): string
    {
        return 'Run database migrations';
    }

    /** @param array<int,string> $argv */
    public function run(array $argv): int
    {
        /** @var Migrator $migrator */
        $migrator = $this->app->make(Migrator::class);
        $applied = $migrator->migrate();
        if ($applied === []) {
            echo "Nothing to migrate.\n";
            return 0;
        }
        foreach ($applied as $name) {
            echo "Migrated: {$name}\n";
        }
        return 0;
    }
}
