# Mastering CustomizableException

(source:
[src/CustomizableException/CustomizableException.php](../../../../src/CustomizableException/CustomizableException.php))

This article describes some ways of tweaking [CustomizableException](../dummies/customizable-exception.md).
If you're not familiar with the library basics then [go read it](../dummies/about.md) first!

Firstly take into your consideration that in addition to already built-in properties you can expand
`EXCEPTIONS_PROPERTIES` with as many new properties as you wish!

Contents:
- [Full message composer](#full-message-composer)
- [Frontend message stub](#frontend-message-stub)
- [Translation wrapper](#translation-wrapper)
- [Default base message](#default-base-message)
- [Unkown exception](#unkown-exception)

## Full message composer

If a classic exception message is just a string you specify as its constructor first argument then an exception
_full message_ is [CustomizableException](../dummies/customizable-exception.md) message composed by
`getMessageComposed()` from several parts (such as _context_ and _base message_). This method is called implicitly:
- in the constructor for the [_system_ version](#translation-wrapper);
- in `getMessageFe()` for the [_frontend_ version](#translation-wrapper) (if an exception
is configured as frontend-visible);
- in [Parser](../dummies/parser.md) as a part of parsed data output (if `$options['is_extended']` equals `false`).

Initially this method composes a _full message_ in a format:
`[context if exists: ]base message[ (details if exist)]`:

```php
class UserException extends CustomizableException
{
    const EXCEPTIONS_PROPERTIES = [
        12 => [
            'message' => 'Not enough money',
        ],
    ];
}

$e = new UserException(12);
echo $e->getMessage(); // >> Not enough money

$e = new UserException(12, 'you need to add $3.05');
$e->setContext('Upgrading your profile');
echo $e->getMessage(); // >> Upgrading your profile: Not enough money (you need to add $3.05)
```

Redefine `getMessageComposed()` if you want to change the _full message_ format or to add some new parts.

## Frontend message stub

When you call `getMessageFe()` it checks if `canShowFe()` returns `true`. If it's so then the _full message_
[_frontend_ version](#translation-wrapper) is composed and returned. Otherwise `getMessageFeStub()` value is returned.
It is useful when you can't show a user the real reason but want to show some base info like an exception code.

Initially `getMessageFeStub()` returns a string "_error XXX_" where _XXX_ is an exception
[formatted code](global-exception.md#global-codes-formatting). But you can redefine it to return your support team
contact email / page link or anything else you consider non-confidential and user-friendly.

## Translation wrapper

[CustomizableException](../dummies/customizable-exception.md) and [Parser](../dummies/parser.md) basics articles
mention the translation wrapper `getL10N()`. Initially this public static method just returns the input string
_as is_. But you can redefine and extend this method with your application translation mechanism.

This method has two parameters. The first is the string to translate. The second is the translation locale. You can
specify here not only the locale name string but also `false` or `null`:
- `false` value should be treated as disabling any translation. It is useful when you want to be sure a string
passed to the wrapper is returned _as is_. Sometimes you can't control if the wrapper is used or not so `false`
value might be the solution for this case.
- `null` value should mean that the locale name is determined automatically. For instance it can be a current user
session locale. It is useful when you show your users an exception message but don't need to specify a current locale
explicitly.

### System locale

**CustomizableException** provides you with `L10N_SYSTEM_LOCALE`. This constant is mainly used for implicit exceptions
messages parts translations into _system_ versions.

By default this constant is set to '_en_'. But you can redefine it with any value `getL10N()` can support as `$locale`
value.

### Usage

You can call this wrapper manually if you wish. But this wrapper is also called implicitly for your convenience:
- when you [change an exception context](../dummies/customizable-exception.md#exception-context) (`$locale` is set to
`null`) during runtime;
- in `getMessageFeStub()` (`$locale` is set to `null`) for the _'error'_ substring if you don't
[redefine it](#frontend-message-stub);
- in [Parser](../dummies/parser.md) (`$locale` is set to `$options['locale']`) for exceptions messages parts while
composing parsed data output - you can specify any locale you wish in '_locale_' option (the default value is
`L10N_SYSTEM_LOCALE`).

The constructor calls `getL10N` twice - for _system_ and _frontend_ version of an exception _context_ and _base
message_:
1. The _system_ version is made by passing `L10N_SYSTEM_LOCALE` as `$locale` value. The
[composed message](#full-message-composer) is passed then to a parent constructor so you can get the original message
for logs or admin interfaces.
2. The _frontend_ version is made by passing `null` as `$locale` value. Then the _context_ and _base message_
are accessible separately via `getContext()` and `getMessageBase()` accordingly; also these parts are used for the
frontend [full message](#full-message-composer) composed in `getMessageFe()` (if an exception message is configured
as frontend-visible).

## Default base message

If an exception properties has no '_message_' property then the constructor considers such an exception as non-user (
`canSowFe()` returns `false`) and specifies the value returned by `getMessageDefault()` as an exception _base message_.

Initially such a message has the format: `CustomizableException XXX (YYY::ZZZ)` where _XXX_ is an exception
[formatted code](global-exception.md#global-codes-formatting), _YYY_ is an exception fully qualified class name and
_ZZZ_ is an exception code (the _base code_ if you use [GlobalException](../dummies/global-exception.md)
functionality).

Redefine `getMessageDefault()` at your will to fit your logs or admin interfaces undefined message error format.

## Unkown exception

If there is no `EXCEPTIONS_PROPERTIES` element for an exception you throw then the constructor considers such an
exception as non-user (`canSowFe()` returns `false`) and specifies the value returned by `getMessageUnknown()` as an
exception _base message_.

Initially such a message has the format: `unknown base code XXX for CustomizableException (YYY)` where _XXX_ is an
exception code (the _base code_ if you use [GlobalException](../dummies/global-exception.md) functionality) and
_YYY_ is an exception fully qualified class name.

Redefine `getMessageUnknown()` at your will to fit your logs or admin interfaces unkown error format.

## Further reading

- [CustomizableException basics](../dummies/customizable-exception.md)
- [Mastering GLobalException](global-exception.md)
- [Mastering Parser]()
