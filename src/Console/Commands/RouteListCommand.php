<?php

declare(strict_types=1);

namespace Air\Console\Commands;

use Air\Application;
use Air\Console\Command;
use Air\Routing\Router;

final class RouteListCommand extends Command
{
    public function __construct(private Application $app)
    {
    }

    public function name(): string
    {
        return 'route:list';
    }

    public function description(): string
    {
        return 'List registered routes';
    }

    /** @param array<int,string> $argv */
    public function run(array $argv): int
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $rows = [];
        foreach ($router->routes()->all() as $route) {
            $action = $route->action;
            if (is_array($action)) {
                $class = is_object($action[0]) ? get_class($action[0]) : (string) $action[0];
                $actionStr = $class . '@' . (string) $action[1];
            } elseif (is_string($action)) {
                $actionStr = $action;
            } elseif ($action instanceof \Closure) {
                $actionStr = 'Closure';
            } else {
                $actionStr = 'callable';
            }
            $rows[] = [implode('|', $route->methods), $route->uri, $route->name ?? '', $actionStr];
        }

        $widths = [6, 4, 4, 6];
        foreach ($rows as $row) {
            foreach ($row as $i => $col) {
                $widths[$i] = max($widths[$i], strlen($col));
            }
        }
        $fmt = sprintf("%%-%ds  %%-%ds  %%-%ds  %%-%ds\n", $widths[0], $widths[1], $widths[2], $widths[3]);
        printf($fmt, 'METHOD', 'URI', 'NAME', 'ACTION');
        printf($fmt, str_repeat('-', $widths[0]), str_repeat('-', $widths[1]), str_repeat('-', $widths[2]), str_repeat('-', $widths[3]));
        foreach ($rows as $row) {
            printf($fmt, $row[0], $row[1], $row[2], $row[3]);
        }

        return 0;
    }
}
