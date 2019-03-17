<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\MockFunction;

trait NamespaceFromClassName
{
    /**
     * @param string $class
     * @return string
     * @throws \ReflectionException
     */
    protected static function getNamespaceFromClassName(string $class): string
    {
        $reflection = new \ReflectionClass($class);

        return $reflection->getNamespaceName();
    }
}
