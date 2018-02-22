<?php
require_once __DIR__ . '/../src/GlobalException.php';
require_once __DIR__ . '/../src/CustomizableException/CustomizableException.php';
require_once __DIR__ . '/../src/CustomizableException/Parser.php';
require_once __DIR__ . '/resources/CustomizableException/ExceptionConfig.php';
require_once __DIR__ . '/resources/CustomizableException/AException.php';
require_once __DIR__ . '/resources/CustomizableException/BException.php';

use MagicPush\EnterpriseException\Examples\CustomizableException\ExceptionConfig;
use MagicPush\EnterpriseException\CustomizableException\Parser;

try {
    $section_to_include = 'weee';
    echo "...CustomizableException\ExceptionConfig exceptions with the '$section_to_include' "
        . "section only (short format):\n";
    echo var_export(
            Parser::parse(ExceptionConfig::class, [], ['class_section_list_in' => [$section_to_include]]),
            true
        )
        . "\n";

    echo "----------\n";

    echo 'All ...CustomizableException\ExceptionConfig exceptions (extended format):' . "\n";
    echo var_export(Parser::parse(ExceptionConfig::class, ['is_extended' => true]), true) . "\n";
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
}
