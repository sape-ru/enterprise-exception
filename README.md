# EnterpriseException

A PHP library for large web applications exceptions management.

This readme is written in the _TL;DR_-style. The full-scale guide "_HOWTO for dummies and experts_" is planned
to be written as separate document (possibly wiki-style pages) in near future.

- [What is it for](#what-is-it-for)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage or HOWTO lite edition](#usage-or-howto-lite-edition)
- [Near future plans](#near-future-plans)
- [If you want to contribute](#if-you-want-to-contribute)

## What is it for

### Exceptions codes globalization

For instance your project has exception classes with identical exceptions codes:
- class _AuthException_ can throw the exception "invalid password" with the code **2**;
- class _BillingException_ can throw the exception "not enough money" with the same code **2**.

And you want those codes become unique so you could differ those exceptions while parsing logs. Or it would be handy
for your API clients' robots. If this is your case then use the [GlobalException](#for-codes-globalization).

### Exceptions customization

- Do you want to specify frontend and system version of exceptions messages?
- Do you want to show your users not real exceptions messages but stubs like "_Please contact our support team to help
with the error XXXXXX_" for low-level exceptions?
- Do you want to add any other custom properties to your exceptions and handle them as you wish?

If you answered "yes" for any question then you probably would like to use the
[CustomizableException](#for-exceptions-customization).

### Customized exceptions parsing

If you already use the
[CustomizableException](#exceptions-customization) and have dozens of customizable exception classes then you probably
would like to:
- print a full list of your exceptions with their properties
- filter your exceptions (for instance to show only user-visible exceptions)
- validate your globalized exceptions codes

If so then you should use the [Parser](#for-customizable-exceptions-parsing).

## Requirements
- Core PHP v7.0+
    - at least there is no incompatible code for v7.1 and v7.2

## Installation

There are several ways to install the library:
- GIT: clone / fork / the repository, include as submodule etc.
- Manual #1: Download the repository :smile:.
- Manual #2 (critical source only):
    - Download the [GlobalException.php](src/GlobalException.php) file for the exceptions codes globalization
    feature.
    - Download the file previously mentioned and the [CustomizableException](src/CustomizableException) folder for
    exceptions customizing and parsing functionality.

## Usage or HOWTO lite edition

Every structural element is described in its PHPDoc.

There are [example classes](examples/resources) and [demo scripts](examples) for quick features overview.
You can launch demos via CLI:

```
php examples/global.php # or any other *.php file
```

The short usage guide is:

### For codes globalization

1. Your base exception class must extend [GlobalException](src/GlobalException.php).
1. The classes you want to globalize must extend your base exception class (previously mentioned).
1. You must redefine the **CLASS_CODE_LIST** constant array in your base exception class with your exception classes
fully qualified names as keys and their own (class) codes as values.
1. That's it! Now your exceptions have unique codes!

### For exceptions customization

That's trickier; you should read the PHPDoc (or the full guide when it is written) for better understanding.
But for a start:

1. Your base exception class must extend [CustomizableException](src/CustomizableException/CustomizableException.php).
1. All the classes you want customizable exceptions for must extend your base exception class (previously mentioned).
1. You must redefine the **EXCEPTIONS_PROPERTIES** constant array in each class you want customize exceptions for.
See the array PHPDoc for possible keys.
1. At this moment you must throw your exceptions with a new constructor signature: an exception code must be the first
parameter (obligatory). If the first parameter is _not_ numeric/specified then the customization config will be ignored.
1. Use corresponding methods to fully enjoy your exceptions customization!

### For customizable exceptions parsing

1. Do all the things for [exceptions customization](#for-exceptions-customization).
1. You must mention all the classes you want to be parsed in the **CLASS_CODE_LIST** constant array in your base
exception class. Like for the [globalization](#for-codes-globalization), but you can specify **0** for class codes if
you don't want to use the globalization feature itself.
1. Call the [Parser](src/CustomizableException/Parser.php)::parse() method with options and/or filters you desire!

## Near future plans

- **2018-03-1X**: _beta testing and issue #1 resolving_.

Replacing current (v1.0) exceptions management system in the [@sape-ru](https://github.com/sape-ru) project with this
repository code. More than thousand of exceptions defined in more than hundred exception classes - that's a good
opportunity to test this library and possibly improve it during the process.

- **2018 late spring**: _the "HOWTO for dummies and experts" guide_.

## If you want to contribute

Feel free to read the [contribution guide](CONTRIBUTING.md).
