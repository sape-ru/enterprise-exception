# Mastering Parser

(source: [src/CustomizableException/Parser.php](../../../../src/CustomizableException/Parser.php))

This article describes some ways of expanding [Parser](../dummies/parser.md) capabilities. If you're not familiar with
the library basics then [go learn them](../dummies/about.md) first!

Contents:
- [Loading a single class at once](#loading-a-single-class-at-once)
- [Exceptions custom filters](#exceptions-custom-filters)
- [Customizing the output](#customizing-the-output)

## Loading a single class at once

**Parser** can throw an exception for any validation error. Unless any of '_ignore_invalid_' and '_add_errors_' options
are enabled the parsing process will be terminated. In this case not all exception classes are processed. And so you
don't need to load all your exception classes at once.

**Parser** provides you with `loadClass()`. This method is called for loading `$config_class_name` class and every
exception class you specify in `CLASS_CODE_LIST` before that class is processed. Initially this method does nothing
but you can redefine it to load a class by its qualified namespaced name.

## Exceptions custom filters

**Parser** already has built-in [exceptions filters](../dummies/parser.md#filtering-exceptions). If you want to add
some new filters (for instance if you add new properties support for `EXCEPTIONS_PROPERTIES`) you should consider
redefining `needFilterException()`. This method is called after all built-in filters are processed; it answers the
question if an exception must be **ex**cluded from processing.

Initially `needFilterException()` always returns **false** (so _no_ exceptions must be excluded by the method). Its
parameters are `$filters`, an exception _base code_ and `$options`; `$filters` and `$options` arrays are the arrays
specified for `parse()`, they are passed unchanged by references.

## Customizing the output

If **Parser** successfully finishes its job it returns [parsed data](../dummies/parser.md#data-returned) (unless
'_no_data_' option is enabled). The output composition built-in agorithm is implemented in `addExceptionData()`.
Consider redefining this method if you want to change the output in any way (for instance by adding more data to the
output or altering the output format).

`addExceptionData()` accepts:
- `$parsed_data` - the array with already parsed data which will be returned in the end by `parse()`;
is passed by a reference.
- `$options` - the array of options specified for `parse()`; is passed unchanged by a reference.
- `$properties` - an exception properties array (one of `EXCEPTIONS_PROPERTIES` elements); is passed unchanged by a
reference.
- `$basis` - an exception data array:
    - `'base_code'` => an exception (_base_) code
    - `'class_code'` => an exception class code
    - `'class_name'` => a qualified namespaced exception class name

## Further reading

- [Mastering GlobalException](global-exception.md)
- [Mastering CustomizableException](customizable-exception.md)
- [Parser basics](../dummies/customizable-exception.md)
