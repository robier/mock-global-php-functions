<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\MockFunction\Exception;

final class MockCreationException extends Base
{
    public static function languageConstruct(string $constructName): self
    {
        return new static(\sprintf('Mocking of language construct %s is not possible', $constructName));
    }

    public static function emptyFunctionName(): self
    {
        return new static('Function name should not be empty');
    }

    public static function functionNotFound(string $function): self
    {
        return new static(\sprintf('Function %s not found in global namespace', $function));
    }

    public static function emptyNamespace(): self
    {
        return new static('Can not create mock function in global namespace, empty namespace provided');
    }
}
