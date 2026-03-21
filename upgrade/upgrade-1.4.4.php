<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_4($module)
{
	Configuration::updateValue('AGCUSTOMERS_MASK_POSTCODE_INPUT', 1);
	    
    return true;
}
