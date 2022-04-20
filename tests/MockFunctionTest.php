<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\Test;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Robier\MockGlobalFunction\MockFunction;

class MockFunctionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // setup fake classes
        $testClass = <<<PHP
namespace Test\Fake\MockFunction {
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

    public function invalidFunctionNameDataProvider(): Generator
    {
        yield 'empty string' => ['', 'Function name should not be empty'];

        // language constructs
        foreach (MockFunction\Registry::LANGUAGE_CONSTRUCTS as $construct) {
            yield 'php construct ' . $construct => [$construct, sprintf('Mocking of language construct %s is not possible', $construct)];
        }
    }

    /**
     * @dataProvider invalidFunctionNameDataProvider
     * @param string $functionName
     */
    public function testFailMockingBadFunctionNames(string $functionName, string $exceptionMessage): void
    {
        $this->expectException(MockFunction\Exception\MockCreationException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new MockFunction('Test\NotExisting\Space', $functionName);
    }

    /**
     * @dataProvider invalidFunctionNameDataProvider
     * @param string $functionName
     */
    public function testFailMockingBadFunctionNamesForClass(string $functionName, string $exceptionMessage): void
    {
        $this->expectException(MockFunction\Exception\MockCreationException::class);
        $this->expectExceptionMessage($exceptionMessage);

        MockFunction::inClassNamespace(\Test\Fake\MockFunction\TestClass::class, $functionName);
    }

    /**
     * @requires PHP < 8
     */
    public function testFailNotExistingClassInPhp7(): void
    {
        $notExistingClass = \Test\NotExisting\TestClass::class;

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage(sprintf('Class %s does not exist', $notExistingClass));

        MockFunction::inClassNamespace($notExistingClass, 'time');
    }

    /**
     * @requires PHP >= 8
     */
    public function testFailNotExistingClassInPhp8(): void
    {
        $notExistingClass = \Test\NotExisting\TestClass::class;

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage(sprintf('Class "%s" does not exist', $notExistingClass));

        MockFunction::inClassNamespace($notExistingClass, 'time');
    }

    public function testMockingNamespaceUsingClassName()
    {
        $mock = MockFunction::inClassNamespace(\Test\Fake\MockFunction\TestClass::class, 'rand', function() {
            return 'rand-text';
        });

        $testClass = new \Test\Fake\MockFunction\TestClass();

        $this->assertSame('rand-text', $testClass->testRand());
    }

    public function testMockingFunction(): void
    {
        $mock = new MockFunction('test', 'time', function() {
            return 1;
        });

        $this->assertNotEquals(\time(), \test\time());
        $this->assertSame(1, \test\time());

        $mock->disable();

        $this->assertNotEquals(1, \test\time());
        $this->assertSame(\time(), \test\time());

        $mock->enable();

        $this->assertNotEquals(\time(), \test\time());
        $this->assertSame(1, \test\time());

        unset($mock);

        $this->assertNotEquals(1, \test\time());
        $this->assertSame(\time(), \test\time());
    }

    public function testNamespaceMethod(): void
    {
        $mock = new MockFunction('test', 'time', function() {
            return 1;
        });

        $this->assertSame('test', $mock->namespace());
    }

    public function testNameMethod(): void
    {
        $mock = new MockFunction('test', 'rand', function() {
            return 1;
        });

        $this->assertSame('rand', $mock->name());
    }

    public function testIsEnabledMethod(): void
    {
        $mock = new MockFunction('test', 'rand', function() {
            return 1;
        });

        $this->assertTrue($mock->isEnabled());

        $mock->disable();

        $this->assertFalse($mock->isEnabled());

        $mock->enable();

        $this->assertTrue($mock->isEnabled());
    }

    public function testSetFunctionMethod(): void
    {
        $mock = new MockFunction('test', 'rand', function() {
            return 1;
        });

        $this->assertSame(1, \test\rand());

        $mock->setFunction(function() {
            return 5;
        });

        $this->assertSame(5, \test\rand());
    }

    public function testInvocation(): void
    {
        $mock = new MockFunction('test', 'rand', function() {
            return 1234;
        });

        $this->assertSame(1234, $mock());
    }

    public function testFailMockingNotExistingFunction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Function %s not found in global namespace', 'foobar'));

        new MockFunction('test', 'foobar', function() {
            return 1234;
        });
    }

    public function testFailMockingFunctionInGlobalNamespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can not create mock function in global namespace');

        new MockFunction('', 'time', function() {
            return 1234;
        });
    }

    public function testFunctionCanNotBeMockedIfNamespaceIsAlreadyLoaded(): void
    {
        $testObject = new \Test\Fake\MockFunction\TestClass();

        $this->assertNotSame(0, $testObject->testRand());

        $mock = new MockFunction('Test\Fake\MockFunction', 'rand', static function(){
            return 0;
        });

        $this->assertNotSame(0, $testObject->testRand());
    }

    public function testFunctionThatPassesParamsByReference(): void
    {
        $mock = new MockFunction(
            'test',
            'getmxrr',
            static function (string $_, array &$hosts): bool {
                $hosts = ['foo.bar'];
                return true;
            }
        );

        $test = [];

        self::assertTrue(\test\getmxrr('test', $test));
        self::assertSame(['foo.bar'], $test);
    }
}
