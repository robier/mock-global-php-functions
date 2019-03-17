<?php

declare(strict_types=1);

namespace Robier\MockGlobalFunction;

use Robier\MockGlobalFunction\MockFunction\NamespaceFromClassName;

final class FreezeClock
{
    use NamespaceFromClassName;

    private $mocks = [];

    private $functions = [
        'date' => null,
        'time' => null,
        'microtime' => null,
        'sleep' => null,
        'usleep' => null,
    ];

    /**
     * Freezes clock in given namespaces. $time param is provided as number (int or float) that value will be used
     * as current time for mocked function.
     *
     * @param int|float $time
     * @param string $namespace
     * @param string ...$namespaces
     */
    private function __construct($time, string $namespace, string ...$namespaces)
    {
        array_unshift($namespaces, $namespace);

        $this->functions['date'] = static function(string $format) use (&$time){
            return \date($format, (int)$time);
        };

        $this->functions['time'] = static function() use (&$time){
            return (int)$time;
        };

        $this->functions['microtime'] = static function(bool $asFloat = false) use (&$time){

            if ($asFloat) {
                return (float)$time;
            }

            return sprintf('%0.6f %d', $time - (int) $time, (int) $time);
        };

        $this->functions['sleep'] = static function($seconds) use (&$time){
            $time+=$seconds;

            return 0;
        };

        $this->functions['usleep'] = static function($microSeconds) use (&$time) {
            $time += ($microSeconds / 1000000);
        };

        foreach($namespaces as $namespace){
            foreach ($this->functions as $functionName => $function){
                $this->mocks[$namespace][$functionName] = new MockFunction($namespace, $functionName, $function);
            }
        }
    }

    public static function atZero(string $namespace, string ...$namespaces): self
    {
        return new static(0, $namespace, ...$namespaces);
    }

    public static function atTime(int $time, string $namespace, string ...$namespaces): self
    {
        return new static($time, $namespace, ...$namespaces);
    }

    public static function atMicrotime(float $microtime, string $namespace, string ...$namespaces): self
    {
        return new static($microtime, $namespace, ...$namespaces);
    }

    public static function atNow(string $namespace, string ...$namespaces): self
    {
        return new static(\microtime(true), $namespace, ...$namespaces);
    }

    /**
     * @param string $class
     * @param int|float|null $time
     * @return $this
     */
    public static function inClassNamespace($time, string $class, string ...$classes): self
    {
        array_unshift($classes, $class);

        $namespaces = [];
        foreach ($classes as $class){
            $namespaces[] = self::getNamespaceFromClassName($class);
        }

        if(null === $time){
            return self::atNow(...$namespaces);
        }

        return new self($time, ...$namespaces);
    }

    /**
     * @param string $format
     * @return string
     */
    public function date(string $format): string
    {
        return call_user_func($this->functions['date'], $format);
    }

    /**
     * @return int
     */
    public function time(): int
    {
        return call_user_func($this->functions['time']);
    }

    /**
     * @param bool $asFloat
     * @return mixed
     */
    public function microtime(bool $asFloat = false)
    {
        return call_user_func($this->functions['microtime'], $asFloat);
    }

    /**
     * @return $this
     */
    public function disable(): self
    {
        foreach($this->mocks as $namespace){
            foreach ($namespace as $mock) {
                $mock->disable();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function enable(): self
    {
        foreach($this->mocks as $namespace){
            foreach ($namespace as $mock) {
                $mock->enable();
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        foreach($this->mocks as $namespace){
            foreach ($namespace as $mock) {
                if(!$mock->isEnabled()){
                    return false;
                }
            }
        }

        return true;
    }

    public function __destruct()
    {
        $this->disable();
    }
}
