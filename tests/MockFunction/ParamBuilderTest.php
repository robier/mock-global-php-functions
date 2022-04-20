<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\Test\MockFunction;

use Robier\MockGlobalFunction\MockFunction\ParamBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Robier\MockGlobalFunction\MockFunction\ParamBuilder
 */
final class ParamBuilderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // this will be by default in global namespace
        $testClass = <<<PHP
    function test_variadic_function(...\$test) {
        return null;
    }
    
    function test_multiple_params_function(\$test1, \$test2, \$test3, \$test4) {
        return null;
    }
    
    function test_passing_by_reference_function(\$test1, &\$test2, \$test3, &\$test4) {
        return null;
    }

    function test_params_with_default_value_function(&\$test1, &\$test2, \$test3 = 'abc', \$test4 = 123) {
        return null;
    }
PHP;
        eval($testClass);
    }

    public function testVariadicFunction(): void
    {
        $paramBuilder = new ParamBuilder('test_variadic_function');

        self::assertSame('...$params', $paramBuilder->arguments());
        self::assertSame('...$params', $paramBuilder->parameters());
    }

    public function testMultipleParamsFunction(): void
    {
        $paramBuilder = new ParamBuilder('test_multiple_params_function');

        self::assertSame('...$params', $paramBuilder->arguments());
        self::assertSame('...$params', $paramBuilder->parameters());
    }

    public function testParamsPassingByReferenceFunction(): void
    {
        $paramBuilder = new ParamBuilder('test_passing_by_reference_function');

        self::assertSame('$param0, $param1, $param2, $param3', $paramBuilder->arguments());
        self::assertSame('$param0, &$param1, $param2, &$param3', $paramBuilder->parameters());
    }

    public function testParamsWithDefaultValueFunction(): void
    {
        $paramBuilder = new ParamBuilder('test_params_with_default_value_function');

        self::assertSame('$param0, $param1, $param2, $param3', $paramBuilder->arguments());
        self::assertSame("&\$param0, &\$param1, \$param2 = 'abc', \$param3 = 123", $paramBuilder->parameters());
    }
}
