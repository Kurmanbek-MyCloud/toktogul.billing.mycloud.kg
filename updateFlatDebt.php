<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/InventoryUtils.php';
require "config.inc.php"; 
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

 global $adb;
$test = $adb->pquery("UPDATE vtiger_flatscf fcf 
INNER JOIN vtiger_crmentity crm ON crm.crmid = fcf.flatsid 
SET fcf.cf_1289 = ((SELECT IFNULL((SELECT SUM(round(total)) FROM vtiger_invoice AS i 
						 INNER JOIN vtiger_invoicecf AS icf ON icf.invoiceid = i.invoiceid
						 INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = i.invoiceid
						 WHERE deleted = 0 AND icf.cf_1265 = fcf.flatsid),0))
						 -
						 (SELECT IFNULL ((SELECT SUM(round(amount)) FROM sp_payments AS p
						 INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
						 INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
						 WHERE deleted = 0 AND pcf.cf_1416 = fcf.flatsid),0)))
WHERE crm.deleted = 0 ",array());

// var_dump($test);