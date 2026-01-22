<?php
require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';
require_once 'Logger.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

$logger = new CustomLogger('createInvoice.log');

$assigned_user_id = 30;
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile($assigned_user_id);
global $adb;


$res = $adb->run_query_allrecords("SELECT * FROM vtiger_flats f 
        INNER JOIN vtiger_flatscf fcf ON fcf.flatsid = f.flatsid
        INNER JOIN vtiger_crmentity crm ON crm.crmid = f.flatsid
        LEFT JOIN vtiger_contactdetails c ON c.contactid = fcf.cf_1235
        LEFT JOIN vtiger_contactscf cf ON cf.contactid = c.contactid
        WHERE crm.deleted = 0 
      and f.flatsid = 29228");
// echo "<pre>";
// var_dump(count($res));
// var_dump($res[0]['flatsid']);
// // var_dump($res[1]['contactid']);
// echo "</pre>";
// exit();
$period = new DateTime();
$period->modify("-1 month");
$due_date = new DateTime();
// $due_date->modify("+5 days");


$translate_month = array(
  'Jan' => 'Январь',
  'Feb' => 'Февраль',
  'Mar' => 'Март',
  'Apr' => 'Апрель',
  'May' => 'Май',
  'Jun' => 'Июнь',
  'Jul' => 'Июль',
  'Aug' => 'Август',
  'Sep' => 'Сентябрь',
  'Oct' => 'Октябрь',
  'Nov' => 'Ноябрь',
  'Dec' => 'Декабрь'
);

$theme = "За " . $translate_month[$period->format("M")] . " " . $period->format("Y") . " года";

// for ($i=0; $i < count($res); $i++) { 
for ($i = 0; $i < 1; $i++)
{
  $contactid = $res[$i]['contactid'];
  $flatid = $res[$i]['flatsid'];
  $kolvo = $res[$i]['cf_1261'];
  // $contactid =  $res[$i]['contactid'];
  // var_dump($contact_id);
  // var_dump($kolvo);
  // exit();

  // $flatid =  $adb->query_result($res, $i, 'flatsid');
  // $streetid = $adb->query_result($res, $i, 'housesid');
  // $kolvo = $adb->query_result($res, $i, 'cf_1261');

  $meters_sql = "SELECT m.metersid, md.data 
                FROM vtiger_meters m 
                INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid 
                INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid 
                LEFT join vtiger_metersdatacf mdcf ON mdcf.cf_1317 = m.metersid
                left JOIN vtiger_metersdata md ON md.metersdataid = mdcf.metersdataid
                left JOIN vtiger_crmentity mdcrm ON mdcrm.crmid = md.metersdataid
                WHERE crm.deleted = 0 
                AND ifnull(mdcrm.deleted,0) = 0
                AND mcf.cf_1319 = $flatid -- flatid
                ORDER BY mdcf.metersdataid desc";
  // $water_meter_data = $adb->pquery($meters_sql,array('Холодная вода'));
  // $meterid = $adb->query_result($water_meter_data, 0, 'metersid');
  // echo "<pre>";
  // // var_dump($contactid);
  // var_dump($meterid);
  // var_dump($water_meter_data);
  // var_dump($meters_sql);
  // echo "</pre>";
  // exit();

  $services_data = $adb->run_query_allrecords("SELECT DISTINCT * FROM vtiger_crmentityrel rel
              INNER JOIN vtiger_service s ON s.serviceid = rel.relcrmid 
              INNER JOIN vtiger_servicecf scf ON scf.serviceid = s.serviceid
              INNER JOIN vtiger_crmentity crm ON crm.crmid = s.serviceid
              WHERE rel.relmodule = 'Services'
              AND crm.deleted = 0
              AND rel.crmid = $flatid");

  // echo "<pre>";
  // var_dump ($services_data);
  // var_dump ($test);
  // echo "</pre>";
  // exit();


  if (count($services_data) > 0)
  {


    $invoice = Vtiger_Record_Model::getCleanInstance("Invoice");
    $invoice->set('contact_id', $contactid);
    $invoice->set('cf_1265', $flatid);
    $invoice->set('subject', $theme);
    $invoice->set('duedate', $due_date->format('Y-m-05'));
    $invoice->set('invoicestatus', 'AutoCreated');
    $invoice->set('assigned_user_id', 1);
    $invoice->set('currency_id', 1);
    $invoice->set('mode', 'create');
    //     echo "<pre>";
    // var_dump($invoice);
    // // var_dump($res[0]['flatsid']);
    // // var_dump($res[1]['contactid']);
    // echo "</pre>";
    // exit();
    $invoice->save();
    $invoice_id = $invoice->getId();

    if ($invoice_id != null)
    {

      foreach ($services_data as $service_data)
      {

        $service_id = $service_data['serviceid'];
        $service_name = $service_data['servicename'];
        $service_name_ru = $service_data['cf_1219'];
        $price = $service_data['unit_price'];
        $accrual_base = $service_data['cf_1297'];

        if (strpos(mb_strtolower($service_name, 'UTF-8'), "канализация") !== false || strpos(mb_strtolower($service_name_ru, 'UTF-8'), "канализация") !== false || strpos(mb_strtolower($service_name, 'UTF-8'), "питьевая вода") !== false || strpos(mb_strtolower($service_name_ru, 'UTF-8'), "питьевая вода") !== false)
        {

          if ($accrual_base == "Счетчик")
          {

            $water_meter_data = $adb->pquery($meters_sql, array('%вода%'));

            $current_md = $adb->query_result($water_meter_data, 0, 'data');
            $prev_md = $adb->query_result($water_meter_data, 1, 'data');
            $current_md = $current_md != null ? $current_md : 0;
            $prev_md = $prev_md != null ? $prev_md : 0;
            $quantity = $current_md - $prev_md;

            if (strpos(mb_strtolower($service_name, 'UTF-8'), "канализация") !== false || strpos(mb_strtolower($service_name_ru, 'UTF-8'), "канализация") !== false)
            {
              $quantity_07 = $quantity * 0.7;
              add_service_to_invoice($invoice_id, $service_id, '', '', $quantity_07, $price, $accrual_base);
            }
            else
            {
              add_service_to_invoice($invoice_id, $service_id, $prev_md, $current_md, $quantity, $price, $accrual_base);
            }

          }
          elseif ($accrual_base == "Количество проживающих")
          {
            $quantity = $kolvo;
            add_service_to_invoice($invoice_id, $service_id, '', '', $quantity, $price, $accrual_base);
          }
          else
          {
            $quantity = '';
            $logger->log("$i ERROR! Invalid accural-base! ID Услуги: $service_id service_name: '$service_name' ID Дома: $flatid");
          }
        }
        elseif (strpos(mb_strtolower($service_name, 'UTF-8'), "мусор") !== false || strpos(mb_strtolower($service_name_ru, 'UTF-8'), "мусор") !== false)
        {
          add_service_to_invoice($invoice_id, $service_id, '', '', $kolvo, $price, $accrual_base);
        }
        elseif (strpos(mb_strtolower($service_name, 'UTF-8'), "электроэнергия") !== false || strpos(mb_strtolower($service_name_ru, 'UTF-8'), "электроэнергия") !== false)
        {
          if ($accrual_base == "Счетчик")
          {

            $electro_meter_data = $adb->pquery($meters_sql, array('%электроэнергия%'));
            $current_md = $adb->query_result($electro_meter_data, 0, 'data');
            $prev_md = $adb->query_result($electro_meter_data, 1, 'data');
            $current_md = $current_md != null ? $current_md : 0;
            $prev_md = $prev_md != null ? $prev_md : 0;
            $quantity = $current_md - $prev_md;

            add_service_to_invoice($invoice_id, $service_id, $prev_md, $current_md, $quantity, $price, $accrual_base);

          }
          elseif ($accrual_base == "Количество проживающих")
          {
            $quantity = $kolvo;
            add_service_to_invoice($invoice_id, $service_id, '', '', $quantity, $price, $accrual_base);
          }
          else
          {
            $quantity = '';
            $logger->log("$i ERROR! Invalid accural-base! ID Услуги: $service_id service_name: '$service_name' ID Дома: $flatid");
          }
        }
        else
        {
          $logger->log("$i ERROR! Нет обработки по данной услуге! ID Услуги: $service_id service_name: '$service_name' ID Дома: $flatid");
        }
      }
      update_flat_debt_by_flatid($flatid);
    }
    $invoice_id != null ? $logger->log("#$i Счет успешно создан ID: $invoice_id ID Дома: $flatid Предыдущее показание: $prev_md Текущее показание: $current_md ") : $logger->log("! Ошибка при создании Счета ! ID Дома: $flatid ");
  }
  else
  {
    $logger->log("$i ERROR! Service not connected! ID Дома: $flatid");
  }
}

function add_service_to_invoice($invoiceid, $serviceid, $prev_md, $current_md, $quantity, $listprice, $accrual_base)
{
  $margin = $listprice * $quantity;
  global $adb;
  $sql = "INSERT INTO vtiger_inventoryproductrel(id, productid, quantity, listprice, margin,accrual_base, previous_reading, current_reading) VALUES(?,?,?,?,?,?,?,?)";
  $params = array($invoiceid, $serviceid, $quantity, $listprice, $margin, $accrual_base, $prev_md, $current_md);
  $adb->pquery($sql, $params);
  // echo $listprice."- listprice<br>";
  // echo $quantity."- quantity<br>";
  // echo $margin."- total<br>";
  $total = get_total_sum_by_service($invoiceid);
  // echo $total."-grand total<br>";
  if ($total)
  {
    update_invoice_total_field($total, $invoiceid);
  }
}
function update_invoice_total_field($total, $invoiceid)
{
  global $adb;
  $sql = "UPDATE vtiger_invoice SET total=?, balance=?, subtotal=?, pre_tax_total=?, taxtype=? WHERE invoiceid=?";
  $adb->pquery($sql, array($total, $total, $total, $total, 'group_tax_inc', $invoiceid));
}
function get_total_sum_by_service($invoiceid)
{
  global $adb;
  $sql = "SELECT SUM(margin) AS total FROM vtiger_inventoryproductrel WHERE id=?";
  $result = $adb->pquery($sql, array($invoiceid));
  $total = $adb->query_result($result, 0, 'total');
  return $total;
}
function update_flat_debt_by_flatid($flatid)
{
  global $adb;

  $adb->pquery("UPDATE vtiger_flatscf fcf
                SET fcf.cf_1289 = (                                   
                            (SELECT IFNULL((SELECT round(sum(total),3) AS summ FROM vtiger_invoice AS I
                                                INNER JOIN vtiger_invoicecf AS ICF ON ICF.invoiceid = I.invoiceid
                                                              INNER JOIN vtiger_crmentity AS CE ON I.invoiceid = CE.crmid
                                                              WHERE deleted = 0
                                                              AND invoicestatus not IN ('Cancel')
                                                              AND ICF.cf_1265 = fcf.flatsid),0)) 
                              -
                            ((SELECT IFNULL( (select round(SUM(amount),3) as summ FROM sp_payments as SP
                                                    INNER JOIN sp_paymentscf AS SPCF ON SPCF.payid = SP.payid
                                                                  INNER JOIN  vtiger_crmentity AS SCE ON SP.payid = SCE.crmid 
                                                                  WHERE SCE.deleted = 0
                                                                  AND pay_type = 'Receipt'
                                                                  AND SPCF.cf_1416 = fcf.flatsid), 0))) 
                            ) 	
                WHERE fcf.flatsid = ?", array($flatid));
}
// echo $adb->num_rows($res); 
exit();

// create_invoice_cron();
