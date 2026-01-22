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

$path = "toktogul_saldo_15.11.23.xlsx";
$reader = PHPExcel_IOFactory::createReaderForFile($path);
$excel_Obj = $reader->load($path);
$worksheet = $excel_Obj->getSheet('0');
$lastRow = $worksheet->getHighestRow();


$period = new DateTime();
$period->modify("-1 month");
$due_date = new DateTime();
$theme = "Задолженность на 01.11.2022";
var_dump('exit');
exit();
// 174
for ($i = 3; $i <= 3563; $i++) {
    // for ($i = 1; $i <= 1; $i++) {
    $lastname = trim($worksheet->getCell('D' . $i)->getValue());
    $street = trim($worksheet->getCell('E' . $i)->getValue());
    $house_number = trim($worksheet->getCell('F' . $i)->getValue());
    $amount = trim($worksheet->getCell('G' . $i)->getValue());

    $assigned_user_id = 30;

    if ($amount != 0 && is_numeric($amount)) {
        $check_flat = $adb->run_query_allrecords("SELECT * FROM vtiger_flatscf fcf
                        INNER JOIN vtiger_flats vf ON fcf.flatsid = vf.flatsid 
                        INNER JOIN vtiger_contactdetails vc ON fcf.cf_1235 = vc.contactid 
                        INNER JOIN vtiger_crmentity vc2 ON fcf.flatsid = vc2.crmid 
                        WHERE vc2.deleted = 0
                        AND vc.lastname = '$lastname'
                        AND fcf.cf_1448 = '$street'
                        AND vf.flat = '$house_number'");
        if (count($check_flat) > 0) {
            $flat_id = $check_flat[0]['flatsid'];
            $contact_id = $check_flat[0]['contactid'];

            if ($amount < 0) {

                $payments = Vtiger_Record_Model::getCleanInstance("SPPayments");
                $payments->set('assigned_user_id', $assigned_user_id);
                $payments->set('amount', abs($amount));
                $payments->set('pay_type', 'Приход');
                $payments->set('cf_1416', $flat_id);
                $payments->set('pay_date', '15-11-2023');
                // $payments->set('cf_1466', 'Вывоз ТБО');
                $payments->set('payer', $contact_id);
                $payments->set('description', 'Первичный ввод переплаты');
                // $payments->set('related_to',0);
                // $payments->set('type_payment','Cashless Transfer');
                $payments->set('spstatus', 'Executed');
                $payments->set('mode', 'create');
                $payments->set('target_code', 'default');
                $payments->save();

                $flats = Vtiger_Record_Model::getInstanceById($flat_id, "Flats");
                $flats->set('mode', 'edit');
                $flats->save();

                $logger->log("$i Оплата успешно добавлена. $lastname, ул.$street дом.$house_number +++++++ ");

            } elseif ($amount > 0) {

                $invoice = Vtiger_Record_Model::getCleanInstance("Invoice");
                $invoice->set('contact_id', $contact_id);
                $invoice->set('cf_1265', $flat_id);
                $invoice->set('subject', 'Первичный ввод задолженности');
                $invoice->set('invoicedate', '15-11-2023');
                $invoice->set('invoicestatus', 'AutoCreated');
                $invoice->set('assigned_user_id', $assigned_user_id);
                $invoice->set('mode', 'create');
                $invoice->save();
                $invoice_id = $invoice->getId();

                if ($invoice_id != null) {

                    $service_id = 78178;
                    $quantity = 1;
                    add_service_to_invoice_xlsx($invoice_id, $service_id, 0, 0, $quantity, abs($amount), '');

                    // Обновление задолженности по Объекту
                    $sql = "SELECT ifnull(sum(a.total),0) FROM vtiger_invoice as a 
                        INNER JOIN vtiger_crmentity as b ON b.crmid=a.invoiceid 
                        INNER JOIN vtiger_invoicecf as c ON b.crmid=c.invoiceid 
                        WHERE b.deleted=0 and c.cf_1265=$flat_id";
                    $invsum = $adb->run_query_field($sql);

                    $sql2 = "SELECT ifnull(sum(a.amount),0) FROM sp_payments as a 
                        INNER JOIN vtiger_crmentity as b ON b.crmid=a.payid 
                        INNER JOIN sp_paymentscf as c ON b.crmid=c.payid 
                        WHERE b.deleted=0 and c.cf_1416=$flat_id";
                    $paysum = $adb->run_query_field($sql2);
                    $res = number_format($invsum - $paysum, 0, '.', '');

                    $adb->run_query_field("UPDATE vtiger_flatscf set cf_1289='$res' WHERE flatsid='$flat_id'");


                }
                $flats = Vtiger_Record_Model::getInstanceById($flat_id, "Flats");
                $flats->set('mode', 'edit');
                $flats->save();

                $logger->log("$i Счет успешно добавлен. $lastname, ул.$street дом.$house_number ------- ");
            }
        } else {
            $logger->log("$i ----------------NOT FOUND ABONENT $lastname, ул.$street дом.$house_number");
        }
    }
}

function add_service_to_invoice_xlsx($invoiceid, $serviceid, $prev_md, $current_md, $quantity, $listprice, $accrual_base)
{
    $margin = $listprice * $quantity;
    global $adb;
    $sql = "INSERT INTO vtiger_inventoryproductrel(id, productid, quantity, listprice, margin,accrual_base, previous_reading, current_reading) VALUES(?,?,?,?,?,?,?,?)";
    $params = array($invoiceid, $serviceid, $quantity, $listprice, $margin, $accrual_base, $prev_md, $current_md);
    $adb->pquery($sql, $params);
    $total = get_total_sum_by_service_xlsx($invoiceid);

    if ($total) {
        update_invoice_total_field_xlsx($total, $invoiceid);
    }
}

function get_total_sum_by_service_xlsx($invoiceid)
{
    global $adb;
    $sql = "SELECT SUM(margin) AS total FROM vtiger_inventoryproductrel WHERE id=?";
    $result = $adb->pquery($sql, array($invoiceid));
    $total = $adb->query_result($result, 0, 'total');
    return $total;
}

function update_invoice_total_field_xlsx($total, $invoiceid)
{
    global $adb;
    $sql = "UPDATE vtiger_invoice SET total=?, balance=?, subtotal=?, pre_tax_total=?, taxtype=? WHERE invoiceid=?";
    $adb->pquery($sql, array($total, $total, $total, $total, 'group_tax_inc', $invoiceid));
}
?>