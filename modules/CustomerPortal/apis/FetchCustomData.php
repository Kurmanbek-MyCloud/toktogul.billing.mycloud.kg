<?php

class CustomerPortal_FetchCustomData extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {

		$current_user = $this->getActiveUser();
		$response = new CustomerPortal_API_Response();

		if ($current_user) {

			$customData = array();

			$customerId = $this->getActiveCustomer()->id;
			$contact = Vtiger_Record_Model::getInstanceById($customerId, 'Contacts');

			$contactDebt = $contact->getTotalDebt();
			$balance;$totaDebt;

			if ($contactDebt > 0) {
				$totalDebt = $contact->getTotalDebt();
			}else{
				$balance = $contact->getTotalDebt();
				$totaDebt = 0;
			}

			if ($contactDebt < 0) {
				$balance = $contact->getTotalDebt();
			}else{
				$balance = 0;
			}

			$customData['personal_account'] = $contact->get('cf_1225');
			$customData['balance'] = $balance;
			$customData['total_debt'] = $totalDebt;


			$response->setResult($customData);
		}

		return $response;
	}

}