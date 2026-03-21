<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_0_0($module)
{
    Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'customer CHANGE person_type person_type varchar(255)');
    $sql = new DbQuery;
    $sql->from('customer');

    $customers = Db::getInstance()->executeS($sql);

    foreach ($customers as $customer) {
        if ($customer['person_type'] == '') {
            if ($customer['cnpj'] != '') {
                Db::getInstance()->update(
                    'customer',
                    [
                        'person_type' => 'pj',
                        'document_number' => pSQL($customer['cnpj'])
                    ],
                    'id_customer=' . (int)$customer['id_customer']
                );
            } elseif ($customer['cpf'] != '') {
                Db::getInstance()->update(
                    'customer',
                    [
                        'person_type' => 'pf',
                        'document_number' => pSQL($customer['cpf'])
                    ],
                    'id_customer=' . (int)$customer['id_customer']
                );
            } else {
                Db::getInstance()->update(
                    'customer',
                    [
                        'person_type' => 'nbr',
                        'document_number' => ''
                    ],
                    'id_customer=' . (int)$customer['id_customer']
                );
            }
        } else {
            if ($customer['person_type'] == 'pf') {
                Db::getInstance()->update(
                    'customer',
                    [
                        'document_number' => pSQL($customer['cpf'])
                    ],
                    'id_customer=' . (int)$customer['id_customer']
                );
            } elseif ($customer['person_type'] == 'pj') {
                Db::getInstance()->update(
                    'customer',
                    [
                        'document_number' => pSQL($customer['cnpj'])
                    ],
                    'id_customer=' . (int)$customer['id_customer']
                );
            } else {
                Db::getInstance()->update(
                    'customer',
                    [
                        'document_number' => ''
                    ],
                    'id_customer=' . (int)$customer['id_customer']
                );
            }
        }
    }

    $module->uninstallOverrides();
    $module->installOverrides();

    $brazil_id = Country::getByIso('BR');
    $tmp_addr_format = new AddressFormat($brazil_id);
    $tmp_addr_format->format = '
firstname lastname
Country:name
postcode
address1, number
address2
city-State:iso_code
phone
phone_mobile';
    $tmp_addr_format->id_country = $brazil_id;
    $tmp_addr_format->save();

    return true;
}
