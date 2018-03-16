<?php
require_once __DIR__ . '/../src/GlobalException.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleConfig.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleA.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleB.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleC.php';

use MagicPush\EnterpriseException\GlobalException;
use MagicPush\EnterpriseException\Examples;

$base_code = 2;
$examples_to_show = [
    new Examples\GEExampleA('', $base_code),
    new Examples\GEExampleB('', $base_code),
    new Examples\GEExampleC('', $base_code),
];
foreach ($examples_to_show as $e) {
    /** @var GlobalException $e */
    echo sprintf(
        "class '%s', getCode(): %d, %s\n",
        get_class($e),
        $e->getCode(),
        json_encode($e::getCodeParts($e->getCode()))
    );
}
