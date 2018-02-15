<?php
namespace MagicPush\EnterpriseException\Examples\GlobalException;

use MagicPush\EnterpriseException\GlobalException;
use MagicPush\EnterpriseException\Examples\GlobalException as Examples;

/**
 * The GlobalException base class example.
 *
 * @package GlobalException\Examples
 */
abstract class ExceptionConfig extends GlobalException
{
    const CLASS_CODE_LIST = [
        // AException doesn't use GlobalException feature
        Examples\BException::class  => 13,
        Examples\CException::class  => 42,
    ];
}
