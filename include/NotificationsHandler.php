<?php
function botNotify($ws_entity) {
	global $adb;
	$data =$ws_entity->data;
	$invoice_id = explode('x',$ws_entity->getId())[1];
	$contact_id = explode('x',$data['contact_id'])[1];
	$flat = '';
	$debt = '';
	$stret = '';
	$house_no = '';
	if ($data['cf_1265'] != null){
		$flat_id = explode('x',$data['cf_1265'])[1];
		$res = $adb->pquery('select * from vtiger_flats as f 
									inner join vtiger_flatscf as fcf on fcf.flatsid = f.flatsid
									LEFT JOIN vtiger_houses AS h ON h.housesid = fcf.cf_1203 
									LEFT JOIN vtiger_housescf AS hcf ON hcf.housesid = h.housesid
									where f.flatsid = ? ', array($flat_id));
		$flat = $adb->query_result($res,0,'flat');
		// $debt = $adb->query_result($res,0,'cf_1289');
		$stret = $adb->query_result($res,0,'cf_1167');
		$house_no = $adb->query_result($res,0,'cf_1169');
	}
	$debt = getTotalDebtByContactId($contact_id);
	$theme = $data['subject']; 
	$total = round($data['hdnGrandTotal']); 
	// echo "<pre>";
	// var_dump(explode('x',$ws_entity->getId())[1]);
	// var_dump($debt);
	// var_dump($contact_id);
	// var_dump($theme);
	// var_dump($flat);
	// // var_dump($data['cf_1265'] != null);
	// // var_dump($data['cf_1265'] != null || $data['cf_1265'] != '');
	// // var_dump($data);
	// echo "<pre>";
	// exit();

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'localhost:3030/botNotify',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => "contactid=$contact_id&flat=$flat&theme=$theme&summ=$total&debt=$debt&stret=$stret&house_no=$house_no",
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
// echo $response;

	
	// $assignedTo = explode("x", $ws_entity->get("assigned_user_id"))[1];
	// $id = explode("x", $ws_entity->getId())[1];
	// $assignedBy = $_SESSION['authenticated_user_id'];
  //   if (empty($assignedBy)) $assignedBy = 1;
	// if($assignedTo == $assignedBy || recordExists($id, $assignedTo)) return;
	// $module = $ws_entity->getModuleName();
	// $url = "index.php?module=$module&view=Detail&record=$id";
	// $adb->pquery("INSERT INTO vtiger_notifications(link,module,assigned_to,added_by,recordid) VALUES(?,?,?,?,?)",
	// array($url,$module,$assignedTo,$assignedBy, $id));
}
 function getTotalDebtByContactId($id) {

	global $adb;

	/*$sql = "select sum(flatscf.cf_1289) from vtiger_flatscf flatscf 
		join vtiger_crmentity entity on entity.crmid = flatscf.flatsid
		where entity.deleted = 0 and flatscf.cf_1235 = $id";*/

	$sql = "SELECT sum(a.balance) FROM vtiger_invoice as a 
			INNER JOIN vtiger_crmentity as b 
			ON b.crmid=a.invoiceid WHERE b.deleted=0 and a.contactid=$id";
	$invsum = $adb->run_query_field($sql);

	$sql2 = "SELECT sum(a.amount) FROM sp_payments as a 
			INNER JOIN vtiger_crmentity as b 
			ON b.crmid=a.payid WHERE b.deleted=0 and a.payer=$id";
	$paysum = $adb->run_query_field($sql2);

	// $res = number_format($invsum-$paysum, 0, '.', ',');
	$res = $invsum-$paysum;

	return $res;
}

function notify($ws_entity) {
	global $adb;
	$assignedTo = explode("x", $ws_entity->get("assigned_user_id"))[1];
	$id = explode("x", $ws_entity->getId())[1];
	$assignedBy = $_SESSION['authenticated_user_id'];
    if (empty($assignedBy)) $assignedBy = 1;
	if($assignedTo == $assignedBy || recordExists($id, $assignedTo)) return;
	$module = $ws_entity->getModuleName();
	$url = "index.php?module=$module&view=Detail&record=$id";
	$adb->pquery("INSERT INTO vtiger_notifications(link,module,assigned_to,added_by,recordid) VALUES(?,?,?,?,?)",
	array($url,$module,$assignedTo,$assignedBy, $id));
}

function recordExists($recordID, $to) {
	global $adb;
	$q = $adb->pquery("SELECT COUNT(*) as res FROM vtiger_notifications WHERE recordid = ? AND assigned_to = ?", array($recordID, $to));
	$f = $adb->query_result_rowdata($q, 0);
	return $f['res'] != 0; 
}