<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_3_0($module)
{
    //instala os novos overrides
	$module->uninstallOverrides();
    $module->installOverrides();

    //marca todos os campos como editáveis pelo BO e pelo FO
    $options = Configuration::get('AGCUSTOMERS_CONFIG');
    $options = unserialize($options);

    foreach ($options['fields']['customer'] as &$field) {
        $field['edit_fo'] = 1;
        $field['edit_bo'] = 1;
    }

    $options = serialize($options);
    Configuration::updateValue('AGCUSTOMERS_CONFIG', $options);
	    
    return true;
}
