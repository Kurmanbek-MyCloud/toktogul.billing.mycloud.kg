<?php
class Vtiger_GetNotificationsCount_Action extends Vtiger_Action_Controller {
	
	function checkPermission() {}
	
	function process(Vtiger_Request $request) {
		echo json_encode(array("count" => $this->getNotificationsCount()));
	}
	
	private function getNotificationsCount() {
		global $adb;
		$q = $adb->pquery("
			SELECT COUNT(*) as res FROM vtiger_notifications WHERE is_seen = 0
			AND assigned_to = ?
		", array(Users_Record_Model::getCurrentUserModel()->getId()));
		$fetch = $adb->query_result_rowdata($q, 0);
		return $fetch['res'];
	}
	
}