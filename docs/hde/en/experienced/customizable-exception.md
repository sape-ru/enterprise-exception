# Mastering CustomizableException

(source:
[src/CustomizableException/CustomizableException.php](../../../../src/CustomizableException/CustomizableException.php))

This article describes some ways of tweaking [CustomizableException](../dummies/customizable-exception.md).
If you're not familiar with the library basics then [go read it](../dummies/about.md) first!

Contents:
- [Full message composer](#full-message-composer)
- [Frontend message stub](#frontend-message-stub)
- [Translation wrapper](#translation-wrapper)

## Full message composer

## Frontend message stub

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
- when you [change an exception context](../dummies/customizable-exception.md#exception-context) (`$locale == null`)
during runtime;
- in `getMessageFeStub()` (`$locale == null`) if you don't [redefine it](#frontend-message-stub);
- in [Parser](../dummies/parser.md) (`$locale == $options['locale']`) for exceptions messages parts while composing 
parsed data output - you can specify any locale you wish in '_locale_' option (the default value is
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

## Further reading

- [CustomizableException basics](../dummies/customizable-exception.md)
- [Mastering GLobalException](global-exception.md)
- [Mastering Parser]()
