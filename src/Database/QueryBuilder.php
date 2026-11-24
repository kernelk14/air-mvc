<?php

declare(strict_types=1);

namespace Air\Database;

use InvalidArgumentException;
use PDO;

final class QueryBuilder
{
    /** @var array<int,array{type:string,column:string,operator:string,value:mixed,boolean:string}> */
    private array $wheres = [];

    /** @var array<int,string> */
    private array $orderBys = [];

    private ?int $limit = null;
    private ?int $offset = null;

    /** @var array<int,string>|string */
    private array|string $columns = ['*'];

    public function __construct(private PDO $pdo, private string $table)
    {
        $this->table = $this->assertIdentifier($table);
    }

    /** @param array<int,string>|string $columns */
    public function select(array|string $columns = ['*']): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function where(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): self
    {
        if ($value === null && $operator !== null && !in_array($operator, ['=', '!=', '<>', '>', '<', '>=', '<=', 'like', 'LIKE'], true)) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $this->assertIdentifier($column),
            'operator' => $this->assertOperator((string) $operator),
            'value' => $value,
            'boolean' => strtoupper($boolean) === 'OR' ? 'OR' : 'AND',
        ];

        return $this;
    }

    /** @param array<int,mixed> $values */
    public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self
    {
        $this->wheres[] = [
            'type' => $not ? 'not_in' : 'in',
            'column' => $this->assertIdentifier($column),
            'operator' => $not ? 'NOT IN' : 'IN',
            'value' => array_values($values),
            'boolean' => strtoupper($boolean) === 'OR' ? 'OR' : 'AND',
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $column = $this->assertIdentifier($column);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBys[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = max(0, $limit);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    /** @return array<int,array<string,mixed>> */
    public function get(): array
    {
        $stmt = $this->runSelect();
        /** @var array<int,array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /** @return array<string,mixed>|null */
    public function first(): ?array
    {
        $this->limit(1);
        $stmt = $this->runSelect();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function count(): int
    {
        $prevColumns = $this->columns;
        $this->columns = ['COUNT(*) AS aggregate'];
        $stmt = $this->runSelect();
        $this->columns = $prevColumns;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? (int) ($row['aggregate'] ?? 0) : 0;
    }

    /** @param array<string,mixed> $data */
    public function insert(array $data): bool
    {
        if ($data === []) {
            throw new InvalidArgumentException('Insert data cannot be empty.');
        }
        $columns = [];
        $placeholders = [];
        $bindings = [];
        foreach ($data as $column => $value) {
            $columns[] = $this->quoteIdentifier($this->assertIdentifier($column));
            $placeholders[] = '?';
            $bindings[] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quoteIdentifier($this->table),
            implode(', ', $columns),
            implode(', ', $placeholders),
        );

        return $this->statement($sql, $bindings)->execute();
    }

    /** @param array<string,mixed> $data */
    public function update(array $data): int
    {
        if ($data === []) {
            throw new InvalidArgumentException('Update data cannot be empty.');
        }
        $sets = [];
        $bindings = [];
        foreach ($data as $column => $value) {
            $sets[] = $this->quoteIdentifier($this->assertIdentifier($column)) . ' = ?';
            $bindings[] = $value;
        }

        [$whereSql, $whereBindings] = $this->compileWheres();
        $sql = sprintf('UPDATE %s SET %s%s', $this->quoteIdentifier($this->table), implode(', ', $sets), $whereSql);
        $stmt = $this->statement($sql, array_merge($bindings, $whereBindings));
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(): int
    {
        [$whereSql, $whereBindings] = $this->compileWheres();
        $sql = sprintf('DELETE FROM %s%s', $this->quoteIdentifier($this->table), $whereSql);
        $stmt = $this->statement($sql, $whereBindings);
        $stmt->execute();

        return $stmt->rowCount();
    }

    private function runSelect(): \PDOStatement
    {
        [$whereSql, $bindings] = $this->compileWheres();
        $sql = sprintf(
            'SELECT %s FROM %s%s%s%s%s',
            $this->compileColumns(),
            $this->quoteIdentifier($this->table),
            $whereSql,
            $this->orderBys !== [] ? ' ORDER BY ' . implode(', ', $this->orderBys) : '',
            $this->limit !== null ? ' LIMIT ' . $this->limit : '',
            $this->offset !== null ? ' OFFSET ' . $this->offset : '',
        );

        $stmt = $this->statement($sql, $bindings);
        $stmt->execute();
        return $stmt;
    }

    private function compileColumns(): string
    {
        $columns = is_array($this->columns) ? $this->columns : [$this->columns];
        $compiled = [];
        foreach ($columns as $column) {
            if ($column === '*') {
                $compiled[] = '*';
                continue;
            }
            if (preg_match('/^COUNT\(\*\)\s+AS\s+([a-zA-Z_][a-zA-Z0-9_]*)$/i', $column, $m) === 1) {
                $compiled[] = 'COUNT(*) AS ' . $this->quoteIdentifier($m[1]);
                continue;
            }
            $compiled[] = $this->quoteIdentifier($this->assertIdentifier($column));
        }

        return implode(', ', $compiled);
    }

    /** @return array{0:string,1:array<int,mixed>} */
    private function compileWheres(): array
    {
        if ($this->wheres === []) {
            return ['', []];
        }

        $parts = [];
        $bindings = [];
        foreach ($this->wheres as $i => $where) {
            $bool = $i === 0 ? '' : ' ' . $where['boolean'] . ' ';
            $col = $this->quoteIdentifier($where['column']);
            if ($where['type'] === 'in' || $where['type'] === 'not_in') {
                $values = is_array($where['value']) ? $where['value'] : [];
                if ($values === []) {
                    $parts[] = $bool . ($where['type'] === 'in' ? '1 = 0' : '1 = 1');
                    continue;
                }
                $placeholders = implode(', ', array_fill(0, count($values), '?'));
                $parts[] = $bool . $col . ' ' . $where['operator'] . ' (' . $placeholders . ')';
                foreach ($values as $v) {
                    $bindings[] = $v;
                }
                continue;
            }

            $parts[] = $bool . $col . ' ' . $where['operator'] . ' ?';
            $bindings[] = $where['value'];
        }

        return [' WHERE ' . implode('', $parts), $bindings];
    }

    /** @param array<int,mixed> $bindings */
    private function statement(string $sql, array $bindings): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        foreach (array_values($bindings) as $i => $value) {
            $stmt->bindValue($i + 1, $value);
        }
        return $stmt;
    }

    private function assertIdentifier(string $identifier): string
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) !== 1) {
            throw new InvalidArgumentException("Invalid identifier [{$identifier}].");
        }
        return $identifier;
    }

    private function assertOperator(string $operator): string
    {
        $op = strtoupper($operator);
        $allowed = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE'];
        if (!in_array($op, $allowed, true)) {
            throw new InvalidArgumentException("Invalid operator [{$operator}].");
        }
        return $op;
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
