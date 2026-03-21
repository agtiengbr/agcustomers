<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_5_3($module)
{
    $module->registerHook('actionObjectCustomerAddAfter');
    $module->registerHook('actionObjectCustomerUpdateAfter');

    return true;
}
