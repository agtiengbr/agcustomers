<?php

class AgCustomersimportFromFkCustomersModuleFrontController extends ModuleFrontController
{
	public function __construct()
	{
		parent::__construct();

		try {
			$sql = new DbQuery;
				$sql->select('cpf_cnpj, id_customer, cpf, cnpj')
				->from('customer');

			$documents = Db::getInstance()->executeS($sql);

			foreach ($documents as $document) {
				$document_number = preg_replace("/[^0-9]/","",$document['cpf_cnpj']);
				if (Tools::strlen($document_number) == 11 && empty($document['cpf'])) {
					Db::getInstance()->update('customer', ['cpf' => pSQL($document_number)], 'id_customer=' . (int)$document['id_customer']);
				} elseif (Tools::strlen($document_number) === 14 && empty($document['cnpj'])) {
					Db::getInstance()->update('customer', ['cnpj' => pSQL($document_number)], 'id_customer=' . (int)$document['id_customer']);
				}
			}

			Db::getInstance()->execute('UPDATE '. _DB_PREFIX_ . 'address SET number=numend where numend != ""');

			echo 'Importação concluída com sucesso!';
		} catch (Exception $e) {
			echo $e->getMessage();
		}

		exit();
	}
}