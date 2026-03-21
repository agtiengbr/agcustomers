<?php

class AgCustomersGoogleModuleFrontController extends ModuleFrontController
{


    public function initContent()
    {
        parent::initContent();
    }

    public function postProcess()
    {
        $is_duplicated = self::checkDuplicity();
        if($is_duplicated){
            self::authentication();
        }else{
            echo json_encode([
                'success' => true,
                'duplicated' => false
            ]);
        }

    }

    public function authentication()
    {
        $email = Tools::getValue('email');

        if (!$email) {
            echo json_encode(array(
                'success' => false,
                'error_type' => 'field.email.missing',
                'error_msg' => $this->trans('E-mail is missing.', array(), 'Modules.AgCustomers.Error')
            ));
            exit();
        }

        $c = new Customer();
        $c = $c->getByEmail($email);

        if (!Validate::isLoadedObject($c)) {
            echo json_encode(array(
                'success' => false,
                'error_type' => 'field.email.not_found',
                'error_msg' => $this->trans('E-mail is missing.', array(), 'Modules.AgCustomers.Error')
            ));
            exit();
        }

        $this->context->updateCustomer($c);

        if (!Validate::isLoadedObject($email)) {
            echo json_encode([
                'success' => true,
                'duplicated' => true
            ]);
            exit();
        }        
    }
    
    public function checkDuplicity()
    {
        $field_name = 'email';
        $value = Tools::getValue('email');
        $id_customer = $this->context->customer->id;


        $sql = new DbQuery;
        $sql->from('customer')
            ->select('id_customer')
            ->where($field_name . '="' . pSQL($value) . '"')
            ->where('id_customer!=' . (int)$id_customer);

        $is_duplicated = (bool)Db::getInstance()->getValue($sql);

        return $is_duplicated;
    }
}