<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_2_16($module)
{    
    $options = $module->getOptions();
    if (!is_array($options)) {
        $options = unserialize($options);
    }
    $options['config']['cep_provider'] = 'agti';

    Configuration::updateValue('AGCUSTOMERS_CONFIG', serialize($options));
    
    return true;
}