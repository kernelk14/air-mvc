<?php

declare(strict_types=1);

namespace Air\Console;

use Air\Application;
use Air\Console\Commands\MakeMigrationCommand;
use Air\Console\Commands\MigrateCommand;
use Air\Console\Commands\MigrateRollbackCommand;
use Air\Console\Commands\RouteListCommand;
use Air\Console\Commands\ServeCommand;

final class Kernel
{
    /** @var array<int, Command> */
    private array $commands = [];

    public function __construct(private Application $app)
    {
        $this->commands = [
            new ServeCommand($this->app),
            new RouteListCommand($this->app),
            new MakeMigrationCommand($this->app),
            new MigrateCommand($this->app),
            new MigrateRollbackCommand($this->app),
        ];
    }

    /** @param array<int, string>|null $argv */
    public function run(?array $argv = null): int
    {
        $argv = $argv ?? (isset($_SERVER['argv']) && is_array($_SERVER['argv']) ? array_values(array_map('strval', $_SERVER['argv'])) : ['air']);
        $script = array_shift($argv);
        $script = is_string($script) ? $script : 'air';
        $name = $argv[0] ?? null;

        if ($name === null || $name === 'list' || $name === '--help' || $name === '-h') {
            $this->printHelp($script);
            return 0;
        }

        foreach ($this->commands as $command) {
            if ($command->name() === $name) {
                array_shift($argv);
                /** @var array<int, string> $args */
                $args = array_values(array_map('strval', $argv));
                return $command->run($args);
            }
        }

        fwrite(STDERR, "Unknown command: {$name}\n\n");
        $this->printHelp($script);
        return 1;
    }

    private function printHelp(string $script): void
    {
        echo "Air MVC CLI\n\n";
        echo "Usage:\n  php {$script} <command> [options]\n\n";
        echo "Commands:\n";
        foreach ($this->commands as $command) {
            printf("  %-20s %s\n", $command->name(), $command->description());
        }
        echo "  list                 List commands\n";
    }
}
