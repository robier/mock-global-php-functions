<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\MockFunction;

final class Mock
{
    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var callable
     */
    protected $function;

    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $namespace;

    public function __construct(string $name, string $namespace, callable $function)
    {
        $this->function = $function;
        $this->name = $name;
        $this->namespace = $namespace;
    }

    /**
     * @return $this
     */
    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function namespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param callable $function
     * @return $this
     */
    public function setFunction(callable $function): self
    {
        $this->function = $function;

        return $this;
    }

    /**
     * @return mixed
     */
    public function __invoke(&...$items)
    {
        if ($this->isEnabled()) {
            $function = $this->function;
            return $function(...$items);
        }

        // call function in global namespace if mock is disabled
        $function = '\\' . $this->name;
        return $function(...$items);
    }
}
