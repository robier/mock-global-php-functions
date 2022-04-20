<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\MockFunction;

use InvalidArgumentException;
use Robier\MockGlobalFunction\MockFunction\Exception\MockCreationException;
use Robier\MockGlobalFunction\MockFunction\Exception\MockInvokeException;

final class Registry
{
    /**
     * @var array
     */
    protected static $registry = [];

    /**
     * @var array
     */
    const LANGUAGE_CONSTRUCTS = [
        '__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable',
        'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default',
        'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor',
        'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'finally', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements',
        'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset',
        'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public',
        'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait',
        'try', 'unset', 'use', 'var', 'while', 'xor', 'yield',
    ];

    /**
     * @param string $namespace
     * @param string $function
     * @param callable|null $mockFunction
     * @return Mock
     */
    public static function register(string $namespace, string $function, callable $mockFunction = null): Mock
    {
        if (in_array($function, self::LANGUAGE_CONSTRUCTS)) {
            throw MockCreationException::languageConstruct($function);
        }

        if ('' === $function) {
            throw MockCreationException::emptyFunctionName();
        }

        if (false === function_exists('\\' . $function)) {
            throw MockCreationException::functionNotFound($function);
        }

        if ('' === $namespace) {
            throw MockCreationException::emptyNamespace();
        }

        if (null === $mockFunction) {
            $mockFunction = static function() {};
        }

        if (!isset(self::$registry[$namespace][$function])) {
            self::$registry[$namespace][$function] = new Mock($function, $namespace, $mockFunction);
            self::registerMockFunction($namespace, $function);

            return self::$registry[$namespace][$function];
        }

        return self::$registry[$namespace][$function]->setFunction($mockFunction);
    }

    /**
     * Invoke mocked function if it's defined
     */
    public static function invoke(string $namespace, string $functionName, &...$args)
    {
        if (!isset(self::$registry[$namespace])) {
            throw MockInvokeException::namespaceNotRegistered($namespace);
        }

        if (!isset(self::$registry[$namespace][$functionName])) {
            throw MockInvokeException::functionNotMocked($functionName, $namespace);
        }

        /** @var Mock $mock */
        $mock = self::$registry[$namespace][$functionName];

        return $mock(...$args);
    }

    /**
     * @param string $namespace
     * @param string $function
     */
    protected static function registerMockFunction(string $namespace, string $function): void
    {
        $mock = '\\' . self::class;

        if (\function_exists(\sprintf('%s\%s', $namespace, $function))) {
            // we hope that we already have registered mock function
            return;
        }

        $paramBuilder = new ParamBuilder($function);
        $parameters = $paramBuilder->parameters();
        $arguments = $paramBuilder->arguments();

        $template = <<<PHP
namespace $namespace {
    function $function($parameters){
        return $mock::invoke(__NAMESPACE__, '$function', $arguments);
    }
}
PHP;

        eval($template);
    }

    /**
     * @param string $function FQN of function
     * @return bool
     */
    static public function isMocked(string $function): bool
    {
        $explodedNamespace = \explode('\\', $function);

        $function = \array_pop($explodedNamespace);
        $namespace = \implode('\\', $explodedNamespace);

        if (!isset(self::$registry[$namespace])) {
            return false;
        }

        if (!isset(self::$registry[$namespace][$function])) {
            return false;
        }

        return self::$registry[$namespace][$function]->isEnabled();
    }
}
