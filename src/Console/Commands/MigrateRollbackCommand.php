<?php

declare(strict_types=1);

namespace Air\Console\Commands;

use Air\Application;
use Air\Console\Command;
use Air\Database\Migrations\Migrator;

final class MigrateRollbackCommand extends Command
{
    public function __construct(private Application $app)
    {
    }

    public function name(): string
    {
        return 'migrate:rollback';
    }

    public function description(): string
    {
        return 'Rollback the last migration batch';
    }

    /** @param array<int,string> $argv */
    public function run(array $argv): int
    {
        /** @var Migrator $migrator */
        $migrator = $this->app->make(Migrator::class);
        $rolled = $migrator->rollback();
        if ($rolled === []) {
            echo "Nothing to rollback.\n";
            return 0;
        }
        foreach ($rolled as $name) {
            echo "Rolled back: {$name}\n";
        }
        return 0;
    }
}
