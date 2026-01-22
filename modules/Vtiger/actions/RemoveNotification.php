<?php
class Vtiger_RemoveNotification_Action extends Vtiger_Action_Controller {
	
	function checkPermission() {}
	
	function process(Vtiger_Request $request) {
		global $adb;
		$record = $request->get("record");
		$adb->pquery("DELETE FROM vtiger_notifications WHERE id = ?", array($record));
	}
	
}