<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl() . '&contact_id=' . $this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	function getCreateTaskUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl() . '&contact_id=' . $this->getId();
	}


	/**
	 * Function to get List of Fields which are related from Contacts to Inventory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields()
	{
		return array(
		  array('parentField' => 'account_id', 'inventoryField' => 'account_id', 'defaultValue' => ''),

		  //Billing Address Fields
		  array('parentField' => 'mailingcity', 'inventoryField' => 'bill_city', 'defaultValue' => ''),
		  array('parentField' => 'mailingstreet', 'inventoryField' => 'bill_street', 'defaultValue' => ''),
		  array('parentField' => 'mailingstate', 'inventoryField' => 'bill_state', 'defaultValue' => ''),
		  array('parentField' => 'mailingzip', 'inventoryField' => 'bill_code', 'defaultValue' => ''),
		  array('parentField' => 'mailingcountry', 'inventoryField' => 'bill_country', 'defaultValue' => ''),
		  array('parentField' => 'mailingpobox', 'inventoryField' => 'bill_pobox', 'defaultValue' => ''),

		  //Shipping Address Fields
		  array('parentField' => 'otherstreet', 'inventoryField' => 'ship_street', 'defaultValue' => ''),
		  array('parentField' => 'othercity', 'inventoryField' => 'ship_city', 'defaultValue' => ''),
		  array('parentField' => 'otherstate', 'inventoryField' => 'ship_state', 'defaultValue' => ''),
		  array('parentField' => 'otherzip', 'inventoryField' => 'ship_code', 'defaultValue' => ''),
		  array('parentField' => 'othercountry', 'inventoryField' => 'ship_country', 'defaultValue' => ''),
		  array('parentField' => 'otherpobox', 'inventoryField' => 'ship_pobox', 'defaultValue' => '')
		);
	}


	public function getBalance()
	{
		global $adb;
		$id = $this->getId();
		// $sql = "select balance from vtiger_contactdetails where contactid = $id";
		$sql = "SELECT sum(a.balance) FROM vtiger_invoice as a 
				INNER JOIN vtiger_crmentity as b 
				ON b.crmid=a.invoiceid WHERE b.deleted=0 and a.contactid=$id";
		$invsum = $adb->run_query_field($sql);

		$sql2 = "SELECT sum(a.amount) FROM sp_payments as a 
				INNER JOIN vtiger_crmentity as b 
				ON b.crmid=a.payid WHERE b.deleted=0 and a.payer=$id";
		$paysum = $adb->run_query_field($sql2);

		$res = number_format($invsum + $paysum, 0, '.', ',');

		return $res;
	}


	public function getTotalDebt()
	{

		global $adb;

		$id = $this->getId();

		// $query = "SELECT f.flatsid,f.flat,
		// 			(SELECT IFNULL((SELECT SUM(total) FROM vtiger_invoice AS i 
		// 			INNER JOIN vtiger_invoicecf AS icf ON icf.invoiceid = i.invoiceid
		// 			INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = i.invoiceid
		// 			WHERE icrm.deleted = 0 AND icf.cf_1265 = f.flatsid),0)) AS inv_sum,

		// 			(SELECT IFNULL ((SELECT SUM(amount) FROM sp_payments AS p
		// 			INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
		// 			INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
		// 			WHERE pcrm.deleted = 0 AND pcf.cf_1416 = f.flatsid),0)) AS pay_sum,

		// 			((SELECT IFNULL((SELECT SUM(round(total)) FROM vtiger_invoice AS i 
		// 			INNER JOIN vtiger_invoicecf AS icf ON icf.invoiceid = i.invoiceid
		// 			INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = i.invoiceid
		// 			WHERE deleted = 0 AND icf.cf_1265 = f.flatsid),0))
		// 			-
		// 			(SELECT IFNULL ((SELECT SUM(round(amount)) FROM sp_payments AS p
		// 			INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
		// 			INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
		// 			WHERE deleted = 0 AND pcf.cf_1416 = f.flatsid),0))) AS total,
		// 			c.contactid,
		// 			c.lastname

		// 	FROM vtiger_flats AS f 
		// 	INNER JOIN vtiger_flatscf AS fcf ON fcf.flatsid = f.flatsid
		// 	INNER JOIN vtiger_crmentity AS crm ON crm.crmid = f.flatsid
		// 	LEFT JOIN vtiger_contactdetails AS c ON c.contactid = fcf.cf_1235
		// 	LEFT JOIN vtiger_contactscf AS ccf ON ccf.contactid = c.contactid
		// 	LEFT JOIN vtiger_contactaddress AS ca ON ca.contactaddressid =c.contactid
		// 	LEFT JOIN vtiger_contactsubdetails AS csd ON csd.contactsubscriptionid = c.contactid
		// 	WHERE crm.deleted = 0
		// 	AND fcf.cf_1235 = $id";
		// $debt_info = $adb->run_query_allrecords($query);
		$query = "SELECT ROUND(SUM(cf_1289), 2)  as debt FROM vtiger_flatscf fcf
				INNER JOIN vtiger_crmentity vc ON vc.crmid = fcf.flatsid 
				WHERE vc.deleted = 0
				AND cf_1235 = $id";
		$debt_info = $adb->run_query_allrecords($query)[0]['debt'];

		return $debt_info;
	}

	public function getHousesInfo()
	{
		global $adb;

		$id = $this->getId();

		$houses_sql = $adb->run_query_allrecords("SELECT cf_1420, c.contactid, fcf.flatsid FROM vtiger_contactdetails c 
		INNER JOIN vtiger_flatscf fcf ON fcf.cf_1235 = c.contactid 
		INNER JOIN vtiger_crmentity ccrm ON ccrm.crmid = c.contactid 
		INNER JOIN vtiger_crmentity fcrm ON fcrm.crmid = fcf.flatsid
		WHERE ccrm.deleted = 0 
		AND fcrm.deleted = 0
		AND c.contactid = $id");
		// echo "<pre>";
		$houses_info = [];
		// $num = $adb->num_rows($houses_info);
		// for ($i=0; $i < $num; $i++) {
		// 	// array_push('asd',$houses_info); 
		// 	// $houses_info[$i]['contactid'] = $adb->query_result($houses_sql, $i, 'contactid');
		// 	// $houses_info[$i]['ls'] = $adb->query_result($houses_sql, $i, 'cf_1420');
		// 		// var_dump($adb->query_result($houses_sql, $i, 'cf_1420')); 
		// 	// $houses_info[] = 'sdf';
		// }
		// var_dump($houses_sql); 
		// foreach ($houses_sql as $i =>$row) {
		// $houses_info[$i]['ls'] = $adb->query_result($houses_sql, $i, 'cf_1420');
		// var_dump($i); 
		// var_dump($row); 
		# code...
		// }
		// echo "</pre>"; 
		// exit();
		// $houses_info['test123'] = $num;
		// $houses_info[$i]['ls'] = $adb->query_result($houses_sql, $i, 'cf_1420');
		// $houses_info['num'] = $num;
		// var_dump($adb->query_result($houses_sql, $i, 'contactid')); 
		// var_dump($adb->query_result($houses_sql, $i, 'cf_1420')); 
		return $houses_sql;
	}

}
