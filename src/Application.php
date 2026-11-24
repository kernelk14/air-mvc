<?php

declare(strict_types=1);

namespace Air;

use Air\Config\Repository;
use Air\Container\Container;

class Application extends Container
{
    private static ?Application $instance = null;

    private string $basePath;

    private bool $booted = false;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        self::$instance = $this;

        $this->instance(self::class, $this);
        $this->instance(Container::class, $this);
        $this->singleton(Repository::class, fn () => new Repository());
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application has not been initialized.');
        }

        return self::$instance;
    }

    public function basePath(string $path = ''): string
    {
        if ($path === '') {
            return $this->basePath;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public function configPath(string $path = ''): string
    {
        return $this->basePath('config' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function appPath(string $path = ''): string
    {
        return $this->basePath('app' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function viewPath(string $path = ''): string
    {
        return $this->appPath('Views' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;
    }

    public function environment(): string
    {
        /** @var Repository $config */
        $config = $this->make(Repository::class);

        return (string) $config->get('app.env', 'production');
    }

    public function isDebug(): bool
    {
        /** @var Repository $config */
        $config = $this->make(Repository::class);

        return (bool) $config->get('app.debug', false);
    }
}
