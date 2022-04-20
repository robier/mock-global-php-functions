<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\MockFunction;

final class ParamBuilder
{
    private $parameters;
    private $arguments;

    public function __construct(string $function)
    {
        $reflection = new \ReflectionFunction($function);
        if(!$this->hasPassingByReference(...$reflection->getParameters())) {
            $this->parameters = '...$params';
            $this->arguments = '...$params';
            return;
        }

        $arguments = [];
        $parameters = [];
        foreach ($reflection->getParameters() as $index => $parameter) {
            $arguments[] = '$param' . $index;
            $parameters[] = $this->createParameter($index, $parameter);
        }

        $this->arguments = $this->combine($arguments);
        $this->parameters = $this->combine($parameters);
    }

    /**
     * Parameters are the names listed in function signature
     */
    public function parameters(): string
    {
        return $this->parameters;
    }

    /**
     * Arguments are actual values passed to function
     */
    public function arguments(): string
    {
        return $this->arguments;
    }

    private function hasPassingByReference(\ReflectionParameter ...$reflectionParameters): bool
    {
        foreach ($reflectionParameters as $reflectionParameter) {
            if ($reflectionParameter->isPassedByReference()) {
                return true;
            }
        }

        return false;
    }

    private function createParameter(int $index, \ReflectionParameter $reflectionParameter): string
    {
        $param = '$param' . $index;

        if ($reflectionParameter->isVariadic()) {
            $param = '...' . $param;
        }

        if ($reflectionParameter->isPassedByReference()) {
            $param = '&' . $param;
        }

        if ($reflectionParameter->isVariadic()) {
            // do not go further as this is variadic parameter
            return $param;
        }

        if(!$reflectionParameter->isOptional()) {
            return $param;
        }

        try {
            if ($reflectionParameter->isDefaultValueAvailable()) {
                $defaultValue = $reflectionParameter->getDefaultValue();
            } elseif ($reflectionParameter->isDefaultValueConstant()) {
                $defaultValue = $reflectionParameter->getDefaultValueConstantName();
            }
        } catch (\ReflectionException $e) {
            // silence!
        }

        if (isset($defaultValue)) {
            if (is_string($defaultValue)) {
                $defaultValue = "'$defaultValue'";
            }

            $param .= ' = ' . $defaultValue;
        } else {
            $param .= ' = null';
        }

        return $param;
    }

    private function combine(array $items): string
    {
        return implode(', ', $items);
    }
}
