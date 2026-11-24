<?php

declare(strict_types=1);

namespace Air\Container;

use ReflectionClass;
use ReflectionNamedType;

class Container implements ContainerInterface
{
    /** @var array<string, callable|object|string> */
    private array $bindings = [];

    /** @var array<string, bool> */
    private array $singletons = [];

    /** @var array<string, object> */
    private array $instances = [];

    public function bind(string $abstract, callable|object|string|null $concrete = null, bool $singleton = false): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
        $this->singletons[$abstract] = $singleton;
        unset($this->instances[$abstract]);
    }

    public function singleton(string $abstract, callable|object|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
        $this->singletons[$abstract] = true;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || class_exists($id);
    }

    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    /** @param array<string, mixed> $parameters */
    public function make(string $abstract, array $parameters = []): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;
        $object = $this->build($concrete, $parameters);

        if (($this->singletons[$abstract] ?? false) === true) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /** @param array<string, mixed> $parameters */
    private function build(mixed $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        if (is_object($concrete)) {
            return $concrete;
        }

        if (!is_string($concrete) || !class_exists($concrete)) {
            throw new NotFoundException("Unable to resolve [{$concrete}].");
        }

        $reflection = new ReflectionClass($concrete);
        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Class [{$concrete}] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $depClass = $type->getName();
                if ($this->has($depClass)) {
                    $dependencies[] = $this->make($depClass);
                    continue;
                }
            }

            if ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
                continue;
            }

            if ($param->allowsNull()) {
                $dependencies[] = null;
                continue;
            }

            throw new ContainerException("Unable to resolve parameter [{$name}] for [{$concrete}].");
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
