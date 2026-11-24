<?php

declare(strict_types=1);

namespace Air\Http;

class Response
{
    /** @param array<string,string> $headers */
    public function __construct(
        private string $content = '',
        private int $status = 200,
        private array $headers = [],
    ) {
    }

    /** @param array<string,string> $headers */
    public static function html(string $content, int $status = 200, array $headers = []): self
    {
        $headers = ['Content-Type' => 'text/html; charset=UTF-8'] + $headers;

        return new self($content, $status, $headers);
    }

    /**
     * @param mixed $data
     * @param array<string,string> $headers
     */
    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        $headers = ['Content-Type' => 'application/json; charset=UTF-8'] + $headers;
        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($content === false) {
            $content = '{"error":"Failed to encode JSON"}';
            $status = 500;
        }

        return new self($content, $status, $headers);
    }

    /** @param array<string,string> $headers */
    public static function redirect(string $to, int $status = 302, array $headers = []): self
    {
        $headers = ['Location' => $to] + $headers;

        return new self('', $status, $headers);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function content(): string
    {
        return $this->content;
    }

    /** @return array<string,string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value, true, $this->status);
            }
        }

        echo $this->content;
    }
}
