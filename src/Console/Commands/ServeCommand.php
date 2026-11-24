<?php

declare(strict_types=1);

namespace Air\Console\Commands;

use Air\Application;
use Air\Console\Command;

final class ServeCommand extends Command
{
    public function __construct(private Application $app)
    {
    }

    public function name(): string
    {
        return 'serve';
    }

    public function description(): string
    {
        return 'Serve the application using the PHP built-in server';
    }

    /** @param array<int,string> $argv */
    public function run(array $argv): int
    {
        $options = $this->parseOptions($argv);
        $host = (string) ($options['host'] ?? '127.0.0.1');
        $port = (string) ($options['port'] ?? '8000');
        $base = $this->app->basePath();

        echo "Air MVC development server started: http://{$host}:{$port}\n";
        $descriptors = [0 => STDIN, 1 => STDOUT, 2 => STDERR];
        $process = proc_open(['php', '-S', $host . ':' . $port, 'index.php'], $descriptors, $pipes, $base);
        if (!is_resource($process)) {
            fwrite(STDERR, "Failed to start server.\n");
            return 1;
        }

        return proc_close($process) === 0 ? 0 : 1;
    }
}
