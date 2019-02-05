<?php
namespace MagicPush\EnterpriseException\CustomizableException;

use MagicPush\EnterpriseException\GlobalException;

/**
 * The exception class used for customizing its exceptions with additional properties.
 *
 * There may be any properties for an exception you can imagine and implement in the class successors.
 * For instance there is an implementation of an exception message frontend version already. And there is more -
 * read EXCEPTIONS_PROPERTIES description and other class elements mentioned in the config for more info.
 * Also it provides you with an ability to specify only an exception code: a message will be taken from
 * the properties config - EXCEPTIONS_PROPERTIES. Read the constructor documentation to know more.
 *
 * CLASS_SECTION_LIST and CLASS_SECTION_DEFAULT are an addition to let Parser filtrate classes by sections
 * (read Parser documentation in its own class file).
 *
 * You must extend this class with your project base exception class (PBEC). All your exception classes must extend
 * that PBEC and have their EXCEPTIONS_PROPERTIES configs set up properly. After that you must call the constructor
 * in customizable mode - via passing exception (base) code as the first argument. Otherwise this class will act
 * like the classic \Exception class.
 *
 * Also if you want to use exceptions codes globalization feature or parse your exception classes then set up
 * CLASS_CODE_LIST config in PBEC (read GlobalException and Parser documentation for more info).
 *
 * @see CustomizableException::EXCEPTIONS_PROPERTIES    For checking / setting up the exceptions properties config.
 * @see CustomizableException::__construct()            For the constructor documentation.
 * @see CustomizableException::CLASS_SECTION_LIST       For checking / setting certain classes sections.
 * @see CustomizableException::CLASS_SECTION_DEFAULT    For checking / setting the default section.
 * @see Parser::parse()                                 For the customizable exceptions Parser documentation.
 * @see GlobalException::CLASS_CODE_LIST                For checking / setting up the globalization config.
 *
 * @package CustomizableException
 *
 * @author Kirill Ulanovskiy <xerxes.home@gmail.com>
 */
abstract class CustomizableException extends GlobalException
{
    /**
     * @var string A class default section.
     *
     * This value is used for Parser sections filtering if a class name is not found
     * in CLASS_SECTION_LIST config.
     *
     * @see CustomizableException::CLASS_SECTION_LIST   For checking / setting certain classes sections.
     * @see Parser::parse()                             For the customizable exceptions Parser documentation.
     */
    const CLASS_SECTION_DEFAULT = '';
    /**
     * @var array|string[] Parser sections filter config.
     *
     * This config is used by Parser to filter exception classes by sections you can set.
     * Each element of the config represents an exception class (a subclass of CustomizableException).
     * The element key must be a qualified namespaced class name (AnyClass::class) or it will be ignored -
     * not found while comparing with a qualified namespaced class name being checked for filtering.
     * The element value may be any string (or a number) you prefer to use as a section name. Just use the same string
     * in Parser section filter to include or exclude any class linked to that section.
     * If you want to set a default section to all classes not specified in this config then redefine
     * CLASS_SECTION_DEFAULT constant.
     *
     * @see CustomizableException::CLASS_SECTION_DEFAULT    For checking / setting the default section.
     * @see Parser::parse()                                 For the customizable exceptions Parser documentation.
     */
    const CLASS_SECTION_LIST = [];

    /**
     * @var array The central CustomizableException config for specifying exceptions' properties.
     *
     * You must set up this config for every subclass of CustomizableException you would like to throw.
     * Each element of the array represents a class exception with its (int) code
     * (base code if you use globalization feature) as a key and its (array) properties as a value.
     * Some properties may be used during an exception construction, some others - during runtime,
     * some - for static usage. It's up to your imagination only.
     * CustomizableException already implements usage of the properties:
     * <pre>
     *  * context       => (string) An optional exception context (where it happened; the subject).
     *                      Is stored in an exception $_context property and then added
     *                      to 'message' config property via ::getMessageComposed().
     *                      Acts like a system version if replaced during runtime via ::setContext() - can be
     *                      returned then only via \Exception finalized methods
     *                      (like ::getCode() or ::__toString()).
     *  * message       => (string) An exception base message (what you usually specify as a message to throw).
     *                      If not set ::getMessageDefault() string is used instead as a default value.
     *                      It is stored in an exception $_message_base property and then passed to the parent
     *                      constructor combined with other exception message parts via ::getMessageComposed().
     *                      Acts like a system version if 'message_fe' config property is set - can be returned
     *                      then only via \Exception finalized methods (like ::getCode() or ::__toString()).
     *  * message_fe    => (string) An optional frontend version of the base message ('message' config property).
     *                      If set it replaces the base message stored in an exception $_message_base property during
     *                      construction (but only after passing the original base message to the parent constructor).
     *                      It is used in ::getMessageFe().
     *  * show_fe       => (bool) An optional flag to control if ::getMessageFe() should return the real message.
     *                      If not equal to true ::getMessageFe() will return ::getMessageFeStub() instead.
     *                      It is stored (not always) in an exception $_show_fe property during construction.
     * </pre>
     *
     * @see CustomizableException::$_context            For 'context' config property runtime storage.
     * @see CustomizableException::getMessageComposed() For the full exception message composing algorithm.
     * @see CustomizableException::setContext()         For setting 'context' config property during runtime.
     * @see CustomizableException::getMessageDefault()  For getting the default 'message' config property.
     * @see CustomizableException::$_message_base       For 'message' config property runtime storage.
     * @see CustomizableException::getMessageFe()       For the frontend message composing algorithm.
     * @see CustomizableException::getMessageFeStub()   For the stub frontend message composing algorithm.
     * @see CustomizableException::$_show_fe            For 'show_fe' config property runtime storage.
     * @see CustomizableException::__construct()        For an exception construction algorithm
     *                                                  (also this config usage).
     * @see Parser::parse()                             For customizable exceptions Parser documentation.
     */
    const EXCEPTIONS_PROPERTIES = [];

    /**
     * @var string A default locale for system messages.
     *
     * It is passed to ::getL10N() explicitly in the constructor to get translations for the system version
     * of an exception message parts.
     *
     * @see CustomizableException::getL10N()        For the translation mechanism.
     * @see CustomizableException::__construct()    For an exception construction algorithm.
     */
    const L10N_SYSTEM_LOCALE = 'en';


    /**
     * @var mixed $_context An optional exception raw (untranslated) context (where it happened; the subject).
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the default value
     *                                                      ('context' config property).
     * @see CustomizableException::getContext()             For getting the value.
     * @see CustomizableException::setContext()             For replacing the default value.
     * @see CustomizableException::getMessageComposed()     For usage in the full exception message composing algorithm.
     */
    protected $_context = '';
    /**
     * @var mixed $_details Optional exception raw (untranslated) details
     * (what exact value is invalid, what is expected etc.).
     *
     * @see CustomizableException::getMessageComposed() For usage in the full exception message composing algorithm.
     * @see CustomizableException::getDetails()         For getting the value.
     */
    protected $_details = '';
    /**
     * @var mixed $_message_base An exception base raw (untranslated) message.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the value
     *                                                      ('message' and 'message_fe' config properties).
     * @see CustomizableException::getMessageBase()         For getting the value.
     * @see CustomizableException::getMessageComposed()     For usage in the full exception message composing algorithm.
     */
    protected $_message_base;
    /**
     * @var bool $_show_fe An optional flag to control if ::getMessageFe() should return the real message.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the value ('show_fe' config property).
     * @see CustomizableException::getMessageFe()           For the frontend message composing algorithm.
     * @see CustomizableException::canShowFe()              For getting the value.
     * @see CustomizableException::__construct()            For cases when 'show_fe' config property is ignored.
     */
    protected $_show_fe = false;


    /**
     * Returns a class section found in CLASS_SECTION_LIST class name corresponding element
     * or set in CLASS_SECTION_DEFAULT otherwise.
     *
     * Initially this method is used by Parser.
     *
     * @see CustomizableException::CLASS_SECTION_LIST       For checking / setting certain classes sections.
     * @see CustomizableException::CLASS_SECTION_DEFAULT    For checking / setting the default section.
     * @see Parser::parse()                                 For the customizable exceptions Parser documentation.
     *
     * @return string A class section.
     */
    public static function getClassSection(): string
    {
        return static::CLASS_SECTION_LIST[static::class] ?? static::CLASS_SECTION_DEFAULT;
    }

    /**
     * Returns the translation for $text_or_arguments_array based on $locale.
     *
     * If $locale is null (by default) it is assumed that the locale is determined automatically inside this method.
     * For instance a user current session locale might be used. And if $locale is equal to false it is assumed
     * that no translation is needed (you can implement such a behavior if needed).
     *
     * Initially this method is called:
     * * during an exception construction for system version of an exception message;
     * * in ::getMessageFeStub() for the 'error' string (using frontend culture)
     * * when an exception context, base message or details are returned via ::getContext(), ::getMessageBase()
     * and ::getDetails() respectively;
     * * in Parser::parse() for the message parts specified in EXCEPTIONS_PROPERTIES configs.
     *
     * Initially this is a stub which returns $text_or_arguments_array without any changes.
     * It is your job to redefine this method and provide it with the translation algorithm you desire.
     * Many popular frameworks have such a feature implementation already.
     * Just call the needed function passing it the arguments this method gets.
     *
     * @see CustomizableException::__construct()        For an exception construction algorithm.
     * @see CustomizableException::getMessageFeStub()   For the stub composing algorithm.
     * @see CustomizableException::getContext()         For getting $_context property translated value.
     * @see CustomizableException::getMessageBase()     For getting $_message_base property translated value.
     * @see CustomizableException::getDetails()         For getting $_details property translated value.
     * @see Parser::parse()                             For the customizable exceptions Parser documentation.
     *
     * @param mixed $text_or_arguments_array The string to translate or the arguments
     * for your translation function.
     * @param string|bool|null $locale [optional] The locale used for the translation.
     * If equals false then no translation is made.
     *
     * @return mixed The translation of $text_or_arguments_array
     * or $text_or_arguments_array unprocessed if $locale equals false.
     */
    public static function getL10N(
        $text_or_arguments_array,
        /** @noinspection PhpUnusedParameterInspection */
        $locale = null
    ) {
        return $text_or_arguments_array;
    }

    /**
     * Composes all message parts into one full exception message.
     *
     * The full exception message consists of the base message ($message) and any other strings as additions you can
     * specify in $parts array.
     * This method already supports some $parts which are mentioned here in the param's own description.
     *
     * Initially this method is called:
     * * during an exception construction to compose the system version of the message
     * * in ::getMessageFe() to compose the frontend version of the message
     * * in Parser::parse() to compose a localized version of the message for short output
     *
     * @see CustomizableException::__construct()    For an exception construction algorithm.
     * @see CustomizableException::getMessageFe()   For the frontend message composing algorithm.
     * @see Parser::parse()                         For the customizable exceptions Parser documentation.
     *
     * @param string $message The base exception message (usually is enough to describe an exception).
     * @param array $parts [optional] An array of message parts to combine with $message. Supported parts:
     * <pre>
     *  * context   => (string) [default: ''] An exception context (where it happened; the subject).
     *  * details   => (string) [default: ''] Exception details (what exact value is invalid, what is expected etc.).
     * </pre>
     *
     * @return string The full exception message.
     */
    public static function getMessageComposed(string $message, array $parts = []): string
    {
        $parts += [
            'context' => '',
            'details' => '',
        ];
        $message_constructed = '';

        if ('' !== $parts['context']) {
            $message_constructed .= $parts['context'] . ': ';
        }

        $message_constructed .= $message;

        if ('' !== $parts['details']) {
            $message_constructed .= ' (' . $parts['details'] . ')';
        }

        return $message_constructed;
    }

    /**
     * Returns the frontend message stub with general information about an exception.
     *
     * Initially it is returned by ::getMessageFe() if an exception $\_show_fe property equals false.
     * That is when you don't want user to see that exception real message.
     *
     * Initially this method uses GlobalException::getCodeFormatted() value to let users know an exception code
     * so they can then send it to support and ask for help.
     * The initial 'error' substring is passed to ::getL10N() in case
     * if it's OK for your application to show this built-in message localized version to users.
     *
     * Also this method is declared as public to let you call it independently. Just make sure to call it
     * from the right exception class and get the right global code if you use GlobalException feature.
     *
     * @see CustomizableException::getMessageFe()   For the frontend message composing algorithm.
     * @see CustomizableException::getL10N()        For the translation mechanism.
     * @see CustomizableException::canShowFe()      For getting $_show_fe property value.
     * @see GlobalException::getCodeFormatted()     For an exception code formatting algorithm.
     *
     * @param int $base_code An exception base (or full when not global) code.
     * @param string|bool|null $locale [optional] The locale used for the translation; passed to ::getL10N().
     *
     * @return mixed The frontend message stub.
     * Might be a string or the arguments for your translation function.
     */
    public static function getMessageFeStub(int $base_code, $locale = null)
    {
        return static::getL10N('error', $locale) . ' ' . static::getCodeFormatted($base_code);
    }


    /**
     * Returns the default value for an exception base message.
     *
     * Initially this method is called in the constructor if an exception 'message' property
     * is not specified in EXCEPTIONS_PROPERTIES.
     *
     * Initially the returned message contains the calling class name and its base and global codes
     * (identical if GlobalException feature is not used for the calling class).
     * The global code format is determined by GlobalException::getCodeFormatted().
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting 'message' config property.
     * @see CustomizableException::__construct()            For an exception construction algorithm.
     * @see GlobalException::getCodeFormatted()             For an exception code formatting algorithm.
     *
     * @param int $base_code An exception base (or full when not global) code.
     *
     * @return mixed An exception default base message to replace 'message' config property.
     * Might be a string or the arguments for your translation function.
     */
    protected static function getMessageDefault(int $base_code)
    {
        return sprintf(
            'CustomizableException %s (%s::%d)',
            static::getCodeFormatted($base_code),
            static::class,
            $base_code
        );
    }

    /**
     * Returns a message for the case when $base_code is not found as one of EXCEPTIONS_PROPERTIES config keys.
     *
     * Initially this method is called in the constructor if EXCEPTIONS_PROPERTIES config doesn't have the key
     * equal to $base_code.
     *
     * Initially the returned message contains $base_code and the calling class name.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting an exception properties' config
     *                                                      under $base_code as a key.
     * @see CustomizableException::__construct()            For an exception construction algorithm.
     *
     * @param int $base_code An exception base (or full when not global) code.
     *
     * @return mixed An exception message for unknown $base_code.
     * Might be a string or the arguments for your translation function.
     */
    protected static function getMessageUnknown(int $base_code)
    {
        return sprintf(
            'unknown base code %d for CustomizableException (%s)',
            $base_code,
            static::class
        );
    }


    /**
     * CustomizableException constructor.
     *
     * Has two modes to construct an exception:
     * * Classic mode - when the first parameter is not numeric and considered as an exception message; then the second
     * parameter is considered as (int) an exception (base) code. No other exception properties are processed.
     * Like you've called GlobalException or original \Exception constructor.
     * * Customizable mode - when the first parameter is numeric and considered as an exception obligatory (base) code,
     * the critical data to determine other exception's properties; then the second parameter is considered as
     * (mixed) an exception "details" part of its message. This is the functionality this class is made for.
     * You can "finalize" this mode by specifying the integer type hint in your extended class redefined constructor
     * for the first parameter.
     *
     * The rest description belongs to the customizable mode only.
     *
     * The customizable exception has system and frontend messages:
     * * The system version is always a real message which you can get by Exception::getMessage(),
     * Exception::__toString() and other original finalized Exception methods. It is composed by
     * ::getMessageComposed() where the base message, the context and the $_details are already passed to ::getL10N()
     * with L10N_SYSTEM_LOCALE.
     * * The frontend message is always composed during runtime by ::getMessageFe(). But the message parts for it
     * ($\_message_base, $\_context and $\_details) are determined in the constructor. All these message parts are
     * accessible by their own "getters" (::getMessageBase(), ::getContext(), ::getDetails()) and also
     * $\_context property can be replaced during runtime via ::setContext().
     *
     * --
     *
     * Step 1. Checking EXCEPTIONS_PROPERTIES.
     *
     * If the exception properties are missing (EXCEPTIONS_PROPERTIES key equal to $base_code is not found)
     * then $\_message_base property gets ::getMessageUnknown() value.
     *
     * If the exception properties are found 'context' config property is stored in $\_context property.
     *
     * If 'message' config property is missing then $\_message_base property gets ::getMessageDefault()
     * value.
     *
     * If 'message' config property is not empty then this 'message' value is stored in $\_message_base
     * property. 'show_fe' config property is stored in $\_show_fe in this case only. It can be accessed by
     * ::canShowFe().
     *
     * --
     *
     * Step 2. The system version of the exception message is passed to the parent constructor
     * (::getMessageBase(), ::getContext(), ::getDetails() are called).
     *
     * --
     *
     * Step 3. If ::canShowFe() returns true and 'message_fe' config property is not an empty string it replaces
     * the system version of $\_message_base.
     *
     * @see GlobalException::__construct()                  The parent constructor.
     * @see CustomizableException::getMessageComposed()     For the full exception message composing algorithm.
     * @see CustomizableException::getL10N()                For the translation mechanism.
     * @see CustomizableException::L10N_SYSTEM_LOCALE       For checking / setting the system message locale.
     * @see CustomizableException::getMessageFe()           For the frontend message composing algorithm.
     * @see CustomizableException::getContext()             For getting $_context property translated value.
     * @see CustomizableException::getDetails()             For getting $_details property translated value.
     * @see CustomizableException::getMessageBase()         For getting $_message_base property translated value.
     * @see CustomizableException::setContext()             For resetting $_context property value.
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For checking / setting up the exceptions properties config.
     * @see CustomizableException::getMessageUnknown()      For getting an "unknown exception" message if
     *                                                      the exception's properties are not found.
     * @see CustomizableException::getMessageDefault()      For getting the default 'message' config property.
     * @see CustomizableException::canShowFe()              For getting $_show_fe property value.
     *
     * @param int|string $base_code [customizable mode] The base (or full when not global) code for the calling
     * class exception OR [classic mode] the optional exception message to throw.
     * @param mixed $details [customizable mode] The optional exception details (what exact value is invalid,
     * what is expected etc.) OR [classic mode] the optional base (or full when not global) code
     * for the calling class exception.
     * @param \Throwable|null $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(string $base_code = '', $details = '', \Throwable $previous = null)
    {
        if (!is_numeric($base_code)) { // classic constructor - the first argument is an exception message
            $this->_message_base = $base_code;
            parent::__construct($base_code, (int) $details, $previous);

            return;
        }

        if (array_key_exists($base_code, static::EXCEPTIONS_PROPERTIES)) {
            if (empty(static::EXCEPTIONS_PROPERTIES[$base_code]['message'])) {
                $this->_message_base = static::getMessageDefault($base_code);
            } else {
                $this->_show_fe = !empty(static::EXCEPTIONS_PROPERTIES[$base_code]['show_fe']);
                $this->_message_base = (string) static::EXCEPTIONS_PROPERTIES[$base_code]['message'];
            }
            $this->_context = (string) (static::EXCEPTIONS_PROPERTIES[$base_code]['context'] ?? '');
        } else {
            $this->_message_base = static::getMessageUnknown($base_code);
        }
        $this->_details = $details;

        // the system version of an exception message
        parent::__construct(
            static::getMessageComposed(
                $this->getMessageBase(static::L10N_SYSTEM_LOCALE),
                [
                    'context' => $this->getContext(static::L10N_SYSTEM_LOCALE),
                    'details' => $this->getDetails(static::L10N_SYSTEM_LOCALE),
                ]
            ),
            $base_code,
            $previous
        );

        if (
            $this->canShowFe()
            && isset(static::EXCEPTIONS_PROPERTIES[$base_code]['message_fe'])
            && '' !== static::EXCEPTIONS_PROPERTIES[$base_code]['message_fe']
        ) {
            $this->_message_base = (string) static::EXCEPTIONS_PROPERTIES[$base_code]['message_fe'];
        }
    }

    /* Getters */

    /**
     * Returns the exception flag if ::getMessageFe() should return the real message.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the value ('show_fe' config property).
     * @see CustomizableException::getMessageFe()           For the frontend message composing algorithm.
     * @see CustomizableException::__construct()            For cases when 'show_fe' config property is ignored.
     *
     * @return bool The flag if the exception message can be shown in frontend interfaces.
     */
    public function canShowFe(): bool
    {
        return $this->_show_fe;
    }

    /**
     * Returns the exception context (where it happened; the subject) processed by ::getL10N().
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the default value
     *                                                      ('context' config property).
     * @see CustomizableException::getL10N()                For the translation mechanism.
     * @see CustomizableException::setContext()             For setting the value during runtime.
     *
     * @param string|bool|null $locale [optional] The locale used for the translation; passed to ::getL10N().
     *
     * @return mixed The translated exception context
     * or (if $locale equals false) unprocessed context data of any type.
     */
    public function getContext($locale = null)
    {
        return static::getL10N($this->_context, $locale);
    }

    /**
     * Returns the exception details (what exact value is invalid, what is expected etc.) processed by ::getL10N().
     *
     * This value is set in the constructor only by being passed as one of its parameters.
     *
     * @see CustomizableException::getL10N()        For the translation mechanism.
     * @see CustomizableException::__construct()    For the exception construction algorithm.
     *
     * @param string|bool|null $locale [optional] The locale used for the translation; passed to ::getL10N().
     *
     * @return mixed The exception translated details
     * or (if $locale equals false) unprocessed details data of any type.
     */
    public function getDetails($locale = null)
    {
        return static::getL10N($this->_details, $locale);
    }

    /**
     * Returns the exception base message processed by ::getL10N().
     *
     * This message can be equal to a value returned by Exception::getMessage() and other
     * original finalized Exception methods if no context, details or any other exception message parts are specified.
     *
     * @see CustomizableException::getL10N()                For the translation mechanism.
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For configuring the value
     *                                                      ('message' and 'message_fe' config properties).
     * @see CustomizableException::__construct()            For the exception construction algorithm where this value
     *                                                      is set depending on circumstances.
     *
     * @param string|bool|null $locale [optional] The locale used for the translation; passed to ::getL10N().
     *
     * @return mixed The exception translated base message
     * or (if $locale equals false) unprocessed base message data of any type.
     */
    public function getMessageBase($locale = null)
    {
        return static::getL10N($this->_message_base, $locale);
    }

    /**
     * Returns the translated frontend version of the exception message.
     *
     * This method determines if it can return the exception message for frontend interfaces by checking
     * ::canShowFe() value.
     * If true then ::getMessageComposed() value is returned (using ::getMessageBase(), ::getContext()
     * and ::getDetails() returned values).
     * ::getMessageFeStub() is returned otherwise.
     *
     * @see CustomizableException::canShowFe()          For checking if the exception message can be shown
     *                                                  in frontend interfaces
     * @see CustomizableException::getMessageComposed() For the full exception message composing algorithm.
     * @see CustomizableException::getMessageBase()     For getting $_message_base property value.
     * @see CustomizableException::getContext()         For getting $_context property value.
     * @see CustomizableException::getDetails()         For getting $_details property value.
     * @see CustomizableException::getMessageFeStub()   For the stub composing algorithm.
     * @see CustomizableException::getL10N()            For the translation mechanism.
     *
     * @param string|bool|null $locale [optional] The locale used for the translation via ::getL10N();
     * passed to inner methods for translation.
     *
     * @return mixed The exception frontend message
     * or (if $locale equals false) its unprocessed version of any type.
     */
    public function getMessageFe($locale = null)
    {
        if (!$this->canShowFe()) {
            return static::getMessageFeStub($this->getCodeBase(), $locale);
        }

        return static::getMessageComposed(
            $this->getMessageBase($locale),
            ['context' => $this->getContext($locale), 'details' => $this->getDetails($locale)]
        );
    }

    /* /Getters */

    /* Setters */

    /**
     * Sets the new context (where it happened; the subject) for the exception.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the default value ('context' config property).
     * @see CustomizableException::getContext()             For getting the value.
     *
     * @param mixed $value The new context value
     * (the string to translate or the arguments for your translation function).
     *
     * @return CustomizableException The updated exception object.
     */
    public function setContext($value): CustomizableException
    {
        $this->_context = $value;

        return $this;
    }

    /* /Setters */
}
