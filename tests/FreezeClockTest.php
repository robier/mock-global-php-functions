<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\Test;

use PHPUnit\Framework\TestCase;
use Robier\MockGlobalFunction\FreezeClock;

class FreezeClockTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // setup fake classes
        $testClass = <<<PHP
namespace Test\Fake\MockClock {
    class TestClass{
        public function testSleep(){
            sleep(20);
        }
        public function testTime(){
            return time();
        }
        public function testRand(){
            return rand();
        }
    }
}

namespace Test\Fake\MockClock2 {
    class TestClass{
        public function testSleep(){
            sleep(20);
        }
        public function testTime(){
            return time();
        }
        public function testRand(){
            return rand();
        }
    }
}
PHP;
        eval($testClass);
    }

    public function testMockClock(): void
    {
        $mock = FreezeClock::atZero('test');

        $this->assertEquals(0, \test\time());
        $this->assertSame('1970-01-01', \test\date('Y-m-d'));
        $this->assertSame(\test\time(), $mock->time());
        $this->assertSame(\test\date('Y-m-d'), $mock->date('Y-m-d'));
        $this->assertSame(\test\microtime(), $mock->microtime());

        \test\sleep(10);

        $this->assertEquals(10, \test\time());

        \test\usleep(1000);

        $this->assertEquals(10, \test\time());
        $this->assertEquals(10.001, \test\microtime(true));
        $this->assertEquals('0.001000 10', \test\microtime());

        $mock->disable();

        // time function on mock always returns mocked value
        $this->assertSame(10, $mock->time());
        $this->assertSame(\time(), \test\time());

        $mock->enable();

        $this->assertSame(10, \test\time());

        unset($mock);

        $this->assertSame(\time(), \test\time());
    }

    public function testIsEnabledMethod(): void
    {
        $mock = FreezeClock::atZero('test');

        $this->assertTrue($mock->isEnabled());

        $mock->disable();

        $this->assertFalse($mock->isEnabled());

        $mock->enable();

        $this->assertTrue($mock->isEnabled());
    }

    public function testMockingNamespaceUsingClassName(): void
    {
        $mock = FreezeClock::inClassNamespace(20, \Test\Fake\MockClock\TestClass::class);

        $this->assertSame(20, $mock->time());
    }

    public function testFreezeTime(): void
    {
        $mock = FreezeClock::inClassNamespace(null,\Test\Fake\MockClock\TestClass::class);

        $this->assertSame(\time(), $mock->time());
    }

    public function testMultipleNamespacesFreeze(): void
    {
        $mock = FreezeClock::atZero('Test\Fake\MockClock', 'Test\Fake\MockClock2');

        $this->assertSame(0, \Test\Fake\MockClock\time());
        $this->assertSame(0, \Test\Fake\MockClock2\time());

        \Test\Fake\MockClock\sleep(20);
        \Test\Fake\MockClock2\sleep(5);

        $this->assertSame(25, \Test\Fake\MockClock\time());
        $this->assertSame(25, \Test\Fake\MockClock2\time());
    }

    public function testStaticFactoryMethods(): void
    {
        $mockNow = FreezeClock::atNow('Test\fakeNow');

        $this->assertSame(\time(), \Test\fakeNow\time());

        $mockZero = FreezeClock::atZero('Test\fakeZero');

        $this->assertSame(0, \Test\fakeZero\time());
        $this->assertSame(0.0, \Test\fakeZero\microtime(true));

        $mockTime = FreezeClock::atTime(15,'Test\fakeTime');

        $this->assertSame(15, \Test\fakeTime\time());
        $this->assertSame(15.0, \Test\fakeTime\microtime(true));

        $mockMicrotime = FreezeClock::atMicrotime(10.45,'Test\fakeMicrotime');

        $this->assertSame(10, \Test\fakeMicrotime\time());
        $this->assertSame(10.45, \Test\fakeMicrotime\microtime(true));
    }
}
