<?php
require_once __DIR__ . '/../src/GlobalException.php';
require_once __DIR__ . '/../src/CustomizableException/CustomizableException.php';
require_once __DIR__ . '/../src/CustomizableException/Parser.php';
require_once __DIR__ . '/resources/CustomizableException/CEExampleConfig.php';
require_once __DIR__ . '/resources/CustomizableException/CEExampleA.php';
require_once __DIR__ . '/resources/CustomizableException/CEExampleB.php';

use MagicPush\EnterpriseException\CustomizableException\Parser;
use MagicPush\EnterpriseException\Examples;

try {
    $section_to_include = 'weee';
    echo "...CustomizableException\ExceptionConfig exceptions with '$section_to_include' "
        . "section only (short format):\n";
    echo var_export(
            Parser::parse(Examples\CEExampleConfig::class, [], ['class_section_list_in' => [$section_to_include]]),
            true
        )
        . "\n";

    echo "----------\n";

    echo 'All ...CustomizableException\ExceptionConfig exceptions (extended format):' . "\n";
    echo var_export(Parser::parse(Examples\CEExampleConfig::class, ['is_extended' => true]), true) . "\n";
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
}
