<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_7_0($module)
{
    //instala os novos overrides
	$module->uninstallOverrides();
    $module->installOverrides();
    return true;
}
