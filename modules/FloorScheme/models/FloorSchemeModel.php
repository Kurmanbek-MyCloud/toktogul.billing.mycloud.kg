<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************* */

class FloorScheme_Model
{
	static function listAll()
	{
		global $adb;
		$floors = [];

		$floors_sql = "SELECT * FROM vtiger_floorscheme";
		$floors_sql_result = $adb->pquery($floors_sql, []);

		$attachments_sql = "SELECT *
			FROM vtiger_seattachmentsrel 
			JOIN vtiger_attachments
			ON vtiger_seattachmentsrel.attachmentsid 
			= vtiger_attachments.attachmentsid
			WHERE crmid = ?";

		while ($floor = $adb->fetchByAssoc($floors_sql_result)) {
			$floor_plan_result = $adb->pquery($attachments_sql, [$floor['floorschemeid']]);
			$floor_plan_info = null;
			while ($info = $adb->fetchByAssoc($floor_plan_result, -1)) {
				if ($info) $floor_plan_info = $info;
			};

			$floor['floor_plan'] = $floor_plan_info['path'] . $floor_plan_info['attachmentsid'] . '_' . $floor_plan_info['name'];

			$floors[] = $floor;

			$spaces = [];

			$spaces_sql = "SELECT * FROM vtiger_workspace WHERE floor_number = " . $floor['floorschemeid'];
			$spaces_result = $adb->pquery($spaces_sql);

			while ($space = $adb->fetchByAssoc($spaces_result, -1)) {
				$organization_logo_result = $adb->pquery($attachments_sql, [$space['workspaceid']]);
				$organization_logo_info = null;
				while ($info = $adb->fetchByAssoc($organization_logo_result, -1)) {
					if ($info) $organization_logo_info = $info;
				};

				$space['organization_logo'] = $organization_logo_info['path'] . $organization_logo_info['attachmentsid'] . '_' . $organization_logo_info['name'];

				$spaces[] = $space;
			}

			$last_floors_index = count($floors) - 1;
			$floors[$last_floors_index]['spaces'] = $spaces;
		}

		return $floors;
	}

	static function getById($floor_id)
	{
		global $adb, $log;

		$sql = "SELECT * FROM vtiger_floorscheme WHERE floorschemeid = $floor_id";
		$result = $adb->pquery($sql, array());

		return $adb->fetch_array($result);
	}
}
