<?php

class Address extends AddressCore
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

    public function add($autodate = true, $null_values = false)
    {
        if (Tools::getIsSet('number_address_BO')) {
            $this->number = Tools::getValue('number_address_BO');
        }
        
        return parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {
        if (Tools::getIsSet('number_address_BO')) {
            $this->number = Tools::getValue('number_address_BO');
        }
        
        return parent::update($null_values);
    }
}
