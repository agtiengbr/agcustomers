<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_7_3($module)
{
	$module->uninstallOverrides();
    $module->installOverrides();
    return true;
}
