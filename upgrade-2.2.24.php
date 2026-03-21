<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_2_16($module)
{    
    $options = unserialize($module->getOptions());
    $options['config']['address']['position'] = 1;
    $options['config']['customer']['position'] = 1;

    Configuration::updateValue('AGCUSTOMERS_CONFIG', serialize($options));
    
    return true;
}