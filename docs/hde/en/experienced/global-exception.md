# Mastering GlobalException

(browse code: [src/GlobalException](../../../../src/GlobalException.php))

Sometimes you need more flexibility with [GlobalException](../dummies/global-exception.md). This article describes some
complicated cases and their possible solutions.

Contents:
- [Inadecuate base code maximum](#inadecuate-base-code-maximum)
- [Global code application limit](#global-code-application-limit)

## Inadecuate base code maximum

Your application starts using an external service, let's call it _ExtApp_. That service can throw exceptions. You
create another [GlobalException](../dummies/global-exception.md) descendant called _ExtAppException_ and configure a
unique _class code_ for it, let it be **29**. So when _ExtApp_ throws an exception **5101** you pass its code as
_base code_ to _ExtAppException_ constructor, get your _global code_ **2905101** and everyone is happy...

_SUDDENLY_ _ExtApp_ throws an exception with its code **325009**. Then bad things happen:
- This code is considered as invalid by `validateCodeBase()` (because it is **not** less than **100000**).
- The exception is not designated as _global_, `getCode()` returns **325009** _as is_.
- The exception's _class code_ is considered equal to **0** when its _global code_ is parsed via `getCodeParts()`.
- [Parser](../dummies/parser.md) throws a validation error.

### Solution

Increase _ExtAppException_ _base code_ maximum.

[GlobalException](../dummies/global-exception.md) has `CLASS_CODE_MULTIPLIER` constant which is used for any validation
or calculation concerning _base codes_. Initially its value equals to **100000** - every _base code_ must be less than
this value to be considered as valid.

You can redefine this constant for _ExtAppException_ class. Firstly predict the maximum possible code thrown by
_ExtApp_; let's imagine it is something around **99999999** (**8** digits). Your next move is to redefine
`CLASS_CODE_MULTIPLIER` in _ExtAppException_ with **8** power of ten:

```php
class ExtAppException extends MyAppBaseException
{
    const CLASS_CODE_MULTIPLIER = 10 ** 8;
    // ...
}
```

That's it! You don't even need to change _ExtAppException_ _class code_! From this point if _ExtApp_ throws the
exception **325009** your _ExtAppException_ will successfully calculate the _global code_ **2900325009**.

You should also take into consideration that from this point if you define another exception class with the same _class
code_ **29** it will be considered as duplicate only if that new class has the same `CLASS_CODE_MULTIPLIER` as
_ExtAppException_. Otherwise it is guaranteed (and [Parser](../dummies/parser.md) can prove it) that you will have no
_global codes_ duplicates throughout these two classes:

```php
class ExtAppException extends MyAppBaseException
{
    const CLASS_CODE_MULTIPLIER = 10 ** 8;
}

class AnotherException extends MyAppBaseException
{
}

class MyAppBaseException extends GlobalException
{
    const CLASS_CODE_LIST = [
        ExtAppException::class  => 29, // CLASS_CODE_MULTIPLIER = 10 ** 8;
        AnotherException::class => 29, // CLASS_CODE_MULTIPLIER = 10 ** 5;
    ];
}

echo ExtAppException::getCodeGlobal(1) . "\n";  // >> 2900000001
echo AnotherException::getCodeGlobal(1) . "\n"; // >> 2900001
```

At the same time and for the same reason you should **not** set _AnotherException_ _class code_ to **29000**. Otherwise
it is possible to generate _global codes_ duplicates (and [Parser](../dummies/parser.md) will throw an exception for
this case).

## Global code application limit

An exception valid _global code_ is limited by valid _base code_ and _class code_. You should already know the
[real _base code_ condition](#inadecuate-base-code-maximum) and how to alter it.

A _class code_ is validated by `validateCodeClass()` - it must be positive (or equal to **0** for disabling the
globalization feature) but less than the value returned by `getCodeClassMax()`. This condition guarantee that a
possible _global code_ maximum will not exceed PHP integer maximum. But sometimes you need to set much smaller limit.

For instance if your aplication has its own API based on [XML-RPC protocol](http://xmlrpc.scripting.com/spec.html)
your API capabilites are limited: _faultCode_ (and any other integer values) must be of type 32-bit signed integer -
_int8_ is not supported by the protocol. So you need to ensure your _global codes_ will _not_ exceed 32-bit signed
integer.

### Solution

`getCodeClassMax()` is finalized intentionally. Change `GLOBAL_CODE_MAX_RELATIVE` instead. Initially its value equals
to `PHP_INT_MAX` but you can change it to fit your application limits. For the example described above you should set
the limit to 32-bit signed integer maximum:

```php
// All your exceptions classes used in your application API must extend this class:
class MyAppOrAPIBaseException extends GlobalException
{
    // ...
    const GLOBAL_CODE_MAX_RELATIVE = 2 ** 31 - 1; // 2147483647
    // ...
}
```

Then if you don't alter `CLASS_CODE_MULTIPLIER` your _class code_ maximum will be **21473**. If you throw an
exception with the _base code_ **99999** you will get the _global code_ **2147399999** wich is definitely less than
32-bit signed integer maximum (**2147483647**).

## Further reading

- [GlobalException basics](../dummies/global-exception.md)
- [Mastering CustomizableException]()
- [Mastering Parser]()
