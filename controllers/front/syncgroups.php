<?php


class AgcustomerssyncgroupsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $module = $this->module;
        $customers = Customer::getCustomers();


        foreach ($customers as $customer) {
            $customer = new Customer($customer['id_customer']);
            $module->updateGroupCustomer($customer);
        }

        echo json_encode([
            'status' => 'success'
        ]);

        exit();
    }
}
