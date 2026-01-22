<?php
class Vtiger_GetNotifications_View extends Vtiger_Index_View {
	
	function checkPermission(){}
	
	function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign("NOTIFICATIONS_LIST", $this->getNotifications());
		$viewer->assign("MODULE", $moduleName);
		$viewer->view("NotificationsList.tpl", $moduleName);
	}
	
	private function getNotifications() {
		global $adb;
		$q = $adb->pquery("
			SELECT id,module,link,is_seen,assigned_to,UNIX_TIMESTAMP(added_time) as added_time, added_by FROM vtiger_notifications WHERE assigned_to = ? ORDER BY id DESC
		", array(Users_Record_Model::getCurrentUserModel()->getId()));
		$res = array();
		$rows = $adb->num_rows($q);
		if($rows) {
			for($i = 0;$i < $rows; ++$i) {
				$row = $adb->query_result_rowdata($q, $i);
				$matches = array();
				preg_match("/record=([\d]+)/", $row['link'], $matches);
				if(Vtiger_Record_Model::isEntityDeleted($matches[1])) continue;
				$model = Vtiger_Record_Model::getInstanceById($matches[1]);
				$text = "";
				$who = Vtiger_Record_Model::getInstanceById($row['added_by'], "Users");
				switch($row['module']) {
					case "Contacts":
					case "Leads":$text = $model->get("lastname") . " " . $model->get("firstname");break;
					case "Potentials":$text = $model->get("potentialname");break;
					case "Events":
					case "SalesOrder":$text = $model->get("subject");break;
				}
				if(time() - $row['added_time'] < 86400) $row['added_time'] = "Создано сегодня в " . date("H:i", $row['added_time']);
				else $row['added_time'] = date("d/m/Y в H:i", $row['added_time']);
				$res[$row['id']] = array(
					"seen" => $row['is_seen'],
					"module" => $row['module'],
					"link" => $row['link'],
					"added_time" => $row['added_time'],
					"text" => $text,
					"description" => $model->get("description"),
					"added_by" => $who->get("first_name") . " " . $who->get("last_name")
				);
			}
		}
		return $res;
	}
	
}