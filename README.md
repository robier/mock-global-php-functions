<p align="center">
    <a href="https://travis-ci.org/robier/mock-global-php-functions">
        <img src="https://travis-ci.org/robier/mock-global-php-functions.svg?branch=master" alt="Build Status">
    </a>
    <a href="https://codecov.io/gh/robier/mock-global-php-functions">
        <img src="https://codecov.io/gh/robier/mock-global-php-functions/branch/master/graph/badge.svg" />
    </a>
    <a href="https://travis-ci.org/robier/mock-global-php-functions">
        <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="MIT">
    </a>
</p>


Mock global php functions
-------------------------

This library allows you to mock any global PHP functions to return predefined values.

This library was created after I saw implementation of Symfony's [ClockMock](https://github.com/symfony/symfony/blob/4.2/src/Symfony/Bridge/PhpUnit/ClockMock.php)
component for mocking PHP native functions related to time. I was curious how to make a mechanism for
mocking any PHP global function, so I created this small library.

This is achieved by [PHP's namespace fallback policy](http://php.net/manual/en/language.namespaces.fallback.php):

> PHP will fall back to global functions […]
> if a namespaced function […] does not exist.

Library uses mentioned feature to dynamically create namespaced function. For this library
to work your code that you are testing should be in **non global namespace** context and call function
**unqualified**. For example:

```php
namespace foo;

$time = time(); // This call can be mocked, a call to \time() can't.
```

## Known limitations

- Only **unqualified** function calls in a namespace context can be mocked. For example a call for `time()` in 
  the namespace `foo` is mockable, a call for `\time()` is not.
- The mock has to be defined before the first call to the unqualified function in the tested class. 
  This is documented in [Bug #68541](https://bugs.php.net/bug.php?id=68541). In most cases, you can ignore this
  limitation as you only need to define a mock before any testing in your tests. It's also recommend that you
  **run your tests as isolated process**.

### Api

At the moment there are 2 mocking objects that developers can use:
- `MockFunction` - mock any given function with your own anonymous function
- `FreezeClock` - mock time/sleep/date related functions, so they are in sync

### MockFunction

Object that can mock any function from global namespace. Mock is active as long as magic method `__destruct`
is not called on that mock, or it's disabled manually via `disable` method. In that way you do not need to manually turn off mock
while writing tests, because as soon as current scope exists, mock is disabled, and you have clean slate.

**Note:** When creating mock use static callback instead of regular one. This prevents them from having the current
class automatically bound to them.

Example:
```php
// mock is active as soon it's defined
$mock = new MockFunction('app', 'rand', static function(){
    return 5;
});

namespace app {
    // some logic
    $randomNumber = rand(); // random number will be always 5
    // some logic
}
```

Example of mocking sleep function:
```php

$mock = new MockFunction('app', 'sleep', static function(){
    return 0;
});

namespace app {
    // some logic
    sleep(100); // sleep will not wait for 100 seconds as function is mocked
    // some logic
}
```

### FreezeClock

Handy object that can freeze time in given namespace.

Handy factory methods:
- `atZero` - stops time at timestamp 0 
- `atTime` - stops time at provided time
- `atMicrotime` - stops time at provided microtime
- `atNow` - stops at current time

Functions affected:
- `date` - returns freeze formatted time
- `time` - return freeze time
- `microtime` - return freeze microtime
- `sleep` - increase freeze time and it's not blocking like regular sleep function
- `usleep` - increase freeze time and it's not blocking like regular usleep function

```php
use Robier\MockGlobalFunction\FreezeClock;

$mock = new FreezeClock::atZero('app/testNamespace');

\app\testNamespace\sleep(15);

\app\testNamespace\time(); // would return 15
\app\testNamespace\microtime(true); // would return 15.0 also
```

### Tests

First run `docker/build` to build a container and then `docker/run composer run test` for running all tests.

### Contribution

Feel free to contribute!
