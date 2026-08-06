<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_9_1($module)
{
    $module->registerHook('validateCustomerFormFields');
    $module->uninstallOverrides();
    $module->installOverrides();

    return true;
}
