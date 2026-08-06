<?php
class Customer extends CustomerCore
{
    public $company_name;
    public $cpf;
    public $rg;
    public $cnpj;
    public $ie;
    public $person_type;
    public $document_number;
    
    public function __construct($id = null) 
    {   
        if (file_exists(_PS_MODULE_DIR_ . 'agcustomers/agcustomers.php')) {
            require_once _PS_MODULE_DIR_ . 'agcustomers/agcustomers.php';
            $module = new AgCustomers;
            $fields = $module->getFields();
            foreach ($fields['customer'] as $field) {
                if (isset($field['name'])) {
                    if (isset($field['type']) && $field['type'] !== 'checkbox') {
                        self::$definition['fields'][$field['name']] = ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false];
                    @$this->{$field['name']} = '';
                    } else {
                        if (isset($field['options']) && is_array($field['options'])) {
                            foreach ($field['options'] as $option) {
                                $input_name = $field['name'] . $option['value'];
                                self::$definition[$input_name] = ['type' => self::TYPE_INT];
                            }
                        }
                    }
                }
            }
            self::$definition['fields']['person_type'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 2);
            self::$definition['fields']['document_number'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 20);
        }
        parent::__construct($id);
    }
    
    /*
    * module: agcustomers
    * date: 2023-11-10 07:04:04
    * version: 2.7.4
    */
    public function getFields() {
        $add_field = parent::getFields();
        $add_field['person_type'] = $this->person_type;
        if (class_exists('agcustomers')) {
            $module = new agcustomers;
            $fields = $module->getFields();
            foreach ($fields['customer'] as $field) {
                if (isset($this->{$field['name']})) {
                    $add_field[$field['name']] = pSQL($this->{$field['name']});
                }
            }
        }
        $add_field['document_number'] = pSQL($this->document_number);
        return $add_field;
    }
    /*
    * module: agcustomers
    * date: 2023-11-10 07:04:04
    * version: 2.7.4
    */
    public function add($auto_date = true, $null_values = false)
    {
        if (file_exists(_PS_MODULE_DIR_ . 'agcustomers/agcustomers.php')) {
            require_once _PS_MODULE_DIR_ . 'agcustomers/agcustomers.php';
        }
        if (Tools::getIsSet('person_type')) {
            $this->person_type = Tools::getValue('person_type');
        }
        if (class_exists('agcustomers')) {
            $module = new agcustomers;
            $fields = $module->getFields();
            foreach ($fields['customer'] as $field) {
                if ($field['type'] == 'checkbox') {
                    continue;
                }

                if (!@$field['insert'][$this->person_type]) {
                    $this->{$field['name']} = null;
                } elseif (Tools::getIsSet($field['name'])) {
                    if(!$field['is_default_input']){
                        $this->{$field['name']} = Tools::getValue($field['name']);
                    }
                }
            }
        }
        if ($this->person_type == 'pj') {
            $this->document_number = $this->cnpj;
        } else {
            $this->document_number = $this->cpf;
        }
        
        $return = parent::add($auto_date, $null_values);
        if ($return) {
            if (class_exists('agcustomers')) {
                $module = new agcustomers;
                $fields = $module->getFields();
                foreach ($fields['customer'] as $field) {
                    if ($field['type'] !== 'checkbox') {
                        continue;
                    }

                    $fields_update = [];

                    foreach ($field['options'] as $option) {
                        $input_name = $field['name'] . $option['value'];

                        if (Tools::getValue($input_name)) {
                            $fields_update[$input_name] = 1;
                        } else {
                            $fields_update[$input_name] = 0;
                        }
                    }

                    Db::getInstance()->update('customer', $fields_update, 'id_customer=' . $this->id);
                }
            }
        }

        return $return;
    }
    /*
    * module: agcustomers
    * date: 2023-11-10 07:04:04
    * version: 2.7.4
    */
    public function update($null_values = false)
    {
        if (Tools::getIsSet('person_type')) {
            $this->person_type = Tools::getValue('person_type');
        }        
        if (Validate::isLoadedObject(Context::getContext()->employee) || Tools::getIsSet('fields_opc') ||  @Context::getContext()->controller->module->name === 'agcheckout') {
            if (class_exists('agcustomers')) {
                $module = new agcustomers;
                $fields = $module->getFields();
                foreach ($fields['customer'] as $field) {
                    if ($field['type'] === 'checkbox') {
                        continue;
                    }

                    if (@$field['insert'][$this->person_type]) {
                        if(!$field['is_default_input'] && Tools::getIsSet($field['name'])){
                            $this->{$field['name']} = Tools::getValue($field['name']);
                        }
                    } elseif (Tools::getIsSet($field['name'])) {
                        $this->{$field['name']} = null;
                    }
                }
            }
        }
        
        if (class_exists('agcustomers')) {
            $module = new agcustomers;
            $fields = $module->getFields();
            $is_bo = Validate::isLoadedObject(Context::getContext()->employee);
            $sql = new DbQuery;
            $sql->from('customer')
                ->where('id_customer=' . $this->id);
            $current_data = Db::getInstance()->getRow($sql);
            foreach ($fields['customer'] as $field) {
                if (!@$field['insert'][$this->person_type]) {
                    $this->{$field['name']} = null;
                } elseif (Tools::getIsSet($field['name'])) {
                    if (!$field['is_default_input']) {
                        $this->{$field['name']} = Tools::getValue($field['name']);
                    }
                    
                }
                if (!$current_data[$field['name']]) {
                    continue;
                }
                if (
                    ($is_bo && !$field['edit_bo'])
                    || (!$is_bo && !$field['edit_fo'])
                ) {
                    $this->{$field['name']} = $current_data[$field['name']];
                }
            }
        }

        if ($this->person_type == 'pj') {
            $this->document_number = $this->cnpj;
        } else {
            $this->document_number = $this->cpf;
        }
        $return =  parent::update($null_values);

        if ($return) {
            if (class_exists('agcustomers')) {
                $module = new agcustomers;
                $fields = $module->getFields();
                foreach ($fields['customer'] as $field) {
                    if ($field['type'] !== 'checkbox') {
                        continue;
                    }

                    $fields_update = [];

                    foreach ($field['options'] as $option) {
                        $input_name = $field['name'] . $option['value'];

                        if (Tools::getValue($input_name)) {
                            $fields_update[$input_name] = 1;
                        } else {
                            $fields_update[$input_name] = 0;
                        }
                    }

                    Db::getInstance()->update('customer', $fields_update, 'id_customer=' . $this->id);
                }
            }
        }

        return $return;
    }
    /*
    * module: agcustomers
    * date: 2023-11-10 07:04:04
    * version: 2.7.4
    */
    public function validateField($fieldName, $value, $id_lang = null, $skip = [], $human_errors = false)
    {
        if (!file_exists(_PS_MODULE_DIR_ . 'agcustomers/agcustomers.php')) {
            return parent::validateField($fieldName, $value, $id_lang, $skip, $human_errors);
        }
        require_once _PS_MODULE_DIR_ . 'agcustomers/agcustomers.php';
        $module = new agcustomers;
        $fields = $module->getFields();

        $documentValidation = $module->validateCustomerDocument(
            $fieldName,
            $value,
            $this->person_type
        );
        if ($documentValidation !== true) {
            return $documentValidation;
        }

        $agcustomers_field = -1;
        foreach ($fields['customer'] as $field) 
        {
            if ($field['name'] != $fieldName || !$field['unique']) {
                continue;
            }
            $agcustomers_field = $field;
            break;
        }
        if ($agcustomers_field == -1) {
            return parent::validateField($fieldName, $value, $id_lang, $skip, $human_errors);
        }
        if ($this->{$agcustomers_field['name']} == '') {
            return true;
        }
        $sql = new DbQuery;
        $sql->from('customer')
            ->select('id_customer')
            ->where("{$agcustomers_field['name']} = '" . pSQL($this->{$agcustomers_field['name']}) . "'");
        
        if ($this->id) {
            $sql->where('id_customer!=' . $this->id);
        }
        $db_data = Db::getInstance()->getValue($sql);
        if ($db_data) {
            $label = isset($agcustomers_field['label'][Context::getContext()->language->id]) ? $agcustomers_field['label'][Context::getContext()->language->id] : (is_string($agcustomers_field['label']) ? $agcustomers_field['label'] : 'Field');
            $msg = Context::getContext()->getTranslator()->trans('%label% already registered.', ['%label%' => $label], 'Modules.Agcustomers.Shop');
            Logger::addLog('agcustomers - ' . $msg, 3, null, null, null, true);
            return $msg;
        }
        return true;
    }
}
