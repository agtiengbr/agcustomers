<?php

class ObjectModel extends ObjectModelCore
{
    public static function getDefinition($class, $field = null)
    {
        $return = parent::getDefinition($class, $field);

        if ($class == 'Customer') {
            if (file_exists(_PS_MODULE_DIR_ . 'agcustomers/agcustomers.php')) {
                require_once _PS_MODULE_DIR_ . 'agcustomers/agcustomers.php';
                $module = new AgCustomers;
                $fields = $module->getFields();

                foreach ($fields['customer'] as $field) {
                    $return['fields'][$field['name']] = ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false];
                }
            }
        }

        return $return;
    }
}