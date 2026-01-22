<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Flats_Record_Model extends Vtiger_Record_Model {
	
		public function getTotalDebt() {

		global $adb;

		$id = $this->getId();

		/*$sql = "select sum(flatscf.cf_1289) from vtiger_flatscf flatscf 
			join vtiger_crmentity entity on entity.crmid = flatscf.flatsid
			where entity.deleted = 0 and flatscf.cf_1235 = $id";*/
		$query = "SELECT f.flatsid,f.flat,
					(SELECT IFNULL((SELECT SUM(total) FROM vtiger_invoice AS i 
					INNER JOIN vtiger_invoicecf AS icf ON icf.invoiceid = i.invoiceid
					INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = i.invoiceid
					WHERE icrm.deleted = 0 AND icf.cf_1265 = f.flatsid),0)) AS inv_sum,
					
					(SELECT IFNULL ((SELECT SUM(amount) FROM sp_payments AS p
					INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
					INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
					WHERE pcrm.deleted = 0 AND pcf.cf_1416 = f.flatsid),0)) AS pay_sum,
					
					((SELECT IFNULL((SELECT SUM(round(total)) FROM vtiger_invoice AS i 
					INNER JOIN vtiger_invoicecf AS icf ON icf.invoiceid = i.invoiceid
					INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = i.invoiceid
					WHERE deleted = 0 AND icf.cf_1265 = f.flatsid),0))
					-
					(SELECT IFNULL ((SELECT SUM(round(amount)) FROM sp_payments AS p
					INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
					INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
					WHERE deleted = 0 AND pcf.cf_1416 = f.flatsid),0))) AS total,
					c.contactid,
					c.lastname
						
			FROM vtiger_flats AS f 
			INNER JOIN vtiger_flatscf AS fcf ON fcf.flatsid = f.flatsid
			INNER JOIN vtiger_crmentity AS crm ON crm.crmid = f.flatsid
			LEFT JOIN vtiger_contactdetails AS c ON c.contactid = fcf.cf_1235
			LEFT JOIN vtiger_contactscf AS ccf ON ccf.contactid = c.contactid
			LEFT JOIN vtiger_contactaddress AS ca ON ca.contactaddressid =c.contactid
			LEFT JOIN vtiger_contactsubdetails AS csd ON csd.contactsubscriptionid = c.contactid
			WHERE crm.deleted = 0
			AND fcf.cf_1235 = $id";
		$debt_info = $adb->run_query_allrecords($query);
		
		$total_debt = 0;
		foreach ($debt_info as $row) {
			$total_debt += $row['total'];
			// var_dump($row['total']);
		}
		return $total_debt;
		// var_dump()

		// $sql = "SELECT sum(a.balance) FROM vtiger_invoice as a 
		// 		INNER JOIN vtiger_crmentity as b 
		// 		ON b.crmid=a.invoiceid WHERE b.deleted=0 and a.contactid=$id";
		// $invsum = $adb->run_query_field($sql);

		// $sql2 = "SELECT sum(a.amount) FROM sp_payments as a 
		// 		INNER JOIN vtiger_crmentity as b 
		// 		ON b.crmid=a.payid WHERE b.deleted=0 and a.payer=$id";
		// $paysum = $adb->run_query_field($sql2);

		// $res = number_format($invsum-$paysum, 0, '.', '');

		
	}
}