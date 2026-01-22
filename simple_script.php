<?php
ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING
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

    $json = file_get_contents('php://input');
    $fd = fopen("test.txt", 'a+');
    fwrite($fd,$json.PHP_EOL);
    fclose($fd);

    $decoded_json = json_decode($json, false);

$query =  $adb->pquery("SELECT * FROM vtiger_wamessagescf", array()); 

// $names_arr = array(); 



$note = Vtiger_Record_Model::getCleanInstance("WAMessages");

$note->set('assigned_user_id', $current_user->id);
$note->set('cf_1428', $decoded_json->payload->destination);
$note->set('cf_1426', $decoded_json->payload->type);
$note->set('cf_1432', date('m-d-Y h:i:sa',  $decoded_json->timestamp));
$note->set('mode', 'create');
$note->save();


$id = $bill->getId();
  if ($id != null) {
    echo json_encode(array('success'=>true, 'id'=>$id));
  }
  else {
    echo json_encode(array('success'=>false));
  }
  
echo "<pre>";
var_dump($bill);
echo "</pre>";

