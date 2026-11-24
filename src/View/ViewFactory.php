<?php

declare(strict_types=1);

namespace Air\View;

use Air\Application;
use Air\Http\Response;
use RuntimeException;

final class ViewFactory
{
    public function __construct(private Application $app)
    {
    }

    /** @param array<string,mixed> $data */
    public function make(string $view, array $data = []): Response
    {
        $path = $this->resolvePath($view);
        if (!is_file($path)) {
            throw new RuntimeException("View [{$view}] not found at [{$path}].");
        }

        $render = static function (string $__path, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            try {
                require $__path;
            } finally {
                $content = ob_get_clean();
            }

            return $content === false ? '' : $content;
        };

        $content = $render($path, $data);

        return Response::html($content);
    }

    private function resolvePath(string $view): string
    {
        $view = str_replace(['.', '\\'], '/', $view);
        $view = trim($view, '/');

        return $this->app->viewPath($view . '.php');
    }
}
