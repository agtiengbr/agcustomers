<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_6_0()
{
    $id_tab = Tab::getIdFromClassName('AdminAgCustomersConfig');
    if (!$id_tab) {
        $tab = new Tab();
        $tab->class_name = 'AdminAgCustomersConfig';
        $tab->id_parent = Tab::getIdFromClassName('AdminParentCustomer');
        $tab->active = 1;
        $tab->module = 'agcustomers';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Configurações dos Cadastros';
        }
        $tab->add();  
    }
    return true;
}
