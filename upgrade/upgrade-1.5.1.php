<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_5_1($module)
{
    $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'customer add column person_type varchar(2)';
    try {
        Db::getInstance()->execute($sql);
    } catch (Exception $e) {
    }

    $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'customer add column document_number varchar(20)';
    try {
        Db::getInstance()->execute($sql);
    } catch (Exception $e) {
    }

    $module->uninstallOverrides();
    $module->installOverrides();
    //atualiza as colunas person_type e document_number de cada cliente

    $sql = new DbQuery;
    $sql->select('id_customer')->from('customer');

    $customers = Db::getInstance()->executeS($sql);

    foreach (@$customers as $customer) {
        $obj = new Customer($customer['id_customer']);
        $obj->save();
    }

    return true;
}
