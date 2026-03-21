<?php
class AdminAgCustomersPreviewController extends ModuleAdminController
{
    public function ajaxProcessPreviewOrder()
    {
        $id_order = Tools::getValue('id_order');
        $order = new Order($id_order);

        $sql = new DbQuery;
        $sql->select('id_order_carrier')
            ->from('order_carrier')
            ->where('id_order=' . (int)$id_order);

        $order_carrier = new OrderCarrier(Db::getInstance()->getValue($sql));


        $carrier = new Carrier($order_carrier->id_carrier);
        $customer_data = AgCustomerData::getCustomerData($order->id_customer);

        $address_shipping = new Address($order->id_address_delivery);
        $state_shipping = new State($address_shipping->id_state);
        $address_shipping->state = $state_shipping->name;
        $address_shipping->tracking_number = $order_carrier->tracking_number;
        $address_shipping->carrier_name = $carrier->name;

        $address_invoice = new Address($order->id_address_invoice);
        $state_invoice = new State($address_shipping->id_state);
        $address_invoice->state = $state_invoice->name;
        $address_invoice->email = $customer_data['email'];

        echo json_encode([
            'success' => true,
            'shipping_data_address' => $address_shipping,
            'invoice_data_address' => $address_invoice
        ]);

        exit();
    }
}
