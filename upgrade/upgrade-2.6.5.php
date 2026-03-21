<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_6_5(AgCustomers $module)
{
    $module->installWorkers();

    $module->registerHook('actionCustomerAccountAdd');
    $module->registerHook('actionCustomerAccountUpdate');
	    
    return true;
}
