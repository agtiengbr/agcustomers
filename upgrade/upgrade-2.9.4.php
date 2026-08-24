<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_9_4($module)
{
    $module->uninstallOverrides();
    $module->installOverrides();

    return true;
}
