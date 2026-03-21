<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_5($module)
{
	Configuration::updateValue('AGCUSTOMERS_INSERT_CUSTOMER_FIELDS', Configuration::get('insert_additional_fields'));
    Configuration::updateValue('AGCUSTOMERS_INSERT_COMPANY_FIELDS', Configuration::get('insert_additional_fields'));
    Configuration::updateValue('AGCUSTOMERS_INSERT_NUMBER_FIELD', Configuration::get('insert_additional_fields'));

    Configuration::updateValue('AGCUSTOMERS_1_4_5', 1);

    return true;
}
