<?php

declare(strict_types=1);

namespace Air\Validation;

final class Validator
{
    /** @var array<string,array<int,string>> */
    private array $errors = [];

    /**
     * @param array<string,mixed> $data
     * @param array<string,string|array<int,string>> $rules
     */
    public function __construct(private array $data, private array $rules)
    {
        $this->validate();
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,string|array<int,string>> $rules
     */
    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string,array<int,string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string,mixed> */
    public function validated(): array
    {
        $validated = [];
        foreach ($this->rules as $field => $_) {
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }
        return $validated;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;
            foreach ($rules as $rule) {
                $rule = trim((string) $rule);
                if ($rule === '') {
                    continue;
                }
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $name = strtolower($name);
                if ($name === 'required' && ($value === null || $value === '' || $value === [])) {
                    $this->add($field, "{$field} is required.");
                }
                if ($name === 'email' && $value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $this->add($field, "{$field} must be a valid email.");
                }
                if ($name === 'min' && is_string($value) && $param !== null && strlen($value) < (int) $param) {
                    $this->add($field, "{$field} must be at least {$param} characters.");
                }
                if ($name === 'max' && is_string($value) && $param !== null && strlen($value) > (int) $param) {
                    $this->add($field, "{$field} must be at most {$param} characters.");
                }
            }
        }
    }

    private function add(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
