<?php
namespace MagicPush\EnterpriseException\Examples;

use MagicPush\EnterpriseException\GlobalException;

/**
 * GlobalException base class example.
 *
 * @package Examples\GlobalException
 */
abstract class GEExampleConfig extends GlobalException
{
    const CLASS_CODE_LIST = [
        // GEExampleA doesn't use GlobalException feature
        GEExampleB::class => 13,
        GEExampleC::class => 42,
    ];
}
