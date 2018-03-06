<?php

use MagicPush\EnterpriseException\CustomizableException\CustomizableException;

/**
 * CustomizableException base class example.
 *
 * @package CustomizableException\Examples
 */
abstract class CEExampleConfig extends CustomizableException
{
    const CLASS_CODE_LIST = [
        CEExampleA::class => 0, // CEExampleA doesn't use GlobalException feature
        CEExampleB::class => 1,
    ];

    const CLASS_SECTION_DEFAULT = 'example'; // the default section
    const CLASS_SECTION_LIST    = [
        CEExampleA::class => 'weee', // the custom section for CEExampleA
    ];
}
