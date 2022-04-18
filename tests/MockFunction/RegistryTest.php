<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\Test\MockFunction;

use Generator;
use InvalidArgumentException;
use Robier\MockGlobalFunction\MockFunction\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // setup fake classes
        $testClass = <<<PHP
namespace Test\Fake\Registry {
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

    public function testFailInvokeNotRegisteredNamespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Namespace %s is not registered for mocking', 'test\not_existing_namespace'));

        Registry::invoke('test\not_existing_namespace', 'time');
    }

    public function testFailInvokeNotRegisteredFunction(): void
    {
        Registry::register('test\existing_namespace', 'sleep');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Function %s in %s namespace is not registered for mocking', 'time', 'test\existing_namespace'));

        Registry::invoke('test\existing_namespace', 'time');
    }

    public function testIsMockedMethod(): void
    {
        $mock = Registry::register('test\existing_namespace', 'sleep');

        $this->assertFalse(Registry::isMocked('test\existing_namespace\sleep'));

        $mock->enable();

        $this->assertTrue(Registry::isMocked('test\existing_namespace\sleep'));

        $this->assertFalse(Registry::isMocked('test\not_existing_namespace\rand'));
    }

    public function testIsMockedFunctionNotRegisteredMethod(): void
    {
        Registry::register('test\existing_namespace', 'sleep');

        $this->assertFalse(Registry::isMocked('test\existing_namespace\rand'));
    }

    public function languageConstructsProvider(): Generator
    {
        foreach(Registry::LANGUAGE_CONSTRUCTS as $construct){
            yield [$construct];
        }
    }

    /**
     * @dataProvider languageConstructsProvider
     * @param string $languageConstruct
     */
    public function testFailMockingLanguageConstructs(string $languageConstruct): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Mocking of language construct %s is not possible', $languageConstruct));

        Registry::register('Test\NotExisting\Space', $languageConstruct);
    }

    public function testFailMockingNotExistingGlobalFunction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Function %s not found in global namespace', 'foobar'));

        Registry::register('Test\NotExisting\Space', 'foobar');
    }

    public function testFailMockingFunctionInGlobalNamespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can not create mock function in global namespace, empty namespace provided');

        Registry::register('', 'time');
    }

    public function testMockedFunctionNeedsToBeEnabledManually(): void
    {
        $mock = Registry::register('Test\Fake\Registry1', 'time', static function(){
            return 5;
        });

        $mock->enable();

        $this->assertSame(5, \Test\Fake\Registry1\time());
    }

    public function testMockedFunctionIsDisabledWhenRegistryIsUsedDirectly(): void
    {
        Registry::register('Test\Fake\Registry2', 'time', static function(){
            return 5;
        });

        $this->assertNotSame(5, \Test\Fake\Registry2\time());
    }

    public function testMockingSameFunctionMultipleTimes(): void
    {
        $mock = Registry::register('Test\Fake\Registry3', 'time', static function(){
            return 5;
        });
        $mock->enable();

        Registry::register('Test\Fake\Registry3', 'time', static function(){
            return 20;
        });

        $this->assertSame(20, \Test\Fake\Registry3\time());
    }

    public function testFunctionCanNotBeMockedIfAlreadyExistingInNamespace(): void
    {
        $php = <<<PHP
    namespace Test\Fake\Bar {
        function rand(): int {
            return 0;
        }
    }
PHP;
        eval($php);

        Registry::register('Test\Fake\Bar', 'rand', static function(): int {
            return 1;
        })->enable();

        self::assertSame(0, \Test\Fake\Bar\rand());
    }
}
