<?php
/* ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class LeadHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if($eventName === 'vtiger.lead.convertlead'){
			if(isset($entityData->so)) {
				global $adb;
				$data = array_merge($entityData->so, ["contact_id" => (explode("x", $entityData->entityIds['Contacts'])[1])]);
				$soInstance = Vtiger_Record_Model::getCleanInstance("SalesOrder");
				$data['productid'] = explode("x", $data['productid'])[1];
				foreach($data as $key => $val) $soInstance->set($key, $val);
				$soInstance->set("mode", "");
				$soInstance->set("assigned_user_id", $entityData->user_id);
				$soInstance->save();
				$adb->pquery("INSERT INTO vtiger_inventoryproductrel(id, productid, listprice, quantity) VALUES(?,?,?,?)", array(
					$soInstance->getID(), $data['productid'], $data['listprice'], 1
				));
			}
		}
	}
}

