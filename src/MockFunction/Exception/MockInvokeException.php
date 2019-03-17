<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\MockFunction\Exception;

final class MockInvokeException extends Base
{
    public static function namespaceNotRegistered(string $namespace): self
    {
        return new self(\sprintf('Namespace %s is not registered for mocking', $namespace));
    }

    public static function functionNotMocked(string $functionName, string $namespace)
    {
        return new self(\sprintf('Function %s in %s namespace is not registered for mocking', $functionName, $namespace));
    }
}
