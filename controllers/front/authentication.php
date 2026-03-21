<?php

class AgCustomersAuthenticationModuleFrontController extends ModuleFrontController
{
    public function initContent()
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

        if (!Validate::isLoadedObject($email)) {
            echo json_encode(array(
                'success' => false,
                'error_type' => 'field.email.not_found',
                'error_msg' => $this->trans('E-mail is missing.', array(), 'Modules.AgCustomers.Error')
            ));
            exit();
        }

        $this->context->updateCustomer($c);

        if (!Validate::isLoadedObject($email)) {
            echo json_encode(array(
                'success' => true,
            ));
            exit();
        }        
    }
}
