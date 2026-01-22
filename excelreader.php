<?php

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
require_once 'phpexcel/Classes/PHPExcel.php';
require_once 'Logger.php';
$logger = new CustomLogger('excelreader.log');

$path = "baza20062025.xlsx";
$reader = PHPExcel_IOFactory::createReaderForFile($path);
$excel_Obj = $reader->load($path);
$worksheet = $excel_Obj->getSheet('0');
$lastRow = $worksheet->getHighestRow();


//exit();

//711
for ($i = 2; $i <= 358; $i++) {
  // for ($i = 5; $i <= 4; $i++) {
  // for Houses 
  // $account_number = trim($worksheet->getCell('A' . $i)->getValue()); 
  $fio = trim($worksheet->getCell('A' . $i)->getValue());
  $street = trim($worksheet->getCell('B' . $i)->getValue());
  $house_num = trim($worksheet->getCell('C' . $i)->getValue());
  $litera = trim($worksheet->getCell('D' . $i)->getValue());
//  $well = trim($worksheet->getCell('E' . $i)->getValue());
  // $vodoprovod = trim($worksheet->getCell('D' . $i)->getValue());
  // $num_people = trim($worksheet->getCell('E' . $i)->getValue());
  // $av_consume = trim($worksheet->getCell('F' . $i)->getValue());
  // $debt = trim($worksheet->getCell('J' . $i)->getValue());
  // $debt_2 = trim($worksheet->getCell('G' . $i)->getValue());     
  $assigned_user_id = 1;
//  $lastCharacter = substr($fio, -1);
//  $fioWithoutLastCharacter = substr($fio, 0, -1);
  // echo "<pre>";
  // var_dump($fio);
  // var_dump($fioWithoutLastCharacter);
  // var_dump($lastCharacter);
  // echo "</pre>";
  // exit();

//  if ($fio == '-') {
//    $logger->log("$i ----------АБОНЕНТ ПУСТОЙ $fio");
//    continue;
//  }
//  if ($lastCharacter == '.') {
//    $fioWithPoint = $fio;
//    $fio = $fioWithoutLastCharacter;
//  } else {
//    $fioWithPoint = $fio . '.';
//  }

  $search_contact = $adb->run_query_allrecords("SELECT * FROM vtiger_contactdetails vc 
  INNER JOIN vtiger_crmentity vc2 ON vc.contactid = vc2.crmid 
  WHERE vc2.deleted = 0
  AND (vc.lastname = '$fio')");

  $contact_id = $search_contact[0]['contactid'];
  // echo "<pre>";
  // var_dump($fio);
  // var_dump($fioWithPoint);
  // var_dump($contact_id);
  // echo "</pre>";
  // exit();

  if ($contact_id == null) {
    $contact_record = Vtiger_Record_Model::getCleanInstance("Contacts");
    $contact_record->set('lastname', $fio);
    $contact_record->set('assigned_user_id', $assigned_user_id);
    $contact_record->set('cf_1468', 'Физ. лицо');
    $contact_record->set('mode', 'create');
    $contact_record->save();
    $contact_id = $contact_record->getId();
  }

  $sql_2 = "SELECT vf.flatsid from vtiger_flatscf vf 
  inner join vtiger_contactdetails vc2 on vf.cf_1235 = vc2.contactid  
  inner join vtiger_flats vf2 on vf.flatsid = vf2.flatsid  
  inner join vtiger_crmentity vc on vf.flatsid = vc.crmid 
  Inner join vtiger_crmentity vc3 on vc2.contactid = vc3.crmid
  WHERE vc.deleted = 0
  AND vc3.deleted = 0
  and (vc2.lastname LIKE '%$fio%')
  and vf2.flat = '$house_num'";

  $check_fio_house = $adb->run_query_allrecords($sql_2);

  $flat_id = $check_fio_house[0]['flatsid'];
  // echo "<pre>";
  // var_dump($fio);
  // var_dump($fioWithPoint);
  // echo "</pre>";
  // exit();


  if ($flat_id == null) {
    $house_record = Vtiger_Record_Model::getCleanInstance("Flats");
    $house_record->set('flat', $house_num.$litera);
    $house_record->set('cf_1448', $street);
    $house_record->set('cf_1235', $contact_id);
    $house_record->set('cf_1444', "Дом");
    $house_record->set('cf_1452', "nf");
//    $house_record->set('cf_type_vod', "Дворовой");
    $house_record->set('assigned_user_id', $assigned_user_id);
    $house_record->set('mode', 'create');
    $house_record->save();
    $flat_id = $house_record->getId();

    $logger->log("$i Создан объект $flat_id. Улица $street д.$house_num");

//    $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '30083', 'Services')", array());
  }

//  $counter_record = Vtiger_Record_Model::getCleanInstance("Meters");
//  $counter_record->set('meter', $counter);
//  $counter_record->set('cf_1430', 'Холодная вода');
//  $counter_record->set('cf_1462', $well);
//  $counter_record->set('cf_1319', $flat_id);
//  $counter_record->save();

//  $logger->log("$i счетчик $counter в дом $flat_id с абонентом $fio был успешно добавлен ");


}

exit();






// var_dump($lastRow);
// $prev_street = "Деповская";
// $street_id = 22144;

// $streets_arr = [];

// echo "<pre>";
// var_dump($worksheet->getCell("C" . 6)->getValue());
// echo "</pre>";

// $streets_array = [];


// $streets_sql_result = $adb->run_query_allrecords("SELECT housesid, house FROM vtiger_houses AS streets
// INNER JOIN vtiger_crmentity AS crm ON crm.crmid=streets.housesid
// WHERE crm.deleted = 0");
// foreach ($streets_sql_result as $value) {
//   $streets_array += [trim(mb_strtolower($value['house'], 'UTF-8')) => trim($value['housesid'])];
// }


// foreach ($streets_array as $key => $value) {
//   echo "<pre>";

//   echo $key . ':' . $value . ','; 

//   echo "</pre>";
// }
// echo "<pre>";
// var_dump(trim(mb_strtolower($a,'UTF-8')));
// var_dump(in_array($a, array_keys($streets_array)));
// var_dump(array_key_exists(trim(mb_strtolower($a, 'UTF-8')), $streets_array));
// var_dump($streets_array[$a]);
// print_r($streets_array);
// // // var_dump($streets_id_array);
// echo "</pre>";
// echo "<pre>";
// print_r($streets_array);
// echo "</pre>";
// exit();

// // for ($i = 5; $i <= 2188; $i++) {
// for ($i = 133; $i <= 402; $i++) {
//   // for Houses
//   $fio = trim($worksheet->getCell('A' . $i)->getValue());
//   $street = trim($worksheet->getCell('B' . $i)->getValue());
//   $house_num = trim($worksheet->getCell('C' . $i)->getValue());
//   $people_count = trim($worksheet->getCell('D' . $i)->getValue());
//   $controller = trim($worksheet->getCell('E' . $i)->getValue());
//   $water = trim($worksheet->getCell('F' . $i)->getValue());
//   $garbage = trim($worksheet->getCell('G' . $i)->getValue());
//   $waterDebt = trim($worksheet->getCell('H' . $i)->getValue());
//   $garbageDebt = trim($worksheet->getCell('I' . $i)->getValue());
//   $assigned_user_id = 1;


//   // // For flats
//   // $fio = trim(mb_strtolower($worksheet->getCell('A' . $i)->getValue(), 'UTF-8'));
//   // $street = trim(mb_strtolower($worksheet->getCell('B' . $i)->getValue(), 'UTF-8'));
//   // $house_num = trim(mb_strtolower($worksheet->getCell('C' . $i)->getValue(), 'UTF-8'));
//   // $flat_num = trim(mb_strtolower($worksheet->getCell('D' . $i)->getValue(), 'UTF-8'));
//   // $people_count = trim(mb_strtolower($worksheet->getCell('E' . $i)->getValue(), 'UTF-8'));
//   // $controller = trim($worksheet->getCell('F' . $i)->getValue());
//   // $water = trim($worksheet->getCell('G' . $i)->getValue());
//   // $garbage = trim($worksheet->getCell('H' . $i)->getValue());
//   // $waterDebt = trim($worksheet->getCell('I' . $i)->getValue());
//   // $garbageDebt = trim($worksheet->getCell('J' . $i)->getValue());
//   // $assigned_user_id = 1;


//   if (strpos($controller, "Бейшенова") !== false) {
//     $assigned_user_id = 16;
//   } else if (strpos($controller, "Абыканова") !== false) {
//     $assigned_user_id = 15;
//   } else if (strpos($controller, "Катеринич") !== false) {
//     $assigned_user_id = 17;
//   } else if (strpos($controller, "Асланова") !== false) {
//     $assigned_user_id = 18;
//   } else if (strpos($controller, "Сабыралиева") !== false) {
//     $assigned_user_id = 19;
//   }  

//   if(!array_key_exists(mb_strtolower($street, 'UTF-8'), $streets_array)){
//     $street_record = Vtiger_Record_Model::getCleanInstance("Houses");
//     $street_record->set('house', $street);
//     $street_record->set('assigned_user_id', $assigned_user_id);
//     $street_record->set('mode', 'create');
//     $street_record->save();
//     $street_record_id = $street_record->getId();
//     $prev_street = $street;
//     $street_id = $street_record_id;
//     $streets_array += [trim(mb_strtolower($street, 'UTF-8')) => trim($street_id)];
//   }else{
//     $street_id = $streets_array[mb_strtolower($street, 'UTF-8')];
//   }

//   $check_house = $adb->run_query_allrecords("SELECT f.flatsid, f.flat, streets.housesid,streets.house  FROM vtiger_houses AS streets
//                   INNER JOIN vtiger_crmentity AS crm ON crm.crmid = streets.housesid
//                   INNER JOIN vtiger_flatscf AS fcf ON streets.housesid = fcf.cf_1203
//                   INNER JOIN vtiger_flats AS f ON f.flatsid = fcf.flatsid
//                   INNER JOIN vtiger_crmentity AS fcrm ON fcrm.crmid = fcf.flatsid
//                   WHERE crm.deleted = 0
//                   AND fcrm.deleted = 0
//                   AND streets.housesid = $street_id
//                   AND f.flat = '$house_num'");  

//   if(count($check_house) > 0){
//     $logger->log("$i ERROR! found dublicate of object(flat, house) flatid: ".$check_house[0]['flatsid']." flat: '".$check_house[0]['flat']."' streetid: ".$check_house[0]['housesid']." street: '".$check_house[0]['house']."'"  );
//     continue;
//   }

//   // $check_house_flat = $adb->run_query_allrecords("SELECT  f.flatsid, f.flat, streets.housesid,streets.house, fcf.cf_1446 FROM vtiger_houses AS streets
//   //                   INNER JOIN vtiger_crmentity AS crm ON crm.crmid = streets.housesid
//   //                   INNER JOIN vtiger_flatscf AS fcf ON streets.housesid = fcf.cf_1203
//   //                   INNER JOIN vtiger_flats AS f ON f.flatsid = fcf.flatsid
//   //                   INNER JOIN vtiger_crmentity AS fcrm ON fcrm.crmid = fcf.flatsid
//   //                   WHERE crm.deleted = 0
//   //                   AND fcrm.deleted = 0
//   //                   AND streets.housesid = $street_id
//   //                   AND f.flat = '$house_num'
//   //                   AND fcf.cf_1446 = '$flat_num'");

//   // if(count($check_house_flat) > 0){
//   //   $logger->log("$i ERROR! found dublicate of object(flat, house) flatid: " . $check_house_flat[0]['flatsid'] . " house: '" . $check_house_flat[0]['flat'] .  "' flat '" . $check_house_flat[0]['cf_1446'] . "' streetid: ".$check_house_flat[0]['housesid']." street: '".$check_house_flat[0]['house']."'"  );
//   //   continue;
//   // }

//   $contact_record = Vtiger_Record_Model::getCleanInstance("Contacts");
//   $contact_record->set('lastname', $fio);
//   $contact_record->set('assigned_user_id', $assigned_user_id);
//   $contact_record->set('mode', 'create');
//   $contact_record->save();
//   $contact_id = $contact_record->getId();

//   $house_record = Vtiger_Record_Model::getCleanInstance("Flats");
//   $house_record->set('flat', $house_num);
//   $house_record->set('cf_1203', $street_id);
//   $house_record->set('cf_1235', $contact_id);
//   $house_record->set('cf_1444', "Дом");
//   // $house_record->set('cf_1444', "Квартира");
//   // $house_record->set('cf_1446', $flat_num);
//   $house_record->set('cf_1261',  $people_count);
//   if ($water == "Да") {
//     $house_record->set('cf_1440',  1);
//   }
//   if ($garbage == "Да") {
//     $house_record->set('cf_1442',  1);
//   }
//   $house_record->set('assigned_user_id', $assigned_user_id);
//   $house_record->set('mode', 'create');
//   $house_record->save();
//   $house_id = $house_record->getId();

//   if ($waterDebt + $garbageDebt > 0) {
//     $invoice = Vtiger_Record_Model::getCleanInstance("Invoice");
//     $invoice->set('contact_id', $contact_id);
//     $invoice->set('cf_1265', $house_id);
//     $invoice->set('subject', $theme);
//     $invoice->set('duedate', $due_date->format('Y-11-01'));
//     $invoice->set('invoicestatus', 'AutoCreated');
//     $invoice->set('assigned_user_id', $assigned_user_id);
//     $invoice->set('mode', 'create');
//     $invoice->save();
//     $invoice_id = $invoice->getId();

//     if ($invoice_id != null) {
//       if ((int)$waterDebt > 0) {
//         $price = 25;
//         $service_id = 8052;
//         $quantity = $waterDebt / $price;
//         add_service_to_invoice($invoice_id, $service_id, 0, 0, $quantity, $price, '');
//       }
//       if ((int)$garbageDebt > 0) {
//         $price = 100;
//         $service_id = 8051;
//         $quantity = $garbageDebt / $price;
//         add_service_to_invoice($invoice_id, $service_id, 0, 0, $quantity, $price, '');
//       }
//       // Обновление задолженности по Объекту
//       $sql = "SELECT ifnull(sum(a.total),0) FROM vtiger_invoice as a 
//               INNER JOIN vtiger_crmentity as b ON b.crmid=a.invoiceid 
//               INNER JOIN vtiger_invoicecf as c ON b.crmid=c.invoiceid 
//               WHERE b.deleted=0 and c.cf_1265=$house_id";
//       $invsum = $adb->run_query_field($sql);

//       $sql2 = "SELECT ifnull(sum(a.amount),0) FROM sp_payments as a 
//               INNER JOIN vtiger_crmentity as b ON b.crmid=a.payid 
//               INNER JOIN sp_paymentscf as c ON b.crmid=c.payid 
//               WHERE b.deleted=0 and c.cf_1416=$house_id";
//       $paysum = $adb->run_query_field($sql2);
//       $res = number_format($invsum-$paysum, 0, '.', '');

//       $adb->run_query_field("UPDATE vtiger_flatscf set cf_1289='$res' WHERE flatsid='$house_id'");


//       // Обновление задолженности по Улице

//       $house_invoices_sql = "SELECT ifnull(sum(i.total),0) FROM vtiger_invoice as i 
//               INNER JOIN vtiger_crmentity as crm ON crm.crmid=i.invoiceid 
//               INNER JOIN vtiger_invoicecf as icf ON crm.crmid=icf.invoiceid 
//               INNER JOIN vtiger_flatscf fcf ON fcf.flatsid = icf.cf_1265
//               WHERE crm.deleted=0 and fcf.cf_1203 = $street_id";
//       $house_invoices_summ = $adb->run_query_field($house_invoices_sql);

//       $house_payments_sql = "SELECT ifnull(sum(p.amount),0) FROM sp_payments as p 
//               INNER JOIN vtiger_crmentity as crm ON crm.crmid=p.payid 
//               INNER JOIN sp_paymentscf as pcf ON crm.crmid=pcf.payid 
//               INNER JOIN vtiger_flatscf fcf ON fcf.flatsid =pcf.cf_1416
//               WHERE crm.deleted=0 and fcf.cf_1203 = $street_id";
//       $house_payments_summ = $adb->run_query_field($house_payments_sql);

//       $houses_res = number_format($house_invoices_summ - $house_payments_summ, 0, '.', '');
//       $adb->run_query_field("UPDATE vtiger_housescf hcf SET hcf.cf_1303 = $houses_res WHERE hcf.housesid = $street_id");   
//     }
//   }

//   $logger->log($i . '  street_id: ' . $street_id . ' -- contact_id: ' . $contact_id . ' -- house_id: ' . $house_id . ' -- invoice_id: ' . $invoice_id);
// }


// echo "<pre>";
// print_r($streets_array);
// echo "</pre>";
// // exit();

// function add_service_to_invoice($invoiceid, $serviceid, $prev_md, $current_md, $quantity, $listprice, $accrual_base)
// {
//   $margin = $listprice * $quantity;
//   global $adb;
//   $sql = "INSERT INTO vtiger_inventoryproductrel(id, productid, quantity, listprice, margin,accrual_base, previous_reading, current_reading) VALUES(?,?,?,?,?,?,?,?)";
//   $params = array($invoiceid, $serviceid, $quantity, $listprice, $margin, $accrual_base, $prev_md, $current_md);
//   $adb->pquery($sql, $params);
//   $total = get_total_sum_by_service($invoiceid);

//   if ($total) {
//     update_invoice_total_field($total, $invoiceid);
//   }
// }

// function update_invoice_total_field($total, $invoiceid)
// {
//   global $adb;
//   $sql = "UPDATE vtiger_invoice SET total=?, balance=?, subtotal=?, pre_tax_total=?, taxtype=? WHERE invoiceid=?";
//   $adb->pquery($sql, array($total, $total, $total, $total, 'group_tax_inc', $invoiceid));
// }

// function get_total_sum_by_service($invoiceid)
// {
//   global $adb;
//   $sql = "SELECT SUM(margin) AS total FROM vtiger_inventoryproductrel WHERE id=?";
//   $result = $adb->pquery($sql, array($invoiceid));
//   $total = $adb->query_result($result, 0, 'total');
//   return $total;
// }
