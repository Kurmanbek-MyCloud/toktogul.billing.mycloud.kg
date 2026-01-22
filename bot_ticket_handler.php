<?php
if ($_SERVER['REQUEST_METHOD'] != "POST")
  exit();
if (!isset($_POST['action'])) {
  // echo json_encode($_POST); 
  echo json_encode(array('error' => 'action is not set'));
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

define('URL', $site_URL . '/webservice.php');
define('KEY', 'BxEZilsTVwrDQrl9');
// $ticket = Vtiger_Record_Model::getInstanceById(1186,"HelpDesk");
$assigned_user_id = 30;
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile($assigned_user_id);
$logger = new CustomLogger('bot_ticket_handler.log');


if ($_POST['action'] == 'test') {
  $token = get_token();
  $sessionName = get_sessionName($token);
  $data = array(
    'pay_date' => date('Y-m-d'),
    'pay_type' => 'Receipt',
    'payer' => '12x' . $_POST['payer'],
    'spstatus' => 'Executed',
    'type_payment' => 'Cash Payment',
    'assigned_user_id' => '19x' . $_POST['assigned_user_id'],
    'amount' => $_POST['amount'],
    'cf_1295' => 'TelegramBot'
  );
  $creation_response = create_entity($sessionName, json_encode($data), 'SPPayments');
  logout($sessionName);
  if ($creation_response['success'] == false) {
    $logger->log($_POST['action'] . ": ERROR! code: " . $creation_response['error']['code'] . ' message: ' . $creation_response['error']['message']);
  }
  if ($creation_response == null) {
    $logger->log($_POST['action'] . ": ERROR! Что то непонятное произошло");
  }

  var_dump($creation_response);


  var_dump(PHP_EOL);
  var_dump($token);
  var_dump($sessionName);
  var_dump(logout($sessionName));
  echo "test\n";
  echo $site_URL . "\r";
  echo URL;

}
// if ($_POST['action'] == 'testmeter'){
//   $token = get_token();
//   $sessionName = get_sessionName($token);
//   $data = array(
//     // 'pay_date' => date('Y-m-d'),
//     // 'pay_type' => 'Receipt',
//     // 'payer' => '12x'.$_POST['payer'],
//     // 'spstatus' => 'Executed',
//     // 'type_payment' => 'Cash Payment',
//     'assigned_user_id' => '19x'.$_POST['assigned_user_id'],
//     'meter' => 'testmeter',
//     'cf_1323' => '11x1028'
//     // 'cf_1416' => '46x'.2390
//   );
//   $creation_response = create_entity($sessionName,json_encode($data),'Meters');
//   logout($sessionName);
//   if($creation_response['success'] == false){
//     $logger->log($_POST['action'] .": ERROR! code: ".$creation_response['error']['code'] .' message: '.$creation_response['error']['message']);
//   }
//   if($creation_response == null){
//     $logger->log($_POST['action'] .": ERROR! Что то непонятное произошло");
//   }
//   // if($creation_response['success'] == true){
//   //   $logger->log($_POST['action'] .": ERROR! code:".$creation_response['error']['code'] .' message:'.$creation_response['error']['message']);
//   // }
//   var_dump($creation_response);
//   // var_dump($data);

//   var_dump(PHP_EOL);
//   var_dump($token);
//   var_dump($sessionName);
//   var_dump(logout($sessionName));
//   echo "test\n";
//   echo $site_URL."\r";
//   echo URL;

// }

if ($_POST['action'] == 'create') {
  $ticket = Vtiger_Record_Model::getCleanInstance("HelpDesk");
  $ticket->set('ticket_title', $_POST['ticket_title']);
  $ticket->set('assigned_user_id', $_POST['assigned_user_id']);
  $ticket->set('contact_id', $_POST['contact_id']);
  $ticket->set('parent_id', $_POST['parent_id']);
  $ticket->set('ticketpriorities', $_POST['ticketpriorities']);
  $ticket->set('ticketstatus', $_POST['ticketstatus']);
  $ticket->set('ticketcategories', $_POST['ticketcategories']);
  $ticket->set('description', $_POST['description']);
  $ticket->set('solution', $_POST['solution']);
  $ticket->set('cf_1279', $_POST['cf_1279']);
  $ticket->set('cf_1424', $_POST['cf_1424']);
  $ticket->set('created_user_id', $_POST['assigned_user_id']);
  $ticket->set('mode', 'create');
  $ticket->save();
  $id = $ticket->getId();
  if ($id != null) {
    echo json_encode(array('success' => true, 'id' => $id));
  } else {
    echo json_encode(array('success' => false));
  }
  //       echo"<pre>";
  //   // var_dump(date('Y-m-d H:i:s', strtotime('+2 hours')));
  //   var_dump($test);
  // var_dump($ticket);
  // echo"</pre>";
  exit();
}
if ($_POST['action'] == 'translate') {
  $word = $_POST['word'];
  $module = $_POST['module'];
  $translated = vtranslate($word, $module);
  echo json_encode($translated);
  exit();
}
if ($_POST['action'] == 'getDebt') {
  $id = $_POST['contactid'];
  global $adb;
  $sql = "SELECT sum(a.balance) FROM vtiger_invoice as a 
			INNER JOIN vtiger_crmentity as b 
			ON b.crmid=a.invoiceid WHERE b.deleted=0 and a.contactid=$id";
  $invsum = $adb->run_query_field($sql);
  $sql2 = "SELECT sum(a.amount) FROM sp_payments as a 
			INNER JOIN vtiger_crmentity as b 
			ON b.crmid=a.payid WHERE b.deleted=0 and a.payer=$id";
  $paysum = $adb->run_query_field($sql2);

  // $res = number_format($invsum-$paysum, 0, '.', ',');
  $res = $invsum - $paysum;
  $res = round($res, 2);

  echo json_encode($res);
  exit();
}
if ($_POST['action'] == 'createPayment') {

  $payment = Vtiger_Record_Model::getCleanInstance("SPPayments");
  $payment->set('pay_date', date('Y-m-d'));
  $payment->set('pay_type', 'Receipt');
  $payment->set('assigned_user_id', $_POST['assigned_user_id']);
  $payment->set('spstatus', 'Executed');
  $payment->set('created_user_id', $_POST['assigned_user_id']);
  $payment->set('type_payment', 'Cash Payment');
  $payment->set('amount', $_POST['amount']);
  $payment->set('payer', $_POST['payer']);
  $payment->set('cf_1295', 'TelegramBot');
  $payment->set('cf_1416', $_POST['flatid']);
  $payment->set('mode', 'create');
  // var_dump($payment);
  // exit();
  $payment->save();
  // var_dump();
  $id = $payment->getId();
  // var_dump($id);
  if ($id != null) {
    $contact = Vtiger_Record_Model::getInstanceById($_POST['payer'], "Contacts");
    $fio = $contact->get('lastname');
    $pay_no = $payment->get('pay_no');
    $date = date('d-m-Y');
    echo json_encode(array('success' => true, 'id' => $id, 'fio' => $fio, 'date' => $date, 'amount' => $_POST['amount'], 'pay_no' => $pay_no));
    $logger->log($_POST['action'] . " 'success' => true, 'id'=>$id,'fio'=>$fio,'date'=>$date,'amount'=>" . $_POST['amount'] . ",'pay_no' => $pay_no");

  } else {
    echo json_encode(array('success' => false));
  }
  exit();
  // echo json_encode($_POST);

  // echo '<pre>';
  // var_dump($invsum);
  // var_dump($paysum);
  // var_dump($res);
  // echo '</pre>';
  // echo json_encode($res);
}
if ($_POST['action'] == 'createContact') {
  $contact = Vtiger_Record_Model::getCleanInstance("Contacts");
  $contact->set('assigned_user_id', $_POST['assigned_user_id']);
  $contact->set('lastname', $_POST['fio']);
  // $contact->set('cf_1420', $house_id);
  $contact->set('mode', 'create');
  $contact->save();
  $contact_id = $contact->getId();

  $house = Vtiger_Record_Model::getCleanInstance("Flats");
  $house->set('flat', $_POST['house_no']);
  $house->set('cf_1203', $_POST['street']);
  $house->set('cf_1235', $contact_id);
  $house->set('assigned_user_id', 1);
  $house->set('mode', 'create');
  $house->save();
  $house_id = $house->getId();
  // echo '<pre>';
  // // var_dump($current_user);
  // var_dump($id);
  // var_dump($meter);
  // echo '</pre>';
  if ($contact_id != null) {
    echo json_encode(array('success' => true, 'id' => $contact_id, 'houseid' => $house_id));
  } else {
    echo json_encode(array('success' => false));
  }
  exit();
}

function get_json($obj)
{
  return json_decode(json_encode(json_decode($obj)), true);
}

function get_token()
{
  $token = get_json(file_get_contents(URL . '?operation=getchallenge&username=admin'));
  return $token['result']['token'];
}

function get_sessionName($accessKey)
{
  $headers = stream_context_create(
    array(
      'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
        'content' => 'operation=login&username=admin&accessKey=' . md5($accessKey . KEY)
      ),
    )
  );
  $sessionName = get_json(file_get_contents(URL, false, $headers));
  return $sessionName['result']['sessionName'];
}

function create_entity($sessionName, $element, $type)
{
  $headers = stream_context_create(
    array(
      'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
        'content' => 'operation=create&sessionName=' . $sessionName . '&element=' . $element . '&elementType=' . $type
      ),
    )
  );
  return $answer = get_json(file_get_contents(URL, false, $headers));
}

function logout($sessionName)
{
  $headers = stream_context_create(
    array(
      'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
        'content' => 'operation=logout&sessionName=' . $sessionName
      ),
    )
  );
  $answer = get_json(file_get_contents(URL, false, $headers));
}



// echo"<pre>";
// // var_dump(vtranslate("Low",'HelpDesk'));
// var_dump($ticket);
// echo"</pre>";
// exit();

?>