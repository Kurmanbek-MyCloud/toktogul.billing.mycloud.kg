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

// echo "<var>";
// var_dump($current_user->id);
// echo "</var>";
// exit();
$res =  $adb->pquery("SELECT * FROM vtiger_contactscf", array()); 

$names_arr = array();

// foreach($res as $q){
//     if($q['cf_1223'] != ''){
// $names_arr[] = $q['cf_1223'];
//     }
// }

// $url = 'https://cntdev.ru/api';


// $options = array(
//   'offset' => '',
//   'count' => ''
// );

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($options));

// $response = curl_exec($ch);
// curl_close($ch);

// echo '<pre>';
// print_r($response);
// echo '</pre>';
// exit();

$bill = Vtiger_Record_Model::getCleanInstance("Invoice");
$bill->set('subject', 'test');
$bill->set('assigned_user_id', $current_user->id);
$bill->set('mode', 'create');
$bill->save();

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

// $ticket->save();

// echo "<pre>";
// var_dump($names_arr);
// echo "</pre>";