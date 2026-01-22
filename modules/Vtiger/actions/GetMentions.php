<?php
class Vtiger_GetMentions_Action extends Vtiger_Action_Controller {
	
	function checkPermission() {}
	
	function process(Vtiger_Request $request) {
		global $adb;
		$qStr = "SELECT id, first_name, last_name FROM vtiger_users ";
		if($request->has("candidate")) {
			$where = "";
			$candidate = trim($request->get("candidate"));
			foreach(explode(" ", $candidate) as $cand) {
				if($where != "") $where .= " OR ";
				$where .= "(first_name LIKE '%$cand%' OR last_name LIKE '%$cand%')";
			}
			$qStr .= "WHERE $where AND id <> " . $_SESSION['authenticated_user_id'];
		}
		else $qStr .= "WHERE id <> $_SESSION[authenticated_user_id]";
		$q = $adb->pquery($qStr);
		$res = array();
		$rows = $adb->num_rows($q);
		if(!$rows) $res['status'] = false;
		else {
			$res['status'] = true;
			$temp = array();
			for($i = 0; $i < $rows; ++$i) {
				$row = $adb->query_result_rowdata($q, $i);
				$temp[$row['id']] = $row['first_name'] . " " . $row['last_name'];
			}
			$res['data'] = $temp;
		}
		echo json_encode($res);
	}
	
}