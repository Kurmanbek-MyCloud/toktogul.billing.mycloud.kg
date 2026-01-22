<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: SalesPlatform Ltd
 * The Initial Developer of the Original Code is SalesPlatform Ltd.
 * All Rights Reserved.
 * If you have any questions or comments, please email: devel@salesplatform.ru
 ************************************************************************************/

class SPPayments_Module_Model extends Vtiger_Module_Model {

    /**
     * Function to check whether the module is summary view supported
     * @return Boolean - true/false
     */
    public function isSummaryViewSupported() {
        return false;
    }

    public function allCashSum(){
    	global $adb;

    	// $id = $this->getId();

    	$quer = $adb->pquery("SELECT sum(amount) as amount FROM sp_payments as a INNER JOIN vtiger_crmentity as b 
    										ON b.crmid=a.payid WHERE deleted=0 and pay_type='Receipt'", array());
    	$prihod = $adb->fetchByAssoc($quer);

    	$quer2 = $adb->pquery("SELECT sum(amount) as amount FROM sp_payments as a INNER JOIN vtiger_crmentity as b 
    										ON b.crmid=a.payid WHERE deleted=0 and pay_type='Expense'", array());
    	$rashod = $adb->fetchByAssoc($quer2);

    	// $res = print_r($quer2, true);

    	// $adb->pquery("INSERT INTO test (text) values ($res)");

    	$result = number_format($prihod['amount'] - $rashod['amount'], 2, '.', ' ');

    	return $result;
    }

}
