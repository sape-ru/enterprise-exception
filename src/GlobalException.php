<?php
namespace MagicPush\EnterpriseException;

/**
 * The exception class used for globalizing exceptions' codes.
 *
 * Will globalize its codes only after CLASS_CODE_LIST config is set properly. You must extend this class with
 * your project base exception class (PBEC) and set up the config there mentioning all exception classes you want
 * to globalize (and all those exception classes must be subclasses of PBEC).
 *
 * Also this class can be used and act like the classic \Exception if you don't set up CLASS_CODE_LIST config.
 *
 * @see GlobalException::CLASS_CODE_LIST    For checking / setting up the config.
 *
 * @package GlobalException
 *
 * @author Kirill Ulanovskiy <xerxes.home@gmail.com>
 */
abstract class GlobalException extends \Exception
{
    /**
     * @var int The relative maximum exception global code possible.
     *
     * Used for calculating a class code maximum depending on the base code scale (CLASS_CODE_MULTIPLIER)
     * to ensure that the biggest possible base code combining with the biggest class code
     * will not turn into an integer bigger than PHP integer maximum or your custom maximum.
     *
     * @see GlobalException::CLASS_CODE_MULTIPLIER  For checking / specifying the base code scale.
     */
    const GLOBAL_CODE_MAX_RELATIVE = PHP_INT_MAX;

    /** @var int The biggest base code must be smaller than this value. */
    const CLASS_CODE_MULTIPLIER = 10 ** 5;

    /**
     * @var array|int[] The central config for globalizing exceptions' codes.
     *
     * The array key must be a qualified namespaced class name (AnyClass::class) or it will be ignored -
     * not found while getting a class code (::getCodeClass()).
     * The array value (a class code) should be a positive integer to enable the globalization feature for that class.
     * A class code equivalent to 0 disables the globalization feature for that class
     * (like it wasn't added to this array).
     * Negative class codes are considered invalid (::validateCodeClass())
     * and equal to 0 during runtime (::getCodeClass(), ::getCodeParts()).
     *
     * @see GlobalException::getCodeClass()         For a class code determining rules.
     * @see GlobalException::validateCodeClass()    For a class code validation rules.
     * @see GlobalException::getCodeParts()         For a global code decomposition algorithm.
     */
    const CLASS_CODE_LIST = [];


    /**
     * @var int $_base_code The exception base (or full when not global) code.
     *
     * @see GlobalException::__construct()  For setting the value.
     * @see GlobalException::getCodeBase()  For getting the value.
     */
    protected $_base_code;


    /**
     * Returns the valid class code based on the calling class.
     *
     * Returns 0 for the invalid class code (::validateCodeClass()).
     * The class code is extracted from CLASS_CODE_LIST config.
     *
     * Initially this method is called in ::getCodeGlobal() to calculate an exception global code.
     *
     * @see GlobalException::validateCodeClass()    For a class code validation rules.
     * @see GlobalException::CLASS_CODE_LIST        For checking / setting up the config.
     * @see GlobalException::getCodeGlobal()        For a global code calculation algorithm.
     *
     * @return int The valid class code.
     */
    public static function getCodeClass(): int
    {
        $class_name = static::class;

        if (!isset(static::CLASS_CODE_LIST[$class_name])) {
            return 0;
        }

        $class_code = (int) static::CLASS_CODE_LIST[$class_name];
        try {
            static::validateCodeClass($class_code);

            return $class_code;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Returns the biggest class code possible.
     *
     * This method is finalized intentionally. The calculation is based on GLOBAL_CODE_MAX_RELATIVE and
     * CLASS_CODE_MULTIPLIER values. Redefine these if you want to change possible results.
     *
     * @see GlobalException::GLOBAL_CODE_MAX_RELATIVE   For checking / setting the value.
     * @see GlobalException::CLASS_CODE_MULTIPLIER      For checking / setting the value.
     *
     * @return int the biggest class code possible
     */
    final public static function getCodeClassMax(): int
    {
        return intval(floor(static::GLOBAL_CODE_MAX_RELATIVE / static::CLASS_CODE_MULTIPLIER) - 1);
    }

    /**
     * Returns the formatted code of an exception to use in different exception string representations.
     *
     * Initially it returns GlobalException::getCodeGlobal() as a string.
     *
     * @see GlobalException::getCodeGlobal()    For a global code calculation algorithm.
     *
     * @param int $base_code An exception base (or full when not global) code.
     *
     * @return string An exception formatted code.
     */
    public static function getCodeFormatted(int $base_code): string
    {
        return (string) static::getCodeGlobal($base_code);
    }

    /**
     * Returns the calculated valid exception code based on the calling class.
     *
     * This exception code may be considered global if the calling class code is set properly
     * in CLASS_CODE_LIST config (::getCodeClass()). If so and $base_code is valid (::validateCodeBase())
     * the global code is calculated using $base_code, the class code and CLASS_CODE_MULTIPLIER
     * (as a base code scale).
     *
     * Initially this method is called in the constructor,  but it also can be used for compairing an exception code
     * caught in a try-catch block and any specific exception code you want to check in your business logic.
     *
     * @see GlobalException::CLASS_CODE_LIST        For checking / setting up the config.
     * @see GlobalException::getCodeClass()         For a class code determining rules.
     * @see GlobalException::validateCodeBase()     For a base code validation rules.
     * @see GlobalException::CLASS_CODE_MULTIPLIER  For checking / setting the base code scale
     *                                              used in the global code calucaltion.
     * @see GlobalException::__construct()          Uses this method.
     *
     * @param int $base_code The base code for the calling class exception.
     *
     * @return int The valid exception code.
     */
    public static function getCodeGlobal(int $base_code): int
    {
        try {
            static::validateCodeBase($base_code);

            return $base_code + static::getCodeClass() * static::CLASS_CODE_MULTIPLIER;
        } catch (\Exception $e) {
            return $base_code;
        }
    }

    /**
     * Returns an array of the exception (global) code parts.
     *
     * Decomposes the global code (based on CLASS_CODE_MULTIPLIER) into class and base codes and returns them as
     * an associative array. If the base code or the class code are considered invalid (::validateCodeBase(),
     * ::validateCodeClass()) then the base code equals $global_code and the class code equals 0.
     *
     * This method can be called from any subclasses of GlobalException (and the class itself). But be warned
     * that CLASS_CODE_MULTIPLIER used for decomposing $global_code is taken from the calling class.
     *
     * This is a tool method which is not used initially inside this library.
     *
     * @see GlobalException::CLASS_CODE_MULTIPLIER  Used in the global code decomposition.
     * @see GlobalException::validateCodeBase()     For a base code validation rules.
     * @see GlobalException::validateCodeClass()    For a class code validation rules.
     *
     * @param int $global_code The exception (global) code.
     *
     * @return array The exception code parts:
     * <pre>
     *  * base_code     => (int) The exception base (or full when not global) code.
     *  * class_code    => (int) The class code (may equal to 0 - invalid or not set as global).
     * </pre>
     */
    public static function getCodeParts(int $global_code): array
    {
        $base_code = $global_code % static::CLASS_CODE_MULTIPLIER;
        try {
            static::validateCodeBase($base_code);
            $class_code = (int) floor($global_code / static::CLASS_CODE_MULTIPLIER);
            static::validateCodeClass($class_code);
        } catch (\Exception $e) {
            $base_code = $global_code;
            $class_code = 0;
        }

        return [
            'base_code'  => $base_code,
            'class_code' => $class_code,
        ];
    }

    /**
     * Checks if the base code is valid to be a part of an exception global code.
     *
     * The validation is based on CLASS_CODE_MULTIPLIER value.
     *
     * Initially this method is called in ::getCodeGlobal() and ::getCodeParts().
     *
     * @see GlobalException::CLASS_CODE_MULTIPLIER  Used to be compared with $base_code.
     * @see GlobalException::getCodeGlobal()        For a global code calculation algorithm.
     * @see GlobalException::getCodeParts()         For a global code decomposition algorithm.
     *
     * @param int $base_code The base code to validate.
     *
     * @return void
     *
     * @throws \Exception   if the base code < 1 or >= static::CLASS_CODE_MULTIPLIER.
     */
    public static function validateCodeBase(int $base_code)
    {
        if ($base_code < 1 || $base_code >= static::CLASS_CODE_MULTIPLIER) {
            throw new \Exception(
                sprintf(
                    'The base code %d for "%s" must range from 1 to %d.',
                    $base_code,
                    static::class,
                    (static::CLASS_CODE_MULTIPLIER - 1)
                )
            );
        }
    }

    /**
     * Checks if the class code is valid to be a part of an exception global code.
     *
     * The validation is based on ::getCodeClassMax() result to determine if a hypothetical global code would be
     * bigger than PHP integer maximum or your custom maximum (specified in GLOBAL_CODE_MAX_RELATIVE constant).
     * It also checks if the class code equals to 0 or positive.
     *
     * Initially this method is called in ::getCodeClass() and ::getCodeParts().
     *
     * @see GlobalException::getCodeClassMax()          For the biggest class code calculation.
     * @see GlobalException::GLOBAL_CODE_MAX_RELATIVE   The relative maximum exception global code possible.
     * @see GlobalException::getCodeClass()             For a class code determining rules.
     * @see GlobalException::getCodeParts()             For a global code decomposition algorithm.
     *
     * @param int $class_code The class code to validate.
     *
     * @return void
     *
     * @throws \Exception   if the class code is negative.
     * @throws \Exception   if the class code is too big (integer overflow possibility).
     */
    public static function validateCodeClass(int $class_code)
    {
        if (!$class_code) {
            return;
        }

        $message = 'The global class code ' . $class_code . ' for "' . static::class . '"';

        if ($class_code < 0) {
            throw new \Exception($message . ' must be 0 (not global) or positive.');
        }

        $class_code_max = self::getCodeClassMax();
        if ($class_code <= $class_code_max) {
            return;
        }

        $message .= ' is bigger than ' . $class_code_max
            . ', can cause integer overflow for the global code.';

        throw new \Exception($message);
    }


    /**
     * GlobalException constructor.
     *
     * The only difference is a calculated global code (::getCodeGlobal()) passed to the parent constructor.
     * Also the base code is stored inside the exception object and can be returned by ::getCodeBase().
     *
     * @see GlobalException::getCodeGlobal()    For a global code calculation algorithm.
     * @see GlobalException::getCodeBase()      For getting the base code.
     *
     * @param string $message [optional] The exception message to throw.
     * @param int $base_code [optional] The base (or full when not global) code for the calling class exception.
     * @param \Throwable $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = '', int $base_code = 0, \Throwable $previous = null)
    {
        $this->_base_code = $base_code;

        parent::__construct($message, static::getCodeGlobal($base_code), $previous);
    }

    /**
     * Returns the exception base (or full when not global) code.
     *
     * @return int The exception base code.
     */
    public function getCodeBase(): int
    {
        return $this->_base_code;
    }
}
