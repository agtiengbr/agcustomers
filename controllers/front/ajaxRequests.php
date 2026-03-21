<?php

class AgCustomersajaxRequestsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $action = Tools::getValue('action');

        if (method_exists($this, $action)) {
            $this->{$action}();
        }

        exit();
    }

    public function getMasks()
    {
        $id_country = Tools::getValue('id_country');
        $country = new Country($id_country);

        echo json_encode([
            'success' => true,
            'masks' => [
                'zipcode' => $country->zip_code_format
            ]
        ]);

        exit();
    }

    public function checkDuplicity()
    {
        $field_name = Tools::getValue('field_name');
        $value = Tools::getValue('value');
        $id_customer = $this->context->customer->id;


        $sql = new DbQuery;
        $sql->from('customer')
            ->select('id_customer')
            ->where($field_name . '="' . pSQL($value) . '"')
            ->where('id_customer!=' . (int)$id_customer);

        $is_duplicated = (bool)Db::getInstance()->getValue($sql);
        echo json_encode([
            'success' => true,
            'duplicated' => $is_duplicated
        ]);

        exit();
    }
}