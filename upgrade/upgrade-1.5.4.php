<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_5_4($module)
{
    Configuration::updateValue('AGCUSTOMERS_REQUIRE_NUMBER_FIELD', 1);

    return true;
}
