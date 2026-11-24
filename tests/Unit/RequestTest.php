<?php

declare(strict_types=1);

namespace Tests\Unit;

use Air\Http\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testInputAndMethod(): void
    {
        $request = new Request(
            query: ['q' => '1'],
            request: ['name' => 'air'],
            cookies: [],
            files: [],
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/submit', 'HTTP_ACCEPT' => 'application/json'],
            json: ['json' => 'yes'],
            content: '{"json":"yes"}',
        );

        $this->assertSame('POST', $request->method());
        $this->assertSame('/submit', $request->path());
        $this->assertSame('air', $request->input('name'));
        $this->assertSame('1', $request->input('q'));
        $this->assertSame('yes', $request->input('json'));
        $this->assertTrue($request->expectsJson());
        $this->assertSame(['q' => '1', 'name' => 'air', 'json' => 'yes'], $request->all());
    }
}
