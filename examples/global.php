<?php
require_once __DIR__ . '/../src/GlobalException.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleConfig.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleA.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleB.php';
require_once __DIR__ . '/resources/GlobalException/GEExampleC.php';

use MagicPush\EnterpriseException\GlobalException;

$base_code = 2;
$examples_to_show = [
    new GEExampleA('', $base_code),
    new GEExampleB('', $base_code),
    new GEExampleC('', $base_code),
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
