<?php
class AdminAgCustomersLoadOptionsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        $email = Tools::getValue('email');
        if ($email) {
            $customer = (new Customer)->getByEmail($email);

            $sql = new DbQuery;
            $sql->from('customer')->where('id_customer=' . (int)$customer->id);

            $customer_data = Db::getInstance()->getRow($sql);
        } else {
            $customer_data = [];
        }
        
        /** @var AgCustomers */
        $module = $this->module;
        
        echo json_encode([
            'success' => true,
            'customer_data' => $customer_data,
            'options' => $module->getOptions(),
            'id_language' => $this->context->language->id
        ]);
        exit();
    }
}
