<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction;

use Robier\MockGlobalFunction\MockFunction\NamespaceFromClassName;

final class MockFunction
{
    use NamespaceFromClassName;
    /**
     * @var MockFunction\Mock
     */
    protected $mock;

    public function __construct(string $namespace, string $functionName, callable $function = null)
    {
        $this->mock = MockFunction\Registry::register($namespace, $functionName, $function);
        $this->mock->enable();
    }

    /**
     * @param string $class
     * @param string $functionName
     * @param callable|null $function
     * @return $this
     */
    public static function inClassNamespace(string $class, string $functionName, callable $function = null): self
    {
        return new static(static::getNamespaceFromClassName($class), $functionName, $function);
    }

    /**
     * @return $this
     */
    public function enable(): self
    {
        $this->mock->enable();

        return $this;
    }

    /**
     * @return $this
     */
    public function disable(): self
    {
        $this->mock->disable();

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->mock->isEnabled();
    }

    /**
     * @param callable $function
     * @return $this
     */
    public function setFunction(callable $function): self
    {
        $this->mock->setFunction($function);

        return $this;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->mock->name();
    }

    /**
     * @return string
     */
    public function namespace(): string
    {
        return $this->mock->namespace();
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        return $this->mock->__invoke(...func_get_args());
    }

    public function __destruct()
    {
        $this->disable();
    }
}
