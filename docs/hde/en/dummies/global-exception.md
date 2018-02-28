# GlobalException

([src/GlobalException.php](../../../../src/GlobalException.php))

- [Problem](#Problem)
- [Solution](#Solution)
- [How it works](#how-it-works)

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
automated strategy... And here comes the _**GlobalException**_ solution.

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
- _Class code_ - that's the integer which transforms base codes into global ones becoming their "higher" part. It is
configured in an exception class config.

For instance **GlobalException** can create an exception with it's code _45600123_ based on the base code _123_ and the
class code _456_.

In most cases that is enough to have all your app exceptions codes unique. But sometimes you can not afford so small
(or large) base codes. Such cases are described in the [guide for experienced]().
