# CustomizableException

(source:
[src/CustomizableException/CustomizableException.php](../../../../src/CustomizableException/CustomizableException.php))

**CustomizableException** can solve many problems (it is called _customizable_ for a reason). One of possible problems
and its solution are described here in this article for some of the class capabilities illustration. Read the
[_experienced_ section](../experienced/customizable-exception.md) for some other problems examples and the rest of
the functionality description.

**CustomizableException** extends **GlobalException**. But you don't need to bother reading **GlobalException**
[documentation](global-exception.md) now - it is optional to configure and you may ignore it completely if you want
to use **CustomizableException** functionality only.

Contents:
- [Problem](#problem)
- [Solution](#solution)
- [How it works](#how-it-works)
    - [If you upgrade your old exceptions](#if-you-upgrade-your-old-exceptions)
- [Setup](#setup)
    - [Frontend message replacement for a certain exception](#frontend-message-replacement-for-a-certain-exception)
    - [Exception context](#exception-context)
    - [Overview example script](#overview-example-script)

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

**CustomizableException** requires exceptions codes (_base codes_ in terms of
[GlobalException](global-exception.md#how-it-works)) as unique keys for properties arrays. So an exception code
becomes obligatory. And more than that: an exception code becomes the _first_ parameter for an exception constructor.
This is the main difference between `CustomizableException::__construct()` and classic `Exception::__construct()`
signatures.

An exception message becomes one of its properties (also is reffered as the _base message_ from this point)
so you don't pass it to a constructor anymore. Instead you can specify exception _details_ - an optional substring
with the data determined during runtime that you find useful - it is added to a constant _base message_:

```php
$e = new UserException(1, 'my details');
echo $e->getMessage(); // >> 'base message (my details)'
```

### If you upgrade your old exceptions

For instance your application has dozens of exception classes which extend your app base exception class already and
you don't want to rewrite all of those at once to fit **CustomizableException** constructor requirements. Probably you
would like to adapt your exception classes one ny one...

No worries! You can set **CustomizableException** as your base exception class' parent immediately without any
trouble - **CustomizableException** provides you with the dual-mode constructor. If you pass a non-numeric value as the
first argument the constructor will operate like for classic `Exception` (or [GlobalException](global-exception.md)).
And only if you pass a _numeric_ value as the first argument it will be treated as an exception (_base_) code, the
second argument will be treated as exception's _details_ and the exception properties will be used (if exist).

## Setup

Let's imagine, you have an exception class called `UserException`. You have an exception that you can show to a user -
"_Not enough money_" (**12**). Also you have another exception "_The operation is forbidden for unreliable
users_" (**301**) and you don't want users to see it's real message.

1. Make `UserException` class extend **CustomizableException**.
1. Specify exceptions properties in `EXCEPTIONS_PROPERTIES` array:

    ```php
    use MagicPush\EnterpriseException\CustomizableException\CustomizableException;
    
    class UserException extends CustomizableException
    {
        const NOT_ENOUGH_MONEY     = 12;
        const FORBIDDEN_UNRELIABLE = 301;
    
        const EXCEPTIONS_PROPERTIES = [ // that's the central config of your exceptions customization
            self::NOT_ENOUGH_MONEY     => [
                'message' => 'Not enough money',
                'show_fe' => true, // "FE" stands for "Front End"; controls if a user can see a real message
            ],
            self::FORBIDDEN_UNRELIABLE => [
                'message' => 'The operation is forbidden for unreliable users',
                'show_fe' => false,' // may be ommitted because it's the default behavior
            ],
        ];
    }
    ```

1. Throw exceptions with corresponding codes; add details if needed:

    ```php
    class BillingService
    {
        // ...
    
        public function checkWallet()
        {
            // ...
            
            if ($this->price > $this->user->money_available) {
                throw new UserException(
                    UserException::NOT_ENOUGH_MONEY,
                    'you need to add $' . ($this->price - $this->user->money_available)
                );
            }
            if ($this->user->is_unreliable) {
                throw new UserException(UserException::FORBIDDEN_UNRELIABLE);
            }
        }
    ```

1. Handle exceptions:

    ```php
    try {
        // ...
        
        $billing_service->checkWallet();
    } catch (CustomizableException $e) {
        $error_message = $e->getMessageFe(); // show only the messages a user is allowed to see
        // NOT_ENOUGH_MONEY >> 'Not enough money (you need to add $3.05)'
        // FORBIDDEN_UNRELIABLE >> 'error 301', the default user-friendly message replacement
    } finally {
        error_log($e->getMessage()); // or $e->__toString(); log real messages for yourself
        // NOT_ENOUGH_MONEY >> 'Not enough money (you need to add $3.05)'
        // FORBIDDEN_UNRELIABLE >> 'The operation is forbidden for unreliable users'
    }
    ```

`getMessageFe()` method checks if the exception property '_show\_fe_' (in fact the value returned by `canShowFe()`)
equals **true**. If so it returns the same message you get when call `getMessage()`. Otherwise you'll get a replacement
"_error XXX_" where _XXX_ is an exception code (it might be a _global code_ if you enable
[GlobalException](global-exception.md) functionality).

**CustomizableException** also provide you with the translation wrapper `getL10N()`. Visit the
[_experienced_ section](../experienced/customizable-exception.md#translation-wrapper) to know more about it as well
as some other tricks.

### Frontend message replacement for a certain exception

You might want to replace `FORBIDDEN_UNRELIABLE` frontend message with something more specific. Specify
'_message\_fe_' property and turn on '_show\_fe_' flag:

```php
// ...

class UserException extends CustomizableException
{
    // ...

    const EXCEPTIONS_PROPERTIES = [ // that's the central config of your exceptions customization
        // ...
        self::FORBIDDEN_UNRELIABLE => [
            'message'    => 'The operation is forbidden for unreliable users',
            'message_fe' => 'Please verify your email first', // frontend replacement for this exception only
            'show_fe'    => true, // you can enable it now when 'message_fe' property is specified
        ],
        // ...
    ];
}

// =======================

// ... somewhere in your user interface
try {
    // ...
    
    $billing_service->checkWallet();
} catch (CustomizableException $e) {
    $error_message = $e->getMessageFe(); // >> 'Please verify your email first'
} finally {
    error_log($e->getMessage()); // or $e->__toString(); >> 'The operation is forbidden for unreliable users'
}
```

### Exception context

Sometimes you want to add a substring to an exception message describing the circumstances an exception was thrown.
For instance you have two "no money" exceptions with similar messages. But one exception is thrown if a user wants
to activate his/her profile "pro" edition. And another exception is thrown if a user tries to buy a profile skin
(I know you love such things you moneybag ;) ).

You can just create two different exceptions with different messages.
But you also can create only one exception and then add different context in different circumstances:

```php
// enabling profiles "pro" edition...
} catch (CustomizableException $e) {
    $e->setContext('Upgrading your profile');
    $error_message = $e->getMessageFe(); // >> 'Upgrading your profile: Not enough money (you need to add $3.05)'
}

// buying profile skins...
} catch (CustomizableException $e) {
    // $skin_name = 'CoolLook';
    $e->setContext(srpintf('"%s" skin purchasing', $skin_name));
    $error_message = $e->getMessageFe(); // >> '"CoolLook" skin purchasing: Not enough money (you need to add $3.05)'
}
```

But there is more! You can even specify the _default_ context ('_context_' property) and redefine it during runtime
(by calling `setContext()`) only if needed!

```php
// ...

class UserException extends CustomizableException
{
    // ...

    const EXCEPTIONS_PROPERTIES = [ // that's the central config of your exceptions customization
        // ...
        self::NOT_ENOUGH_MONEY     => [
            'context' => 'Unable to purchase a service', // here is the default context
            'message' => 'Not enough money',
            'show_fe' => true,
        ],
        // ...
    ];
}

// buying anything...
} catch (CustomizableException $e) {
    $error_message = $e->getMessageFe(); // >> "Unable to purchase a service: Not enough money (you need to add $3.05)"
    $e->setContext('You've squandedered your funds');
    $error_message = $e->getMessageFe();
    // >> "You've squandedered your funds: Not enough money (you need to add $3.05)"
}
```

### Overview example script

This repository contains an example script with a few classes and exceptions properties configured for a quick review
of the built-in properties. Just launch it in the CLI:

```php
php examples/customizable.php
```

## Further reading

- [CustmoizableException _experienced_ section](../experienced/customizable-exception.md)
- [GlobalException](global-exception.md)
- [Parser](parser.md)
