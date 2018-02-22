<?php
namespace MagicPush\EnterpriseException\Examples\CustomizableException;

/**
 * The CustomizableException class example.
 *
 * @package CustomizableException\Examples
 */
class AException extends ExceptionConfig
{
    const EXCEPTIONS_PROPERTIES = [
        1   => [
            'message'       => 'bingo bango bongo',
            'message_fe'    => 'CUSTOMIZED frontend message',
            'show_fe'       => true,
        ],
        2   => [
            'message'       => 'bish bash bosh',
            'context'       => 'Default context',
            'show_fe'       => true,
        ],
        3   => [
            'message'       => 'easy peasy lemon squeezy',
            'context'       => 'Another default context',
        ],
    ];
}
