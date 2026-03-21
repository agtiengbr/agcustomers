<?php

class AgCustomersUpdateBlingDataModuleFrontController extends ModuleFrontController
{
    public function initContent()
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

        exit();
    }
}