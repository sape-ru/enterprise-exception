# Parser

(source: [src/CustomizableException/Parser.php](../../../../src/CustomizableException/Parser.php))

`Parser::parse()` is used for... you won't believe it... _parsing_ [GlobalException](global-exception.md) and
[CustomizableException](customizable-exception.md) descendants.
![You don't say!](../../../assets/images/you-dont-say.jpg)

Contents:
- [What is it for](#what-is-it-for)
- [How it works](#how-it-works)
- [Prerequisites](#prerequisites)
- [Filtering classes](#filtering-classes)
    - [... by sections](#filtering-classes-by-sections)
- [Validating classes](#validating-classes)
- [Filtering exceptions](#filtering-exceptions)
- [Validating exceptions](#validating-exceptions)
- [Data returned](#data-returned)
    - [Overview example script](#overview-example-script)

## What is it for

For several reasons. Here is an illustration from the real world.

A large web application my team continiously develop already contains more than _140_ classes with more than _1300_
exceptions in total (and counting). When you have so many exceptions (we also use
[GlobalException](global-exception.md#setup) functionality) you want to be sure there will be no exceptions codes
duplicates.

Also when we show to users an exception code only (a real message is hidden for front end) it is very great if our
moderators can type an exception code, press a button and discover the real meaning so they can decide what to do
next - sometimes we hide real messages from users just because such exceptions describe internal policies restrictions.

Another reason is our user API. We would like to show there a list of possible exceptions codes and their messages.
But such a list must contain FE-visible exceptions only. 

We needed to filter and validate our exceptions somehow. So here comes **Parser**!

## How it works

**Parser** reads `CLASS_CODE_LIST` for classes to check. Every class from this list is parsed first then its
exceptions (and their properties) are processed. At first `$filters` reduce the data to parse and then class and
exception codes validations are executed. Finally a formatted output is composed depending on `$options` you
specify.

## Prerequisites

If you want to use **Parser** you must have a base exception class that extends
[CustomizableException](customizable-exception.md) and has `CLASS_CODE_LIST` defined with all exception classes you
wish to parse (even if you want to parse [GlobalException](global-exception.md) descendants only). If you don't want
to use [GlobalException](global-exception.md#setup) functionality you can disable it in `CLASS_CODE_LIST` by
specifying **0** as a _class code_ - such classes are treated as classic exception classes (no codes validation and
_global codes_ calculation).

```php
use MagicPush\EnterpriseException\CustomizableException\CustomizableException;

abstract class AppException extends CustomizableException
{
    const CLASS_CODE_LIST = [
        UserException::class    => 1,
        ProfileException::class => 2,
        BillingException::class => 0, // globalization is disabled
        // ...
    ];
}

// somewhere else...
$options = [
    // ...
];
$filters = [
    // ...
];
$exceptions_parsed = Parser::parse(AppException::class, $options, $filters);
```

Before you start parsing your exception classes make sure that every class is loaded first. It's obligatory for
**Parser** to do anything. Load all classes manually or install an autoloader and configure it properly. You don't
need to load all classes at once before calling **Parser** though. Read the
[_experienced_ section](../experienced/parser.md#loading-a-single-class-at-once) to know more.

## Filtering classes

Every class filter has suffixes _ex_ and _in_ meaning classes exclusion and inclusion accordingly.

If you want to parse classes with specific codes only use '_class_code_list_in_' filter. For instance
`$filters['class_code_list_in'] = [2, 3]` will make **Parser** to process only classes with their codes **2** and
**3**. Or if you want to parse every but the classes with codes **2** and **3** then use '_class_code_list\_**ex**_'
filter the same way.

The filters '_class_name_part_list_ex_' and '_class_name_part_list_in_' allow you to exclude or include classes by
substrings found in their fully qualified names parts (case sensitive). For instance
`$filters['class_name_part_list_in'] = ['Billing']` will make **Parser** to process only classes which fully
qualified names contain a substring '_Billing_'.

### Filtering classes by sections

You can also filter your classes by _sections_ (case sensitive). A _section_ is just a string. For instance you have
`OddAppException`, `FriendlyAppException` and `PartnerAppException` classes. All these classes represent exceptions
from different partners. And all of them are the exceptions being thrown by your _partners_ applications. So if you
want to filter all _partners_ exceptions just give them all the same _section_ name ('_partner_', '_EXTERNAL_' or
anything else you wish). Then you can just filter all these classes at once:
`$filters['class_name_part_list_in'] = ['partner']` (or exclude them by specifying partners section name to
'_class_name_part_list\_**ex**_' filter).

You can specify sections by two ways:
- Define `CLASS_SECTION_LIST` array the same way you define `CLASS_CODE_LIST` but instead codes (as
elements values) there should be any strings (or anything else that can be casted to a string) you wish.
- Define `CLASS_SECTION_DEFAULT` constant with a string (or anything else that can be casted to a string).
Any class with this constant and all that class descendants will belong to this section.

## Validating classes

The validation is executed only for classes with [GlobalException](global-exception.md#codes-validation)
functionality enabled (`CLASS_CODE_LIST` element value is not equal to zero).

Firstly a class code is validated via `GlobalException::validateCodeClass()`. An exception is thrown if a class code
is not valid.

Secondly a potential _global code_ is generated via `GlobalException::getCodeGlobal()` by passing **1** as the
_base code_. If any other classes generate the same potential _global code_ (so dublicate _global codes_ are possible)
then an exception is thrown.

If you specify '_add_errors_' or '_ignore_invalid_' option then no exception is thrown, the invalid classes are just
skipped instead (inluding all their exceptions).

## Filtering exceptions

Exceptions from [CustomizableException](customizable-exception.md) descendants only can be filtered.

Almost every exception filter has suffixes _ex_ and _in_ meaning exceptions exclusion and inclusion accordingly.

If you want to parse only exceptions with specific codes (_base codes_ if the exception classes use
[GlobalException](global-exception.md#how-it-works) functionality) use '_base_code_list_in_' filter. For instance
`$filters['base_code_list_in'] = [11, 12, 14]` will make **Parser** to process only exceptions with their (_base_)
codes **11**, **12** and **14**. Or if you want to parse every but the exceptions with (_base_) codes **11**, **12**
and **14** then use '_base_code_list\_**ex**_' filter the same way.

Also you can filter exceptions (_base_) codes by ranges (or just borders if you specify only "left" or "right"
borders):
- '_base_code_from_in_' - for the **in**clusion range start
- '_base_code_from_ex_' - for the **ex**clusion range start
- '_base_code_to_in_' - for the **in**clusion range end
- '_base_code_to_ex_' - for the **ex**clusion range end

There is also a property-related filter '_show_fe_'. If you want to generate a list of exceptions messages visible for
users you can set '_show_fe_' to **true**. If you want the opposite list containing only exceptions messages which are
not supposed to be seen by users then set '_show_fe_' to **false**.

If you want to add some your own filters you should read the
[_experienced_ section](../experienced/parser.md#exceptions-custom-filters) for instructions.

## Validating exceptions

Exceptions validation is executed only for exceptions from [CustomizableException](customizable-exception.md)
descendants with [GlobalException](global-exception.md#setup) functionality enabled (`CLASS_CODE_LIST` element value
is not equal to zero).

Every exception (_base_) code is validated via `GlobalException::validateCodeBase()`. An exception is thrown if an
exception (_base_) code is not valid.

If you specify '_add_errors_' or '_ignore_invalid_' option then no exception is thrown, the invalid exceptions are just
skipped instead.

## Data returned

The returned data is based only on classes and exceptions which "survive" filtering and validation. Also the returned
data first level keys depend on classes.

If parsed exception classes use [GlobalException](global-exception.md#setup) functionality then all those exceptions
data will be put in an array under '_\_\_global_' key. That array's elements will represent each _global exception_
with its _global code_ as a key:

```php
array (
    '__global' => 
    array (
        100001 => // ...
        100002 => // ...
        // ...
        // here comes codes from another global class:
        200001 => // ...
        200002 => // ...
        // ...
    ),
    // ...
```

If parsed exception classes doesn't use [GlobalException](global-exception.md#setup) functionality then exceptions
data will be put in an array under each exception class fully qualified name as a key. That array's elements will
represent each exception in that class with its code as a key:

```php
array (
    'MyCoolException' => 
    array (
        1  => // ...
        2  => // ...
        // ...
    ),
    'AnotherCoolException' =>
    array ( // ...
        100 => // ...
        101 => // ...
        200 => // ...
    ),
    // ...
```

None of these first level keys will exist in the returned data if '_no_data_' option is specified. It is useful
when you want only to validate your _global_ exceptions.

If you turn on '_add_errors_' option then [classes validation](#validating-classes) and
[exceptions validation](#validating-exceptions) errors will be suppressed (like with '_ignore_invalid_' option) but
those validation errors messages will be added to the returned data as an array under '_\_\_errors_' first level key:

```php
array (
  // ...
  '__errors' => 
  array (
    0 => 'The base code -5 for "MyCoolException" must range from 1 to 99999.',
    1 => 'The base code 250000 for "AnotherCoolException" must range from 1 to 99999.',
    2 => 'Same potential global code 500001 generated for "CrazyException" and "MadException".',
    // ...
  ),
)
```

By default a parsed exception data is its system _full message_ based on '_context_' and '_message_' properties (see
[CustomizableException setup](customizable-exception.md#setup) for more info).
You can replace '_message_' substrings with '_message_fe_' substrings (if exist) by turning on '_use_message_fe_'
option:

```php
array (
  '__global' => 
  array (
    100001 => 'Something bad happened',
    100002 => 'Upgrading a profile: not enough money', // has 'context' property
     // the next exception has 'message_fe' property:
    100003 => 'Please verify your email first', // is returned if 'use_message_fe' option is enabled
    // and now the same exception without 'use_message_fe' option
 // 100003 => 'The operation is forbidden for unreliable users',
    // ...
  ),
  // ...
)
```

If just exceptions messages isn't enough and you need all exceptions data then enable '_is_extended_' option:

```php
array (
  '__global' => 
  array (
    100001 => 
    array (
      'base_code' => 1,
      'class_code' => 1,
      'class_name' => 'MyCoolException',
      'class_section' => '',
      'context' => '',
      'message' => 'Something bad happened,
      'message_fe' => '',
      'show_fe' => false,
    ),
    100002 => 
    array (
      'base_code' => 2,
      'class_code' => 1,
      'class_name' => 'MyCoolException',
      'class_section' => '',
      'context' => 'Upgrading a profile',
      'message' => 'not enough money',
      'message_fe' => '',
      'show_fe' => true,
    ),
    100003 => 
    array (
      'base_code' => 3,
      'class_code' => 1,
      'class_name' => 'MyCoolException',
      'class_section' => '',
      'context' => '=',
      'message' => 'The operation is forbidden for unreliable users',
      'message_fe' => 'Please verify your email first',
      'show_fe' => true,
    ),
    // ...
  ),
  // ...
)
```

There is '_locale_' option wich is passed to `CustomizableException::getL10N()` (the
[translation wrapper](../experienced/customizable-exception.md#translation-wrapper)) for each exception message part
translation.

Also you can change the output format entirely (or maybe add some more options to customize it). Visit the
[_experienced_ section](../experienced/parser.md#customizing-the-output) to know more.

### Overview example script

This repository contains an example script with a few classes and exceptions properties configured for a quick review
of the parsed results. Just launch it in the CLI:

```php
php examples/parser.php
```

## Further reading

- [Parser _experienced_ section](../experienced/parser.md)
- [GlobalException](global-exception.md)
- [CustomizableException](customizable-exception.md)
