<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\Test\MockFunction;

use PHPUnit\Framework\TestCase;
use Robier\MockGlobalFunction\MockFunction\Mock;

class MockTest extends TestCase
{
    public function testGetters(): void
    {
        $mock = new Mock('name', 'namespace', static function(){});

        $this->assertSame('name', $mock->name());
        $this->assertSame('namespace', $mock->namespace());
    }

    public function testEnablingMock(): void
    {
        $mock = new Mock('name', 'namespace', static function(){});

        $this->assertFalse($mock->isEnabled());
        $this->assertInstanceOf(Mock::class, $mock->enable());
        $this->assertTrue($mock->isEnabled());
    }

    public function testDisablingMock(): void
    {
        $mock = new Mock('name', 'namespace', static function(){});
        $mock->enable();

        $this->assertTrue($mock->isEnabled());
        $this->assertInstanceOf(Mock::class, $mock->disable());
        $this->assertFalse($mock->isEnabled());
    }

    public function testChangingFunction(): void
    {
        $mock = new Mock('name', 'namespace', static function(){
            return null;
        });
        $mock->enable();

        $this->assertNull($mock());

        $mock->setFunction(static function(){
            return 12345;
        });

        $this->assertSame(12345, $mock());
    }

    public function testInvokingRealFunction(): void
    {
        $mock = new Mock('time', 'namespace', static function(){
            return null;
        });

        $this->assertSame(\time(), $mock());

        $mock->enable();

        $this->assertNull($mock());

        $mock->disable();

        $this->assertSame(\time(), $mock());
    }
}
