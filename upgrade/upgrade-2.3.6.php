<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_3_6()
{
    //configura o provedor de CEPs como AGTI
    
    $options = Configuration::get('AGCUSTOMERS_CONFIG');
    $options = unserialize($options);
    $options['config']['cep_provider'] = 'agti';
    $options = serialize($options);
    Configuration::updateValue('AGCUSTOMERS_CONFIG', $options);
	    
    return true;
}
