<?php

declare(strict_types=1);

namespace Tests\Unit;

use Air\Container\Container;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testBindAndSingleton(): void
    {
        $c = new Container();
        $c->bind('foo', fn () => new \stdClass());
        $a = $c->make('foo');
        $b = $c->make('foo');
        $this->assertNotSame($a, $b);

        $c->singleton('bar', fn () => new \stdClass());
        $this->assertSame($c->make('bar'), $c->make('bar'));
    }
}
