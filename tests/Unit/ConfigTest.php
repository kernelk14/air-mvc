<?php

declare(strict_types=1);

namespace Tests\Unit;

use Air\Config\Repository;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testDotAccess(): void
    {
        $repo = new Repository(['app' => ['name' => 'Air', 'debug' => true]]);
        $this->assertTrue($repo->has('app.name'));
        $this->assertSame('Air', $repo->get('app.name'));
        $this->assertSame('fallback', $repo->get('app.missing', 'fallback'));
        $repo->set('db.host', 'localhost');
        $this->assertSame('localhost', $repo->get('db.host'));
    }
}
