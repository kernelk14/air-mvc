<?php

declare(strict_types=1);

namespace Tests\Feature;

use Air\Http\Kernel;
use Air\Http\Request;
use Tests\TestCase;

final class DemoRoutesTest extends TestCase
{
    private function dispatch(string $method, string $uri, array $post = [], array $server = []): \Air\Http\Response
    {
        $server = array_merge(['REQUEST_METHOD' => $method, 'REQUEST_URI' => $uri], $server);
        $request = new Request(query: [], request: $post, cookies: [], files: [], server: $server, json: [], content: '');
        /** @var Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        return $kernel->handle($request);
    }

    public function testHome(): void
    {
        $res = $this->dispatch('GET', '/');
        $this->assertSame(200, $res->status());
        $this->assertStringContainsString('Build fast.', $res->content());
        $this->assertStringContainsString('pico/css/pico.min.css', $res->content());
    }

    public function testUserShow(): void
    {
        $res = $this->dispatch('GET', '/user/1');
        $this->assertSame(200, $res->status());
        $this->assertStringContainsString('User profile', $res->content());
        $this->assertStringContainsString('<strong>ID:</strong> 1', $res->content());
    }

    public function testLogin(): void
    {
        $res = $this->dispatch('GET', '/login');
        $this->assertSame(200, $res->status());
        $this->assertStringContainsString('Welcome back', $res->content());
    }

    public function testClickPost(): void
    {
        $token = csrf_token();
        $res = $this->dispatch('POST', '/click', ['enter' => 'hello', '_token' => $token]);
        $this->assertSame(200, $res->status());
        $this->assertStringContainsString('hello', $res->content());
    }

    public function test404(): void
    {
        $res = $this->dispatch('GET', '/missing');
        $this->assertSame(404, $res->status());
    }

    public function test405(): void
    {
        $res = $this->dispatch('PUT', '/login');
        $this->assertSame(405, $res->status());
    }
}
