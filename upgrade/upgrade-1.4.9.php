<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_9($module)
{
    Configuration::updateValue('AGCUSTOMERS_INSERT_RG_FIELD', Configuration::get('insert_additional_fields'));

    return true;
}
