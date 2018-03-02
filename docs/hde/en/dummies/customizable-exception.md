# CustomizableException

(browse:
[src/CustomizableException/CustomizableException.php](../../../../src/CustomizableException/CustomizableException.php))

**CustomizableException** can solve many problems (it is called _customizable_ for a reason). One of possible problems
and its solution are described here in this article for some of the class capabilities illustration. Read the
[_experienced_ section]() for some other problems examples and the rest of the functionality description.

**CustomizableException** extends **GlobalException**. But you don't need to bother reading **GlobalException**
[documentation](global-exception.md) now - it is _not_ obligatory to configure and you may ignore it completely
if you want to use just **CustomizableException** functionality _only_.

Contents:
- [Problem](#problem)
- [Solution](#solution)
- [How it works](#how-it-works)
- [Setup](#setup)
- [Further reading](#further-reading)

## Problem

Imagine that your application has two groups of exceptions:
1. Exceptions which you can show to users. Users read the exceptions messages, understand (you hope so) their own
mistakes or circumstances and react on those accordingly.
1. Exceptions which describe low level logic or some confidential details you don't want to be exposed to users. You
want to secretly log such exceptions in your private errors log file or database table or any other container. But you
also want to let users notify you about certain exceptions without exposing their real messages.

## Solution

It would be nice to set a config controlling which exceptions can be visible for users and which must replace their
messages with a non-confidential general descriptions. Such a replacement may be exceptions codes and/or a link to your
application support page.

## How it works

**CustomizableException** provides you with the flag which controls if an exception real message can be shown to a
user. This flag is just one of possible exceptions properties. But how exactly can we link properties with certain
exceptions?

**CustomizableException** requires exceptions codes (_base codes_ in terms of **GlobalException**) as unique keys for
properties arrays. So an exception code becomes obligatory. And more than that: an exception code becomes the _first_
parameter for an exception constructor. This is the main difference between **CustomizableException** and classic
_Exception_ _\_\_construct()_ signatures.

An exception message becomes one of its properties (and is also reffered as the _base message_ from this point)
so you don't pass it to a constructor anymore. Instead you can specify exception _details_ - a substring with the data
determined during runtime that you find useful - it is added to a constant _base message_.

## Setup

## Further reading

- [CustmoizableException _experienced_ section]()
- [GlobalException](global-exception.md)
- [Parser](parser.md)
