<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_2()
{
    //configura campo Aniversario
    $options = Configuration::get('AGCUSTOMERS_CONFIG');
    $options = unserialize($options);

    //verifica se já existe o campo de aniversario
    foreach ($options['fields']['customer'] as $key => $value) {
        if($value['name'] == 'birthday'){
            return true;
        }
    }

    $field = [
        'name' => 'birthday',
        'label' => 'Data de nascimento',
        'is_default_input' => 1,
        'required' => [
            'pf' => true,
            'pj' => true,
            'nbr' => true
        ],
        'insert' => [
            'pf' => true,
            'pj' => true,
            'nbr' => true
        ]
    ];

    $slc1 = array_slice($options['fields']['customer'], 0, 2);
    $slc2 = array_slice($options['fields']['customer'], 2, count($options['fields']['customer']));
        
    $slc1[] = $field;
        
    $options['fields']['customer'] = array_merge($slc1,$slc2);
    $options = serialize($options);

    Configuration::updateValue('AGCUSTOMERS_CONFIG', $options);
	    
    return true;
}
