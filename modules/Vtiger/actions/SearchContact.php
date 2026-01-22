<?php
class Vtiger_SearchContact_Action extends Vtiger_Action_Controller {
	
	function checkPermission() {}
	
	function process(Vtiger_Request $request) {
		echo json_encode($this->SearchContact($request->get('input')));
	}
	
	private function SearchContact($input) {
		global $adb;
		$q = $adb->pquery("SELECT CD.contactid, CD.lastname FROM vtiger_contactdetails AS CD
                        INNER JOIN vtiger_crmentity AS CRM ON CRM.crmid = CD.contactid
                        WHERE CD.lastname LIKE CONCAT('%',?,'%') AND deleted = 0
                        LIMIT 5", array($input));
		$res = array();
		for ($i=0; $i < $adb->num_rows($q); $i++) { 
			$row = $adb->query_result_rowdata($q, $i);
			$res[]=array($row['contactid'], $row['lastname']);
		}
		return $res;
	}
	
}