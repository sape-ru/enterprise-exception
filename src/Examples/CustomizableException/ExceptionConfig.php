<?php
namespace MagicPush\EnterpriseException\Examples\CustomizableException;

use MagicPush\EnterpriseException\CustomizableException\CustomizableException;
use MagicPush\EnterpriseException\Examples\CustomizableException as Examples;

/**
 * The CustomizableException base class example.
 *
 * @package CustomizableException\Examples
 */
abstract class ExceptionConfig extends CustomizableException
{
    const CLASS_CODE_LIST = [
        Examples\AException::class  => 1,
        Examples\BException::class  => 0,   // BException doesn't use GlobalException feature
    ];

    const CLASS_SECTION_DEFAULT = 'example'; // the default section
    const CLASS_SECTION_LIST    = [
        Examples\AException::class  => 'weee', // the custom section for the AException
    ];
}
