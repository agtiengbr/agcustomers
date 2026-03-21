<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_2_0($module)
{    
    $options = unserialize(Configuration::get('AGCUSTOMERS_CONFIG'));
    $default_options = $module->getDefaultOptions();

    //adiciona as opções padrão de obrigatoriedade dos campos de endereço
    $options['fields']['address'] = $default_options['fields']['address'];
    Configuration::updateValue('AGCUSTOMERS_CONFIG', serialize($options));

    return true;
}