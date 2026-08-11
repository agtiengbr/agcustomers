<?php
class AdminAgCustomersLoadOptionsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        $customerId = Tools::getValue('id_customer');
        $email = Tools::getValue('email');
        $customer_data = [];

        if (Validate::isUnsignedId($customerId)) {
            $customer = new Customer((int) $customerId);
        } elseif (!empty($email) && $email !== 'undefined' && Validate::isEmail($email)) {
            $customer = (new Customer())->getByEmail($email);
        } else {
            $customer = null;
        }

        if (Validate::isLoadedObject($customer)) {
            $sql = new DbQuery();
            $sql->from('customer')->where('id_customer=' . (int) $customer->id);

            $customer_data = Db::getInstance()->getRow($sql);
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
