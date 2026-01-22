<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
ob_clean();
ini_set('memory_limit', -1);
include_once 'includes/Loader.php';
include_once 'include/utils/utils.php';
include_once 'include/utils/InventoryUtils.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

global $adb;
global $current_user;
$current_user = Users::getActiveAdminUser();
require_once 'Logger.php';
$logger = new CustomLogger('updateDebt.log');



$flats_sql_result = $adb->pquery("SELECT f.flatsid FROM vtiger_flats f
inner join vtiger_flatscf fcf on f.flatsid = fcf.flatsid 
INNER JOIN vtiger_crmentity fcrm on fcf.flatsid = fcrm.crmid 
WHERE fcrm.deleted = 0");


for ($i = 0; $i <= $adb->num_rows($flats_sql_result); $i++) {
  $flatsid = $adb->query_result($flats_sql_result, $i, 'flatsid');

  $flats = Vtiger_Record_Model::getInstanceById($flatsid, "Flats");
  $flats->set('mode', 'edit');
  $flats->save();

  $logger->log("#$i Объект успешно обновлен. ID Дома: $flatsid");
}