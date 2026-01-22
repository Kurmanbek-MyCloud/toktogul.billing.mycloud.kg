<?php
// exit();
include_once 'includes/Loader.php';
include_once 'include/utils/utils.php';
include_once 'include/utils/InventoryUtils.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');
global $current_user;
global $adb;
$current_user = Users::getActiveAdminUser();

// $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
// $parts = parse_url($url);
// parse_str($parts['query'], $query);
// $id_cf_1317 = $query['cf_1317'];
// $id_cf_1333 = $query['cf_1333'];

 $res = $adb->pquery("SELECT metersid FROM vtiger_meters vm
                    INNER JOIN vtiger_crmentity vc ON vc.crmid = vm.metersid 
                    WHERE vc.deleted = 0 AND vm.metersid > 77319", array());

for ($i = 0; $i < $adb->num_rows($res); $i++) {
// for ($i = 0; $i < 2; $i++) {

$id_cf_1317 = $adb->query_result($res, $i, 'metersid');
$id_cf_1333 = $adb->query_result($res, $i, 'metersid');

// var_dump($response_value);
var_dump($id_cf_1317);
var_dump($id_cf_1333);

$meters = $adb->run_query_allrecords("SELECT RIGHT(m.meter, 5) AS meter ,
  vtm.data AS meter_data,
  vmcf.cf_1325 AS date_add_meter,
  vmcf.metersdataid AS id_meter,
  vmcf.cf_1333 AS house_id
  FROM vtiger_meters m 
  INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
  INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
  INNER JOIN vtiger_metersdatacf vmcf ON vmcf.cf_1317 = m.metersid 
  INNER JOIN vtiger_metersdata vtm ON vtm.metersdataid =vmcf.metersdataid 
  WHERE vmcf.metersdataid = (SELECT MAX(metersdataid) 
  FROM vtiger_metersdatacf cf 
  INNER JOIN vtiger_crmentity crmm ON crmm.crmid = cf.metersdataid 
  WHERE cf_1317 = $id_cf_1317 AND crmm.deleted = 0)
  AND m.metersid = $id_cf_1317 AND crm.deleted = 0");

if ($meters[0] == NULL) {
  $meter_sql = $adb->run_query_allrecords("SELECT RIGHT(m.meter, 5) AS meter,
    m.metersid
    FROM vtiger_meters m 
    INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
    INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
    AND m.metersid = $id_cf_1317 AND crm.deleted = 0");
  $meter_cf_1317 = json_encode('2-1-' . (int) $meter_sql[0]['meter']);
} else {
  $meter_cf_1317 = json_encode('2-1-' . (int) $meters[0]['meter']);
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://cntdev.ru/api?',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '{
      "list" : [' . $meter_cf_1317 . ']
  }',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Anarbaev1955@mail.ru:b0580d62-3514-4f13-bcca-0a99cedede98',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
$response_value = json_decode($response);
$response_value_date = $response_value->{'data'}->{'meters'}[0]->{'updated'};
$response_value = $response_value->{'data'}->{'meters'}[0]->{'value'};


curl_close($curl);
// echo $response;
$red = (string) $_SERVER['HTTP_REFERER'];

$updated_date = date('d.m.Y', $response_value_date);
$house_id = $meters[0]['house_id'];
$meter_id = '00' . $meters[0]['meter'];



if ($response_value == NULL) {
  $sql = "UPDATE vtiger_meterscf SET cf_1491=?, cf_1493=? WHERE metersid=?";
  $adb->pquery($sql, array('Элехант', 1, $meter_sql[0]['metersid']));
  header("Location: $red ");
} elseif ($meters[0] == NULL) {
  $MetersData = Vtiger_Record_Model::getCleanInstance("MetersData");
  $MetersData->set('data', (string) $response_value);
  $MetersData->set('cf_1317', (string) $id_cf_1317);
  $MetersData->set('cf_1325', (string) $updated_date);
  $MetersData->set('cf_1333', (string) $id_cf_1333);
  $MetersData->set('cf_1327', 'сайт cntdev.ru');
  $MetersData->set('assigned_user_id', 1);
  $MetersData->set('mode', 'create');
  $MetersData->save();
  header("Location: $red ");
} elseif ((float) $meters[0]['meter_data'] == (float) $response_value) {
  header("Location: $red ");
} else {
  $MetersData = Vtiger_Record_Model::getCleanInstance("MetersData");
  $MetersData->set('data', (string) $response_value);
  $MetersData->set('cf_1317', (string) $id_cf_1317);
  $MetersData->set('cf_1325', (string) $updated_date);
  $MetersData->set('cf_1333', (string) $house_id);
  $MetersData->set('cf_1327', 'сайт cntdev.ru');
  $MetersData->set('assigned_user_id', 1);
  $MetersData->set('mode', 'create');
  $MetersData->save();
  header("Location: $red ");
}


}





