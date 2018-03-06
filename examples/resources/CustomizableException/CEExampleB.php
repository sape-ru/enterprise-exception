<?php

/**
 * CustomizableException class example.
 *
 * @package CustomizableException\Examples
 */
class CEExampleB extends CEExampleConfig
{
    const EXCEPTIONS_PROPERTIES = [
        1 => [
            'message' => 'stay frosty',
            'show_fe' => true,
        ],
        2 => [
            // 'message' element is missing intentionally
            'message_fe' => 'not used frontend message',
            'show_fe'    => true, // will turn to false because there is no 'message' element
        ],
        // 3 is absent - unknown exception; unknown message will be generated
    ];
}
