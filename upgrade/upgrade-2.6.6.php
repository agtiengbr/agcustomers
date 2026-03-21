<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_6_6($module)
{
    //instala os novos overrides
	$module->uninstallOverrides();
    $module->installOverrides();
    return true;
}
