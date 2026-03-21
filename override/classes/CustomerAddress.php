<?php

class CustomerAddress extends AddressCore
{
    public $number;

    public function __construct($id = null) 
    {
        self::$definition['fields']['number'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 20);
        parent::__construct($id);
    }
    
    public function getFields() {
        $add_field = parent::getFields();
        $add_field['number'] = pSQL($this->number);

        return $add_field;
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (Tools::getIsSet('fields')) {
            $fields = json_encode(Tools::getValue('fields'));

            foreach ($fields as $field) {
                if ($field['object'] == 'delivery' && $field['name'] == 'number') {
                    $this->number = $field['value'];
                    break;
                }
            }
        }
        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        if (Tools::getIsSet('fields')) {
            $fields = json_encode(Tools::getValue('fields'));

            foreach ($fields as $field) {
                if ($field['object'] == 'delivery' && $field['name'] == 'number') {
                    $this->number = $field['value'];
                    break;
                }
            }
        }
       
        return parent::update($null_values);
    }
}
