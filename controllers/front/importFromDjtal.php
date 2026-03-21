<?php

class AgCustomersimportFromDjtalModuleFrontController extends ModuleFrontController
{
	public function __construct()
	{
		parent::__construct();

		try {
			$sql = new DbQuery;
				$sql->from('djtalbrazilianregister');

			$documents = Db::getInstance()->executeS($sql);
			foreach ($documents as $document) {
				$cpf = $document['cpf'];
				$cnpj = $document['cnpj'];
				$ie = $document['ie'];
				$rg = $document['rg'];
				$company = $document['comp'];

				Db::getInstance()->update(
					'customer',
					[
						'cpf'          => pSQL($cpf),
						'cnpj'         => pSQL($cnpj),
						'rg'           => pSQL($rg),
						'ie'           => pSQL($ie),
						'company_name' => pSQL($company)
					],
					'id_customer=' . (int)$document['id_customer']
				);
			}


			$sql = new DbQuery;
				$sql->from('address');

			$addresses = Db::getInstance()->executeS($sql);
			foreach ($addresses as $address) {
				$add1 = $address['address1'];
				$exploded = explode(',', $add1);

				//sem número
				if (count($exploded) < 2) {
					continue;
				}

				$number = trim($exploded[count($exploded) - 1]);
				array_pop($exploded);
				$add1 = implode(',', $exploded);

				Db::getInstance()->update(
					'address',
					[
						'address1' => pSQL($add1),
						'number'   => psQL($number)
					],
					'id_address=' . (int)$address['id_address']
				);
			}

			echo 'Importação concluída com sucesso!';
		} catch (Exception $e) {
			echo $e->getMessage();
		}

		exit();
	}
}
