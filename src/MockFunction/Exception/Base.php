<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction\MockFunction\Exception;

use InvalidArgumentException;

class Base extends InvalidArgumentException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 0, null);
    }
}
