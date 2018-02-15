<?php
require_once __DIR__ . '/../src/GlobalException.php';
require_once __DIR__ . '/../src/Examples/GlobalException/ExceptionConfig.php';
require_once __DIR__ . '/../src/Examples/GlobalException/AException.php';
require_once __DIR__ . '/../src/Examples/GlobalException/BException.php';
require_once __DIR__ . '/../src/Examples/GlobalException/CException.php';

use MagicPush\EnterpriseException\GlobalException;
use MagicPush\EnterpriseException\Examples\GlobalException\AException;
use MagicPush\EnterpriseException\Examples\GlobalException\BException;
use MagicPush\EnterpriseException\Examples\GlobalException\CException;

$base_code = 2;
$examples_to_show = [
    new AException('', $base_code),
    new BException('', $base_code),
    new CException('', $base_code),
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
