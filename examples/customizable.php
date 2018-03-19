<?php
require_once __DIR__ . '/../src/GlobalException.php';
require_once __DIR__ . '/../src/CustomizableException/CustomizableException.php';
require_once __DIR__ . '/resources/CustomizableException/CEExampleConfig.php';
require_once __DIR__ . '/resources/CustomizableException/CEExampleA.php';
require_once __DIR__ . '/resources/CustomizableException/CEExampleB.php';

use MagicPush\EnterpriseException\CustomizableException\CustomizableException;
use MagicPush\EnterpriseException\Examples;


/**
 * Generates 3 exceptions with codes from 1 to 3 and shows some of their data.
 *
 * There is some special processing for certain exceptions to demonstrate some CustomizableException features.
 *
 * @param string $exception_class A qualified namespaced exception class name.
 *
 * @return void
 */
function showExample(string $exception_class)
{
    echo $exception_class . ":\n";

    for ($base_code = 1; $base_code <= 3; $base_code++) {
        /** @var CustomizableException $e */
        $e = new $exception_class(
            $base_code,
            'random value: ' . mt_rand(1, 1000) // exception details
        );

        if ($e instanceof Examples\CEExampleA && 2 == $base_code) {
            $e->setContext('REPLACED context');
        } elseif ($e instanceof Examples\CEExampleB && 1 == $base_code) {
            $e->setContext('ADDED context');
        }

        var_export(
            [
                'getCode()'      => $e->getCode(),
                'getCodeBase()'  => $e->getCodeBase(),
                'getMessage()'   => $e->getMessage(),
                'canShowFe()'    => $e->canShowFe(),
                'getMessageFe()' => $e->getMessageFe(),
            ]
        );

        echo "\n";
    }

    echo "----------\n";
}


showExample(Examples\CEExampleA::class);
showExample(Examples\CEExampleB::class);
