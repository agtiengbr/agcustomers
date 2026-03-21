<?php

class AgCustomerData 
{

    public static function getCustomerData($id_customer = null)
    {
        if (!is_null($id_customer)) {
            $customer = new Customer($id_customer);
        } else {
            $customer = Context::getContext()->customer;
        }

        if (Validate::isLoadedObject($customer)) {
            $sql = new DbQuery;
            $sql->from('customer')
                ->where('id_customer=' . (int)$customer->id);

            $db_data = Db::getInstance()->getRow($sql);

            return $db_data;
        }

        return (array) $customer;
    }
}