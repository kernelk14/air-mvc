<?php

declare(strict_types=1);

namespace Air\Database;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    /** @var array<int,string> */
    protected array $fillable = [];

    /** @var array<string,mixed> */
    protected array $attributes = [];

    /** @param array<string,mixed> $attributes */
    public function __construct(array $attributes = [], protected ?Connection $connection = null)
    {
        $this->fill($attributes);
        $this->connection ??= app(Connection::class);
    }

    /** @param array<string,mixed> $attributes */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->fillable === [] || in_array($key, $this->fillable, true) || $key === $this->primaryKey) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->attributes;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getTable(): string
    {
        if ($this->table !== '') {
            return $this->table;
        }
        $class = (new \ReflectionClass($this))->getShortName();
        $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $class));
        return str_ends_with($snake, 's') ? $snake : $snake . 's';
    }

    public static function query(): QueryBuilder
    {
        $instance = static::newInstance();
        return $instance->connection()->table($instance->getTable());
    }

    /** @return array<int,static> */
    public static function all(): array
    {
        $instance = static::newInstance();
        $rows = $instance->connection()->table($instance->getTable())->get();
        return array_map(fn (array $row) => static::newInstance($row, $instance->connection()), $rows);
    }

    public static function find(mixed $id): ?static
    {
        $instance = static::newInstance();
        $row = $instance->connection()->table($instance->getTable())->where($instance->primaryKey, '=', $id)->first();
        return $row === null ? null : static::newInstance($row, $instance->connection());
    }

    /** @param array<string,mixed> $attributes */
    public static function create(array $attributes): static
    {
        $instance = static::newInstance($attributes);
        $data = $instance->persistableAttributes();
        $instance->connection()->table($instance->getTable())->insert($data);
        $pdo = $instance->connection()->pdo();
        $id = $pdo->lastInsertId();
        if ($id !== false && $id !== '' && $id !== '0') {
            $instance->attributes[$instance->primaryKey] = ctype_digit($id) ? (int) $id : $id;
        } elseif (array_key_exists($instance->primaryKey, $data)) {
            $instance->attributes[$instance->primaryKey] = $data[$instance->primaryKey];
        }
        return $instance;
    }

    public function save(): bool
    {
        $key = $this->attributes[$this->primaryKey] ?? null;
        $data = $this->persistableAttributes();
        unset($data[$this->primaryKey]);
        if ($key === null) {
            static::create($this->attributes);
            return true;
        }
        $this->connection()->table($this->getTable())->where($this->primaryKey, '=', $key)->update($data);
        return true;
    }

    public function delete(): bool
    {
        $key = $this->attributes[$this->primaryKey] ?? null;
        if ($key === null) {
            return false;
        }
        $this->connection()->table($this->getTable())->where($this->primaryKey, '=', $key)->delete();
        return true;
    }

    /** @param array<string,mixed> $attributes */
    protected static function newInstance(array $attributes = [], ?Connection $connection = null): static
    {
        /** @var static $model */
        $model = (new \ReflectionClass(static::class))->newInstance($attributes, $connection);
        return $model;
    }

    /** @return array<string,mixed> */
    protected function persistableAttributes(): array
    {
        if ($this->fillable === []) {
            return $this->attributes;
        }
        $data = [];
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $this->attributes)) {
                $data[$field] = $this->attributes[$field];
            }
        }
        if (array_key_exists($this->primaryKey, $this->attributes)) {
            $data[$this->primaryKey] = $this->attributes[$this->primaryKey];
        }
        return $data;
    }

    protected function connection(): Connection
    {
        $this->connection ??= app(Connection::class);
        return $this->connection;
    }
}
