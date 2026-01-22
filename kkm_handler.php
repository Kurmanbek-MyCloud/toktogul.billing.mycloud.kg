<?php 
if($_SERVER['REQUEST_METHOD'] != "POST")  exit();
if(!isset($_POST['action'])){
  // echo json_encode($_POST); 
  echo json_encode(array('error'=>'action is not set')); 
  exit();
}
require_once 'Logger.php';

require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/InventoryUtils.php';
require "config.inc.php"; 
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

$assigned_user_id = 1;
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile( $assigned_user_id );
$logger = new CustomLogger('bot_ticket_handler.log');


if ($_POST['action'] == 'ping'){
    echo json_encode(array('success'=>true));
}

if ($_POST['action'] == 'authorization'){
    $user = $_POST['username'];
    $userName = substr($_POST['username'], 0, 2);
	$pwd = $_POST['password'];
	$salt = '$1$' . str_pad($userName, 9, '0');
    $hash = crypt($pwd, $salt);
    $sql = "select * from vtiger_users vu where user_name = '$user' and user_password = '$hash'";
	$invsum = $adb->run_query_allrecords($sql);
    echo "<pre>";
    var_dump($invsum[0]);
    echo "</pre>";
    exit();
    echo json_encode(array('success'=>$invsum));
}