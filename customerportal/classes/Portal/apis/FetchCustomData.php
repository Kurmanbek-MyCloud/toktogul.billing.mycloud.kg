<?php

class Portal_FetchCustomData_API extends Portal_Default_API {

	public function process(Portal_Request $request) {
		$customData = Vtiger_Connector::getInstance()->fetchCustomData();
		$result = array();
		$result['personal_account'] = $customData['personal_account'];
		$result['balance'] = $customData['balance'];
		$result['total_debt'] = $customData['total_debt'];
		$response = new Portal_Response();
		$response->setResult($result);
		return $response;
	}

}