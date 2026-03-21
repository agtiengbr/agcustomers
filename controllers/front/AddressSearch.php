<?php

class AgCustomersAddressSearchModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
        $postcode = Tools::getValue('postcode');
        $options = $this->module->getOptions();

        $address = $this->module->findAddressByPostcode($postcode, $options['config']['cep_provider'] ?? null);
		echo json_encode($address);
		
		exit();
	}
}