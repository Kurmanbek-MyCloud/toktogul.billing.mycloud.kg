<?php
class Vtiger_UpdateNotifications_Action extends Vtiger_Action_Controller {
	
	function checkPermission() {}
	
	function process(Vtiger_Request $request) {
		global $adb;
		$list = $request->get("list");
		$qStr = "";
		foreach($list as $id) {
			if($qStr != "") $qStr .= ",";
			$qStr .= $id;
		}
		$adb->pquery("UPDATE vtiger_notifications SET is_seen = 1 WHERE id IN($qStr)");
	}
	
}