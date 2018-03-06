# Parser

(browse: [src/CustomizableException/Parser.php](../../../../src/CustomizableException/Parser.php))

**Parser**_::parse()_ is used for... you won't believe it... _parsing_ [GlobalException](global-exception.md) and
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
- [Further reading](#further-reading)

## What is it for

For several reasons. Here is an illustration from the real world.

A large web application my team continiously develop already contains more than _140_ classes with more than _1300_
exceptions in total (and counting). When you have so many exceptions (we also use
[GlobalException](global-exception.md) functionality) you want to be sure there will be no exceptions codes
duplicates.

Also when we show to users an exception code only (a real message is hidden for front end) it is very great if our
moderators can type an exception code, press a button and discover the real meaning so they can decide what to do next
\- sometimes we hide real messages from users just because such exceptions describe internal politics restrictions.

Another reason is our user API. We would like to show there a list of possible exceptions codes and their messages.
But such a list must contain FE-visible exceptions only. 

We needed to filter and validate our exceptions somehow. So here comes **Parser**!

## How it works

**Parser** reads **CLASS_CODE_LIST** for classes to check. Every class from this list is parsed first then its
exceptions (and their properties) are processed. At first `$filters` reduce the data to parse and then class and
exception codes validations are executed. Finally a formatted output is composed depending on `$options` you
specify.

## Prerequisites

If you want to use **Parser** you must have a base exception class that extends **CustomizableException** and has
**CLASS_CODE_LIST** defined with all exceptions classess you wish to parse (even if you want to parse
[GlobalException](global-exception.md) descendants only). If you don't want to use
[GlobalException](global-exception.md) functionality you can disable it by specifying **0** as a class code - such
classess are treated as classic exceptions classes (no codes validation and _global codes_ calculation).

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

Before you start parsing your exceptions classes make sure that every class is loaded first. It's obligatory for
**Parser** to do anything. Load all classes manually or install an autoloader and configure it properly. You don't
need to load all classes at once before calling **Parser** though. Read the [_experienced_ section]() to know more.

## Filtering classes

Every class filter has suffixes _ex_ and _in_ meaning classes exclusion and inclusion accordingly.

If you want to parse classess with specific codes only use '_class_code_list_in_' filter. For instance
`$filters['class_code_list_in'] = [2, 3]` will make **Parser** to process only classes with their codes **2** and
**3**. Or if you want to parse every but the classes with codes **2** and **3** then use '_class_code_list\_**ex**_'
filter the same way.

The filters '_class_name_part_list_ex_' and '_class_name_part_list_in_' allow you to exclude or include classes by
substrings found in their fully qualified names parts (case sensitive). For instance
`$filters['class_name_part_list_in'] = ['Billing']` will make **Parser** to process only classes which fully
qualified names contain a substring '_Billing_'.

### Filtering classes by sections

You can also filter your classes by _sections_ (case sensitive). A _section_ is just a string. For instance you have
_OddAppException_, _FriendlyAppException_ and _PartnerAppException_ classes. All these classes represent exceptions
from different partners. And all of them are the exceptions being thrown by your _partners_ applications. So if you
want to filter all _partners_ exceptions just give them all the same _section_ name ('_partner_', '_EXTERNAL_' or
anything else you wish). Then you can just filter all these classes at once:
`$filters['class_name_part_list_in'] = ['partner']` (or exclude them by specifying partners section name to
'_class_name_part_list\_**ex**_' filter).

You can specify sections by two ways:
- Define **CLASS_SECTION_LIST** array the same way you define **CLASS_CODE_LIST** but instead codes (as
elements values) there should be any strings (or anything else that can be casted to a string) you wish.
- Define **CLASS_SECTION_DEFAULT** constant with a string (or anything else that can be casted to a string).
Any class with this constant and all that class descendants will belong to this section.

## Validating classes

The validation is executed only for classes with [GlobalException](global-exception.md) functionality enabled
(`CLASS_CODE_LIST` element value is not equal to zero).

Firstly a class code is validated via `GlobalException::validateCodeClass()`. An exception is thrown if a class code
is not valid.

Secondly a potential _global code_ is generated via `GlobalException::getCodeGlobal()` by passing **1** as the
_base code_. If any other classes generate the same potential _global code_ (so dublicate _global codes_ are possible)
then an exception is thrown.

If you specify '_ignore_invalid_' option then no exception is thrown, the invalid classes are just skipped
instead (inluding all their exceptions).

## Filtering exceptions

## Validating exceptions

## Data returned

## Further reading

- [Parser _experienced_ section]()
- [GlobalException](global-exception.md)
- [CustomizableException](customizable-exception.md)
