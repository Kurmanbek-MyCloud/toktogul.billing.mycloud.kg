<?php
// ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING
include_once 'includes/Loader.php';
include_once 'include/utils/utils.php';
include_once 'include/utils/InventoryUtils.php';

require_once 'Logger.php';

vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

global $adb;
$logger = new CustomLogger('whatsAppViaGupshupAll.log');
// exit();


// echo '$response2';
// echo $response;
// echo '$response2';
// exit();
##############################################################################################
################################# Рассыллка для ВСЕХ жителей #################################
##############################################################################################
$res = $adb->pquery("SELECT  CONCAT('996',right(CD.mobile,9)) AS mobile, CD.lastname,
                        (
                          (SELECT IFNULL((SELECT round(sum(total)) AS summ FROM vtiger_invoice AS I
                                        INNER JOIN vtiger_crmentity AS CE ON I.invoiceid = CE.crmid
                                        WHERE deleted = 0
                                        AND invoicestatus not IN ('Cancel')
                                        AND contactid=CF.contactid),0))
                          -
                          (SELECT IFNULL( (select round(SUM(amount)) as summ FROM sp_payments as SP
                                            INNER JOIN  vtiger_crmentity AS SCE ON SP.payid = SCE.crmid 
                                            WHERE SCE.deleted = 0
                                            AND pay_type = 'Receipt'
                                            AND payer = CF.contactid), 0))
                        ) AS debt,
                        (SELECT COUNT(FCF.flatsid) FROM vtiger_flatscf FCF 
                        INNER JOIN vtiger_crmentity FCE ON FCE.crmid = FCF.flatsid
                        WHERE FCF.cf_1235 = CD.contactid AND FCE.deleted = 0
                        ) AS flats_count


                    FROM vtiger_contactdetails AS CD
                    INNER JOIN vtiger_contactscf AS CF ON CD.contactid = CF.contactid
                    INNER JOIN vtiger_crmentity AS CRM ON CRM.crmid = CF.contactid
                    WHERE CRM.deleted = 0
");
// echo '<pre>';
// var_dump($adb->num_rows($res));
// echo '</pre>';
$logger->log("Количество абонентов ".$adb->num_rows($res));
// exit();

// if ($adb->num_rows($res) == 0 ) {
//   $logger->log("Должников не обнаружено )");
//   exit();
// }

for ($i=0; $i < $adb->num_rows($res); $i++) { 
  // for ($i=0; $i < 1; $i++) { 
  $flats_count = $adb->query_result($res,$i,'flats_count');
  $mobile = $adb->query_result($res,$i,'mobile');
  // echo "mobile: $mobile<br>";
  // $mobile = "996553408448";
  // echo "mobile: $mobile";
  // exit();
  // $mobile = "996755505504";
  $lastname = $adb->query_result($res,$i,'lastname');
  $debt = $adb->query_result($res,$i,'debt');
  // 779294635
  //#OPT-IN
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.gupshup.io/sm/api/v1/app/opt/in/OimoWAtest1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    // CURLOPT_POSTFIELDS => "user=".$mobile,
    CURLOPT_POSTFIELDS => "user=".$mobile,
    CURLOPT_HTTPHEADER => array(
      'apikey: 9lf17q29xkqsu2o7nlbavclan4s2itgq',
      'Content-Type: application/x-www-form-urlencoded'
    ),
  ));

  $response = curl_exec($curl);
  $logger->log("OPT-IN номер: $mobile ");
  curl_close($curl);

  // if ($flats_count == '1'){
    $curl = curl_init();
    // $data = 'destination=' . '996508408448' .
    //       '&source=996999901767'.http_build_query([
        
    //     "template" => '{"id": "3bffecdc-1d86-4ac8-a896-e77d212a1fde","params": ["asdf"]}'
    // ]);
    curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://api.gupshup.io/sm/api/v1/template/msg',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_BINARYTRANSFER, TRUE,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          // CURLOPT_POSTFIELDS => $data,
          CURLOPT_POSTFIELDS => 'destination=' .$mobile.
          '&source=996999901767'.
          '&template={"id": "c9d42319-32fc-4560-b569-5e6aa8e67410","params": []}',
          CURLOPT_HTTPHEADER => array(
            'apikey: 9lf17q29xkqsu2o7nlbavclan4s2itgq',
            'Content-Type: application/x-www-form-urlencoded'
          ),
      ));


      $response = curl_exec($curl);
      $logger->log("Sending.. #$i номер: $mobile ФИО: $lastname response: $response");
      
      curl_close($curl);
      // }
}
//     echo "<pre>";
//     var_dump(curl_exec($curl));
// var_dump($data);
// var_dump('response');
// var_dump($response);
// var_dump($adb->num_rows($res));
// var_dump($adb->query_result($res,0,'flats_count'));
// echo "</pre>";
// exit();





// $contacts_array = array();


// function get_debt($adb, $id){
//   $sql = "SELECT sum(a.balance) FROM vtiger_invoice as a 
//   INNER JOIN vtiger_crmentity as b 
//   ON b.crmid=a.invoiceid WHERE b.deleted=0 and a.contactid=$id";
//   $invsum = $adb->run_query_field($sql);

//   $sql2 = "SELECT sum(a.amount) FROM sp_payments as a 
//   INNER JOIN vtiger_crmentity as b 
//   ON b.crmid=a.payid WHERE b.deleted=0 and a.payer=$id";
//   $paysum = $adb->run_query_field($sql2);
//   $res = number_format($invsum-$paysum, 0, '.', '');

//   return $res;
// }

// $res =  $adb->pquery(
// "SELECT contactid as id, 
// lastname as name, 
// CONCAT('996',RIGHT(mobile,9)) as phoneNumber 
// FROM vtiger_contactdetails as a
// INNER JOIN vtiger_crmentity as b
// ON b.crmid=a.contactid WHERE b.deleted=0", array()); 

// $i = 0;
// foreach($res as $value){
//   $contacts_array[$i]['id'] = $value['id'];
//   $contacts_array[$i]['name'] = $value['name'];
//   $contacts_array[$i]['phone'] = trim($value['phoneNumber'], "0");
//   $contacts_array[$i]['debt'] = get_debt($adb, $contacts_array[$i]['id']);
//   $i++;
// }


// for($i = 0; $i < sizeof($contacts_array); $i++){
//   if($contacts_array[$i]['debt'] != 0 && $contacts_array[$i]['debt'] > 0){

//     $curl = curl_init();

//     curl_setopt_array($curl, array(
//       CURLOPT_URL => 'https://api.gupshup.io/sm/api/v1/app/opt/in/MyCloud2Oimo',
//       CURLOPT_RETURNTRANSFER => true,
//       CURLOPT_ENCODING => '',
//       CURLOPT_MAXREDIRS => 10,
//       CURLOPT_TIMEOUT => 0,
//       CURLOPT_FOLLOWLOCATION => true,
//       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//       CURLOPT_CUSTOMREQUEST => 'POST',
//       CURLOPT_POSTFIELDS => 'user=' . $contacts_array[$i]['phone'],
//       CURLOPT_HTTPHEADER => array(
//         'apikey: r4lfnfcoqtmi6eug8vo3i7ft58hmc37t',
//         'Content-Type: application/x-www-form-urlencoded'
//       ),
//     ));

//     $response = curl_exec($curl);

//     curl_close($curl);

//     try{

//       $curl = curl_init();
//       echo $template;
//       curl_setopt_array($curl, array(
//         CURLOPT_URL => 'http://api.gupshup.io/sm/api/v1/template/msg',
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_ENCODING => '',
//         CURLOPT_MAXREDIRS => 10,
//         CURLOPT_TIMEOUT => 0,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//         CURLOPT_CUSTOMREQUEST => 'POST',
//         CURLOPT_POSTFIELDS => 'destination=' . $contacts_array[$i]['phone'] . '&source=996557035356&template={"id": "c4f51dcf-7edd-4ed6-ad3b-c09fecc33bc3","params": ["' . $contacts_array[$i]['name'] . '","' . $contacts_array[$i]['debt'] . '"]}',
//         CURLOPT_HTTPHEADER => array(
//           'apikey: r4lfnfcoqtmi6eug8vo3i7ft58hmc37t',
//           'Content-Type: application/x-www-form-urlencoded'
//         ),
//       ));

//       $response = curl_exec($curl);
//       if(curl_errno($curl)){

//         throw new \Exception(curl_error($curl));
        
//       }
//       $text = date('Y-m-d H:i:s').' : ' . "Уважаемый(-ая) " . $contacts_array['name'] . "! У вас имеется задолжность по оплате за услугу, сумма составляет " . $contacts_array['debt'] . " сом(-ов). Просим оплатить в течение месяца. НЕ ОТВЕЧАЙТЕ НА ДАННОЕ СООБЩЕНИЕ. По всем вопросам звоните: 0555 555 555." ."\n";
//       $open = fopen('gupshup_logs/дата_отправки.txt','a');
//       fwrite($open, $text);
//       fclose($open);
//     }catch(Exception $e){
//       $text = date('Y-m-d H:i:s').': Could not send request : '. $e->getMessage() ."\n";
//       $open = fopen('gupshup_logs/errors.txt','a');
//       fwrite($open, $text);
//       fclose($open);
//     }
//     curl_close($curl);
//     }
//   }


