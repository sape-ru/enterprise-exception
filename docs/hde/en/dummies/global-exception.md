# GlobalException

(browse: [src/GlobalException.php](../../../../src/GlobalException.php))

Contents:
- [Problem](#problem)
- [Solution](#solution)
- [How it works](#how-it-works)
- [Setup](#setup)
- [Codes validation](#codes-validation)
- [Further reading](#further-reading)

## Problem

A web application _CoolApp_ has its own API. That API has a function; let's call it... _foo()_. That _foo()_ function
has some complicated business logic and can throw different exceptions. Some of those exceptions are:
- **230** => _Not enough money._
- **525** => _Internal temporary error. Please try again 5 seconds later._

When API client _unfortunate_robot_ (you'll understand very soon why it is so unfortunate) calls _foo()_ it expects
those exceptions and is ready to parse and process them by codes (not a big deal). When it catches **230** it informs a
user that some money should be addedd to a wallet for completing the process successfully. And when that
_unfortunate_robot_  catches **525** it just sleeps for 5 seconds and then calls _foo()_ again (with no tries limit;
yes, it's is not a very good practice but is suitable for the problem illustration).

SUDDENLY _CoolApp_ makes a deal with _OddApp_ to operate together for some mutual benefits. One of the deal's part is
that _foo()_ business logic includes now a request to _OddApp_ API for some extra processing.
So one unfortunate day our already well known _unfortunate_robot_ calls _foo()_ and gets **525**... The problem is that
this time the **525** exception is thrown by _OddApp_'s functionality and means something very different: _Out of
stock_. Such an exception occurs every time this poor robot calls _foo()_, catches **525**, sleeps 5 seconds, tries
again... And there is more: _CoolApp_ **still keeps** its own **525** - _Internal temporary error_ exception may appear
as well.

So that _unfortunate_robot_ just can't determine the nature of that **525** now. The robot's developer must notice
the endless loop problem and reprogram the robot to parse not a code but a message. As you probably know such a
practice is not a very reliable one. On the other side _CoolApp_ could handle _OddApp_ exception to transform it to
_CoolApp_ exception with the same meaning and a new code **600** but such a thing must be done for each new _OddApp_
exception.

Of course there are other possible solutions for such a problem and there are other inconveniences caused by exceptions
codes duplicates. But we wanted something universal that could handle many different problems by one common and more
automated strategy... And here comes _**GlobalException**_ solution.

## Solution

It would be great if _CoolApp_ could throw its own **525** exception with the code _**1**_**00525** and _OddApp_
**525** exception with the code _**2**_**00525**. If done so then _unfortunate_robot_ becomes _lucky_robot_ because it
can differ those codes easily without any hacks! The only thing the robot's developer needs is specifying both new
codes for the exceptions individual post-processing.

So how exactly **GlobalException** can do such a trick easily and mostly automated?

## How it works

![global exception parts]()

The globalization mechanism is based on a simple math calculation involving mainly these two integers:
- _Base code_ - it is specified as an exception code: you pass it as the second parameter to an exception constructor.
- _Class code_ - that's the integer which transforms _base codes_ into _global_ ones becoming their "higher" part.
It is configured in an exception class config.

For instance **GlobalException** can create an exception with its code _45600123_ based on the _base code_ _123_ and
the _class code_ _456_.

In most cases that is enough to have all your app exceptions codes unique. But sometimes you can not afford so small
(or large) _base codes_. Such cases are described in the [_experienced_ section]().

## Setup

Let's imagine, you have an exception class called _UserException_. You can throw some exceptions of this class with
different codes. For instance you can throw an exception "_No money - no honey!_" with the code _5_. And you want
this code (and other _UserException_ codes) become _global_...

1. Create an abstract exception class for all your application exceptions. It must extend **GlobalException**.
1. Define inside the constant array **CLASS_CODE_LIST** with _UserException_ class as its element:
```php
use MagicPush\EnterpriseException\GlobalException;

abstract class AppException extends GlobalException
{
    const CLASS_CODE_LIST = [
        UserException::class => 42,
    ];
}
```
1. Extend _UserException_ from _AppException_ (or whatever name you give it).
1. Create _UserException_ objects as usual: global codes will be calculated automaticaly!
```php
$e = new UserException('No money - no honey!', 5);
echo $e->getCode(); // >> 4200005
echo $e->getCodeBase(); // >> 5
```

That's it! From this point every _UserException_ construction with _base codes_ from _1_ to _9999_ will create
exceptions with _global codes_ from _4200001_ to _4299999_. Add more exception classes to **CLASS_CODE_LIST**,
specify unique _class codes_ for each and all those classes will generate exceptions with unique codes!

This repository contains an example script with a few classess configured. Just launch it in the CLI:
```php
php examples/global.php
```

Defining an abstract base exception class is not obligatory but there are reasons for it:
- You can define **CLASS_CODE_LIST** inside _UserException_ and such a setup will work perfectly.
But it is more convenient to control your exceptions _class codes_ by observing all of them in one place.
- You will definitely need such a setup if you wish to use [Parser](parser.md).

## Codes validation

Every _class code_ and _base code_ are validated during a _global code_ construction. By the way, you can construct a
_global code_ manually by calling _getCodeGlobal()_. If any of the codes is considered invalid then a _base code_ is
considered the only exception code and the globalization feature is disabled for that exception. Unless you read the
[_experienced_ section]() you should consider these limits to keep your codes valid:
- A _base code_ must be positive and less than **100000**.
- A _class code_ must be positive and less than the value returned by _getCodeClassMax()_.

## Further reading

- [GlobalException _experienced_ section]()
- [CustomizableException](customizable-exception.md)
- [Parser](parser.md)
