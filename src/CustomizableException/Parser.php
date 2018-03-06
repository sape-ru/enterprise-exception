<?php
namespace MagicPush\EnterpriseException\CustomizableException;

use MagicPush\EnterpriseException\GlobalException;

/**
 * Parser is used to parse CustomizableException classes.
 * Read the ::parse() documentation for more info.
 *
 * @see CustomizableException   For the customizable exceptions documentation.
 * @see Parser::parse()         For the parser documentation.
 *
 * @package CustomizableException
 *
 * @author Kirill Ulanovskiy <xerxes.home@gmail.com>
 */
abstract class Parser
{
    /**
     * Parses CustomizableException classes and acts differently depending on $options specified.
     *
     * This method can parse exceptions only via extracting class names from CLASS_CODE_LIST config
     * which is set up in $config_class_name class. Then it parses each class EXCEPTIONS_PROPERTIES config.
     *
     * Be warned that all classes (auto)loading is entirely up to you. You must load all classes being processed by
     * Parser before calling ::parse().
     * As an alternative you can redefine ::loadClass() to load classes one by one on demand.
     *
     * This method makes several steps (also depending on $options):
     * 1. Loading. An exception class can be loaded via ::loadClass() for further processing.
     * 2. Filtering. If you want to validate and/or get only specific classes/exceptions then check $filters
     * description. Also not classess but exceptions filtering can be customized by redefining
     * ::needFilterException() called after all built-in filters.
     * 3. Validation. Every class and exception which is not filtered by $filters and uses GlobalException
     * functionality (a class code is not equal to 0) is validated by several checks:
     *      * A class code is validated via GlobalException::validateCodeClass(). Then the possible global code is
     *      generated via GlobalException::getCodeGlobal(1) to check if any of already parsed classes can have
     *      the same code. You can even validate pure GlobalException descendants. But such classes will not
     *      be parsed for the returned data array.
     *      * An exception base code is validated via ::GlobalException::validateCodeBase().
     * 4. Preparing the returned data. An exception class data is read and added to the returned data array.
     * Read ::addExceptionData() documentation for returned array formats.
     *
     * @see CustomizableException                           For the customizable exceptions documentation.
     * @see GlobalException::CLASS_CODE_LIST                For checking / setting up a class codes list for the parser.
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For checking / setting up a class exceptions properties.
     * @see Parser::loadClass()                             For a single class (auto)loading.
     * @see Parser::needFilterException()                   For exceptions extra filtering.
     * @see GlobalException                                 For the global exceptions documentation.
     * @see GlobalException::validateCodeClass()            For a class code validation.
     * @see GlobalException::getCodeGlobal()                For a global code calculation.
     * @see GlobalException::validateCodeBase()             For an exception base code validation.
     * @see Parser::addExceptionData()                      For the returned array formats.
     * @see CustomizableException::L10N_SYSTEM_LOCALE       For checking / setting the system message locale.
     * @see CustomizableException::getL10N()                For the translation mechanism.
     * @see CustomizableException::getClassSection()        For the class section determination.
     *
     * @param string $config_class_name CustomizableException base subclass with CLASS_CODE_LIST set up.
     * @param array $options An array of parsing options. This method already supports the options:
     * <pre>
     *  * add_errors        => (bool) [default: false] If the parser should act like 'ignore_invalid' option is
     *                          turned on but also add validations errors messages to the output
     *                          under '__errors' 1-level key.
     *  * ignore_invalid    => (bool) [default: false] If the parser should suppress validations errors and ignore
     *                          (not to add to the returned data) invalid classes and exceptions;
     *                          might be useful if you output the returned data to frontend interfaces (to show as much
     *                          as possible without a page crash due to a validation error being thrown).
     *  * is_extended       => (bool) [default: false] Controls the returned array formats;
     *                          read 'return' documentation for more info.
     *  * locale            => (string) [default: $config_class_name::L10N_SYSTEM_LOCALE - the system locale]
     *                          Controls exceptions string-type properties translation via
     *                          CustomizableException::getL10N();
     *                          read ::addExceptionData() to know which exception properties are translated.
     *  * no_data           => (bool) [default: false] If the parser should return an empty array instead of storing
     *                          exceptions classes data;
     *                          useful if you want just to validate exceptions and not to occupy memory.
     *  * use_message_fe    => (bool) [default: false] If the parser should return 'message_fe' property
     *                          from a class EXCEPTIONS_PROPERTIES config instead of 'message' property;
     *                          is ignored if 'is_extended' option equals to true.
     * </pre>
     * @param array $filters An array of filters. Filters with default values are considered disabled. The filters
     * names suffixes stand for: 'ex' - exclude, 'in' - include. The "base" codes might be considered as standard full
     * exceptions codes if their class codes are equal to 0 (the globalization feature is disabled for those classes).
     * This method already supports the filters:
     * <pre>
     *  * base_code_from_ex         => (int) [default: null] All exceptions but those with codes
     *                                  equal or bigger than the specified integer.
     *  * base_code_from_in         => (int) [default: null] Only exceptions with codes
     *                                  equal or bigger than the specified integer.
     *  * base_code_list_ex         => (array) [default: []] All exceptions but those with codes equal to the specified.
     *  * base_code_list_in         => (array) [default: []] Only exceptions with codes equal to the specified.
     *  * base_code_to_ex           => (int) [default: null] All exceptions but those with codes
     *                                  equal or smaller than the specified integer.
     *  * base_code_to_in           => (int) [default: null] Only exceptions with codes
     *                                  equal or smaller than the specified integer.
     *  * class_code_list_ex        => (array) [default: []] All classes but those with codes equal to the specified.
     *  * class_code_list_in        => (array) [default: []] Only classes with codes equal to the specified.
     *  * class_name_part_list_ex   => (array) [default: []] All classes but those with fully qualified names
     *                                  containing the specified substrings (case sensitive).
     *  * class_name_part_list_in   => (array) [default: []] Only classes with fully qualified names
     *                                  containing the specified substrings (case sensitive).
     *  * class_section_list_ex     => (array) [default: []] All classes but those with sections
     *                                  (CustomizableException::getClassSection()) equal to the specified;
     *                                  case sensitive;
     *                                  doesn't work with GlobalException descendants.
     *  * class_section_list_in     => (array) [default: []] Only classes with sections
     *                                  (CustomizableException::getClassSection()) equal to the specified;
     *                                  case sensitive;
     *                                  doesn't work with GlobalException descendants.
     *  * show_fe                   => (bool) [default: null] Filters exceptions by 'show_fe' property:
     *                                  true  => exceptions with 'show_fe' property equal to true;
     *                                  false => exceptions with 'show_fe' property missing or equal to false.
     * </pre>
     *
     * @return array The parsed data composed by ::addExceptionData().
     *
     * @throws \Exception   if $config_class_name is not loaded.
     * @throws \Exception   if $config_class_name is not a subclass of CustomizableException.
     * @throws \Exception   if a class code is not valid (GlobalException::validateCodeClass()).
     * @throws \Exception   if two exception classes can generate identical global codes
     *                      (GlobalException::getCodeGlobal(1)).
     * @throws \Exception   if an exception base code is not valid (GlobalException::validateCodeBase()).
     */
    public static function parse(string $config_class_name, array $options = [], array $filters = []): array
    {
        /** @var string|CustomizableException $config_class_name Not an object; is needed for IDE hinting */
        static::loadClass($config_class_name);
        if (!class_exists($config_class_name, false)) {
            throw new \Exception('The config class "' . $config_class_name . '" is not loaded.');
        }

        $base_class_name = CustomizableException::class;

        if (!is_subclass_of($config_class_name, $base_class_name, true)) {
            throw new \Exception(
                sprintf(
                    'The config class "%s" is not a subclass of "%s"',
                    $config_class_name,
                    $base_class_name
                )
            );
        }

        $parsed_data = [];
        if (empty($config_class_name::CLASS_CODE_LIST)) {
            return $parsed_data;
        }

        /* Defaults */

        $options += [
            'add_errors'     => false,
            'ignore_invalid' => false,
            'is_extended'    => false,
            'locale'         => $config_class_name::L10N_SYSTEM_LOCALE,
            'no_data'        => false,
            'use_message_fe' => false,
        ];
        $filters += [
            'base_code_from_ex'       => null,
            'base_code_from_in'       => null,
            'base_code_list_ex'       => [],
            'base_code_list_in'       => [],
            'base_code_to_ex'         => null,
            'base_code_to_in'         => null,
            'class_code_list_ex'      => [],
            'class_code_list_in'      => [],
            'class_name_part_list_ex' => [],
            'class_name_part_list_in' => [],
            'class_section_list_ex'   => [],
            'class_section_list_in'   => [],
            'show_fe'                 => null,
        ];

        /* /Defaults */

        /* Optimization prerequisites */

        $has_filter_base_code_from_ex = is_numeric($filters['base_code_from_ex']);
        $has_filter_base_code_to_ex = is_numeric($filters['base_code_to_ex']);
        $is_filter_base_code_ex_from_lessequal_than_to = $filters['base_code_from_ex'] <= $filters['base_code_to_ex'];

        $has_filter_base_code_from_in = is_numeric($filters['base_code_from_in']);
        $has_filter_base_code_to_in = is_numeric($filters['base_code_to_in']);
        $is_filter_base_code_in_from_more_than_to = $filters['base_code_from_in'] > $filters['base_code_to_in'];

        $base_code_list_flipped_ex = array_flip($filters['base_code_list_ex']);
        $base_code_list_flipped_in = array_flip($filters['base_code_list_in']);

        $class_code_list_flipped_ex = array_flip($filters['class_code_list_ex']);
        $class_code_list_flipped_in = array_flip($filters['class_code_list_in']);

        $class_section_list_flipped_ex = array_flip($filters['class_section_list_ex']);
        $class_section_list_flipped_in = array_flip($filters['class_section_list_in']);

        $is_filter_show_fe_not_null = !is_null($filters['show_fe']);
        if ($is_filter_show_fe_not_null) {
            $filters['show_fe'] = (bool) $filters['show_fe'];
        }

        /* /Optimization prerequisites */

        $classes_names_by_codes_arr = $validation_errors_arr = [];
        foreach ($config_class_name::CLASS_CODE_LIST as $class_name => $class_code) {
            /** @var string|CustomizableException $class_name Not an object; is needed for IDE hinting */
            static::loadClass($class_name);

            $is_subclass_of_customizable = is_subclass_of($class_name, $base_class_name, true);

            /* Class filtering */

            if ($class_code_list_flipped_ex && array_key_exists($class_code, $class_code_list_flipped_ex)) {
                continue;
            }
            if ($class_code_list_flipped_in && !array_key_exists($class_code, $class_code_list_flipped_in)) {
                continue;
            }

            if ($is_subclass_of_customizable) {
                $class_section = $class_name::getClassSection();
                if (
                    $class_section_list_flipped_ex
                    && array_key_exists($class_section, $class_section_list_flipped_ex)
                ) {
                    continue;
                }

                if (
                    $class_section_list_flipped_in
                    && !array_key_exists($class_section, $class_section_list_flipped_in)
                ) {
                    continue;
                }
            }

            foreach ($filters['class_name_part_list_ex'] as $class_name_part) {
                if (false !== strpos($class_name, $class_name_part)) {
                    continue 2;
                }
            }
            if ($filters['class_name_part_list_in']) {
                $need_filter_class_name_part_in = true;
                foreach ($filters['class_name_part_list_in'] as $class_name_part) {
                    if (false !== strpos($class_name, $class_name_part)) {
                        $need_filter_class_name_part_in = false;

                        break;
                    }
                }
                if ($need_filter_class_name_part_in) {
                    continue;
                }
            }

            /* /Class filtering */

            // if an exception class uses the globalization feature
            if ($class_code) {
                try {
                    $class_name::validateCodeClass($class_code);

                    $global_code_potential = $class_name::getCodeGlobal(1);
                    if (!empty($classes_names_by_codes_arr[$global_code_potential])) {
                        throw new \Exception(
                            sprintf(
                                'Same potential global code %d generated for "%s" and "%s".',
                                $global_code_potential,
                                $classes_names_by_codes_arr[$global_code_potential],
                                $class_name
                            )
                        );
                    }
                    $classes_names_by_codes_arr[$global_code_potential] = $class_name;
                } catch (\Exception $e) {
                    if ($options['add_errors'] || $options['ignore_invalid']) {
                        if ($options['add_errors']) {
                            $validation_errors_arr[] = $e->getMessage();
                        }

                        continue;
                    }

                    throw $e;
                }
            }

            if (
                !$is_subclass_of_customizable
                || empty($class_name::EXCEPTIONS_PROPERTIES)
            ) {
                continue;
            }

            foreach ($class_name::EXCEPTIONS_PROPERTIES as $base_code => $properties) {
                /* Exception filtering */

                if ($base_code_list_flipped_ex && array_key_exists($base_code, $base_code_list_flipped_ex)) {
                    continue;
                }
                if ($base_code_list_flipped_in && !array_key_exists($base_code, $base_code_list_flipped_in)) {
                    continue;
                }

                if (
                    $is_filter_show_fe_not_null
                    && $filters['show_fe'] != !empty($properties['show_fe'])
                ) {
                    continue;
                }

                $need_filter_base_code_from_ex = $has_filter_base_code_from_ex
                    && $base_code >= $filters['base_code_from_ex'];
                $need_filter_base_code_to_ex = $has_filter_base_code_to_ex
                    && $base_code <= $filters['base_code_to_ex'];
                if (
                    $has_filter_base_code_from_ex
                    && $has_filter_base_code_to_ex
                    && $is_filter_base_code_ex_from_lessequal_than_to
                ) {
                    if ($need_filter_base_code_from_ex && $need_filter_base_code_to_ex) {
                        continue;
                    }
                } elseif ($need_filter_base_code_from_ex || $need_filter_base_code_to_ex) {
                    continue;
                }

                $need_filter_base_code_from_in = $has_filter_base_code_from_in
                    && $base_code < $filters['base_code_from_in'];
                $need_filter_base_code_to_in = $has_filter_base_code_to_in
                    && $base_code > $filters['base_code_to_in'];
                if (
                    $has_filter_base_code_from_in
                    && $has_filter_base_code_to_in
                    && $is_filter_base_code_in_from_more_than_to
                ) {
                    if ($need_filter_base_code_from_in && $need_filter_base_code_to_in) {
                        continue;
                    }
                } elseif ($need_filter_base_code_from_in || $need_filter_base_code_to_in) {
                    continue;
                }

                if (static::needFilterException($filters, $base_code, $properties)) {
                    continue;
                }

                /* /Exception filtering */

                // if an exception class uses the globalization feature
                if ($class_code) {
                    try {
                        $class_name::validateCodeBase($base_code);
                    } catch (\Exception $e) {
                        if ($options['add_errors'] || $options['ignore_invalid']) {
                            if ($options['add_errors']) {
                                $validation_errors_arr[] = $e->getMessage();
                            }

                            continue;
                        }

                        throw $e;
                    }
                }

                if ($options['no_data']) {
                    continue;
                }

                static::addExceptionData(
                    $parsed_data,
                    $options,
                    $properties,
                    [
                        'base_code'  => $base_code,
                        'class_code' => $class_code,
                        'class_name' => $class_name,
                    ]
                );
            }
        }
        unset($classes_names_by_codes_arr);

        if ($options['add_errors']) {
            $parsed_data['__errors'] = $validation_errors_arr;
        }

        return $parsed_data;
    }


    /**
     * Updates $parsed_data array with the next exception data.
     *
     * $parsed_data array always has two levels. The first level key can be:
     * * '__global' - for classes using GlobalException functionality;
     * * a class fully qualified name - for other classes (a class code is always equal to 0 in this case);
     * * '__errors' - if $options['add_erros'] equals true and there are validations errors encountered.
     *
     * The second level key is an exception (global) code (which you get via \Exception::getCode()).
     *
     * Initially the data stored under the second level key depends on $options['is_extended']. If this option
     * equals false then the stored value is an exception full message composed via
     * CustomizableException::getMessageComposed().
     * Otherwise the stored value is an aray of the data:
     * <pre>
     * * base_code      => (int) An exception base code ($basis['base_code']); can equal to the second level key
     *                      if the globalization feature is disabled for that exception class.
     * * class_code     => (int) An exception class code ($basis['class_code']).
     * * class_name     => (string) An exception class fully qualified name ($basis['class_name']).
     * * class_section  => (string) An exception class section (CustomizableException::getClassSection()).
     * * context        => (string) An exception default contex,
     *                      'context' CustomizableException::EXCEPTIONS_PROPERTIES config property;
     *                      is translated via CustomizableException::getL10N().
     * * message        => (string) An exception base message,
     *                      'message' CustomizableException::EXCEPTIONS_PROPERTIES config property;
     *                      is translated via CustomizableException::getL10N().
     * * message_fe     => (string) An exception frontend base message,
     *                      'message_fe' CustomizableException::EXCEPTIONS_PROPERTIES config property;
     *                      is translated via CustomizableException::getL10N().
     * * show_fe        => (bool) An exception flag which allows to show the real message in frontend interfaces,
     *                      'show_fe' CustomizableException::EXCEPTIONS_PROPERTIES config property.
     * </pre>
     *
     * You can add your own functionality by redefining this method. But be warned that you must call the parent
     * version before your code: this parent creates new $parsed_data elements; you must merge your extra data with
     * the existing ones.
     *
     * @see Parser::parse()                                 For the parser documentation.
     * @see CustomizableException::getMessageComposed()     For the full exception message composing algorithm.
     * @see GlobalException                                 For the global exceptions documentation.
     * @see CustomizableException::getClassSection()        For the class section determination.
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For checking / setting up a class exceptions properties.
     * @see CustomizableException::getL10N()                For the translation mechanism.
     *
     * @param array $parsed_data The array returned by ::parse() in the end. Passed by a reference.
     * @param array $options The array of options, one of ::parse() arguments. Passed by a reference.
     * @param array $properties The array of an exception EXCEPTIONS_PROPERTIES config. Passed by a reference.
     * @param array $basis An array of an exception basis. This array is critical for this method functioning but
     * not accompanied with its validations for optimization purpose. It is your job to ensure that all needed elements
     * are specified here if you decide to call this method explicitly anywhere else. The array includes:
     * <pre>
     * * base_code  => (int) an exception code (validated already)
     * * class_code => (int) an exception class code (validated already)
     * * class_name => (string) a fully qualified exception class name (not blank)
     * </pre>
     *
     * @return void
     */
    protected static function addExceptionData(array &$parsed_data, array &$options, array &$properties, array $basis)
    {
        /** @var string|CustomizableException $class_name Not an object; is needed for IDE hinting */
        $class_name = $basis['class_name'];

        $exception_code = $basis['class_code'] ? $class_name::getCodeGlobal($basis['base_code']) : $basis['base_code'];
        $class_type = $basis['class_code'] ? '__global' : $class_name;
        $context = $message = $message_fe = '';
        if (!empty($properties['context'])) {
            $context = $class_name::getL10N($properties['context'], $options['locale']);
        }
        if (!empty($properties['message'])) {
            $message = $class_name::getL10N($properties['message'], $options['locale']);
        }
        if (!empty($properties['message_fe'])) {
            $message_fe = $class_name::getL10N($properties['message_fe'], $options['locale']);
        }

        if ($options['is_extended']) {
            $parsed_data[$class_type][$exception_code] = [
                'base_code'     => $basis['base_code'],
                'class_code'    => $basis['class_code'],
                'class_name'    => $class_name,
                'class_section' => $class_name::getClassSection(),
                'context'       => $context,
                'message'       => $message,
                'message_fe'    => $message_fe,
                'show_fe'       => !empty($properties['show_fe']),
            ];
        } else {
            $parsed_data[$class_type][$exception_code] = $class_name::getMessageComposed(
                ($options['use_message_fe'] && $message_fe) ? $message_fe : $message,
                ['context' => $context]
            );
        }
    }

    /**
     * Loads $class_name for further Parser processing.
     *
     * This method is called in ::parse() for each class before its usage.
     *
     * Initially it is a stub which does nothing.
     * You can redefine it if you wish to load your exception classes one by one.
     *
     * @see Parser::parse() For the parser documentation.
     *
     * @param string $class_name A fully qualified exception class name.
     *
     * @return void
     */
    protected static function loadClass(
        /** @noinspection PhpUnusedParameterInspection */
        string $class_name
    ) {
        return;
    }

    /**
     * Returns an answer to the question: "Will this exception be excluded by a filter from the parsed data?".
     *
     * This method is called for each exception after all built-in filters in ::parse().
     *
     * Initially this is a stub which always returns false.
     * You can redefine it to add any filtering rules you desire.
     *
     * @see Parser::parse()                                 For the parser documentation.
     * @see GlobalException                                 For the global exceptions documentation.
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For checking / setting up a class exceptions properties.
     *
     * @param array $filters The array of filters, one of ::parse() arguments. Passed by a reference.
     * @param int $base_code An exception base (or full when not global) code.
     * @param array $properties The array of an exception EXCEPTIONS_PROPERTIES config. Passed by a reference.
     *
     * @return bool If an exception must be excluded.
     */
    protected static function needFilterException(
        /** @noinspection PhpUnusedParameterInspection */
        array &$filters,
        int $base_code,
        array &$properties
    ): bool {
        return false;
    }
}
