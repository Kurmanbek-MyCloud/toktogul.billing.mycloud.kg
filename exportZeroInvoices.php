<?php
chdir('../');

require 'vendor/autoload.php';
include_once 'includes/Loader.php';
require_once 'include/utils/utils.php';

vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Globals');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');
vimport('includes.http.Request');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

global $adb, $current_user;
$current_user = Users::getActiveAdminUser();

// Задаем ID услуги, которую ищем в счетах
$targetServiceId = 43210;

// Подготавливаем Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Zero Invoices');

$sheet->fromArray(['ФИО', 'ЛС', 'Квартира', 'Пред. показание', 'Тек. показание', 'Дата счета'], NULL, 'A1');

// Получаем все счета с total = 0 и нужной услугой
$query = "
SELECT inv.invoiceid, inv.invoicedate, c.lastname, f.flat, fcf.cf_1420 AS ls, rel.previous_reading, rel.current_reading
FROM vtiger_invoice inv
INNER JOIN vtiger_invoicecf icf ON inv.invoiceid = icf.invoiceid
INNER JOIN vtiger_crmentity ent ON ent.crmid = inv.invoiceid AND ent.deleted = 0
INNER JOIN vtiger_inventoryproductrel rel ON rel.id = inv.invoiceid
LEFT JOIN vtiger_flats f ON f.flatsid = icf.cf_1265
LEFT JOIN vtiger_flatscf fcf ON fcf.flatsid = f.flatsid
LEFT JOIN vtiger_contactdetails c ON c.contactid = inv.contact_id
WHERE inv.total = 0
AND rel.productid = ?
";

$res = $adb->pquery($query, [$targetServiceId]);

$row = 2;
for ($i = 0; $i < $adb->num_rows($res); $i++) {
    $fio = $adb->query_result($res, $i, 'lastname');
    $ls = $adb->query_result($res, $i, 'ls');
    $flat = $adb->query_result($res, $i, 'flat');
    $prev = $adb->query_result($res, $i, 'previous_reading');
    $curr = $adb->query_result($res, $i, 'current_reading');
    $date = $adb->query_result($res, $i, 'invoicedate');

    $sheet->setCellValue("A{$row}", $fio);
    $sheet->setCellValue("B{$row}", $ls);
    $sheet->setCellValue("C{$row}", $flat);
    $sheet->setCellValue("D{$row}", $prev);
    $sheet->setCellValue("E{$row}", $curr);
    $sheet->setCellValue("F{$row}", $date);
    $row++;
}

// Сохраняем
$filename = 'zero_invoices_export.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

echo "Готово! Файл сохранен как $filename\n";
