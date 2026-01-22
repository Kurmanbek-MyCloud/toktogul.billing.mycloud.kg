<?php
// Консольный загрузчик Excel → Vtiger MetersData
chdir('../');
include_once 'includes/Loader.php';
include_once 'include/utils/utils.php';
include_once 'include/utils/InventoryUtils.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');
require_once('libraries/PHPExcel/Classes/PHPExcel/IOFactory.php');

global $adb;
global $current_user;
$current_user = Users::getActiveAdminUser();

$logs = [];

// Проверка аргументов
if ($argc < 2) {
    echo "Использование: php import_excel.php путь/к/файлу.xlsx\n";
    exit(1);
}

$excelFile =  "phpexcel/elehantMeters23_05_2025.xlsx";;
if (!file_exists($excelFile)) {
    echo "Файл не найден: $excelFile\n";
    exit(1);
}

$objPHPExcel = PHPExcel_IOFactory::load($excelFile);
$sheet = $objPHPExcel->getActiveSheet();
$highestRow = $sheet->getHighestRow();

logMessage("Начата обработка файла: $excelFile");
logMessage("Всего строк: $highestRow");

for ($row = 2; $row <= 3; $row++) {
    $meterNumber = trim($sheet->getCell("A")->getValue());
    $meterValue = trim($sheet->getCell("B")->getValue());
    $lastUpdateCell = $sheet->getCell("C")->getValue();
    echo $meterNumber;

    if (!$lastUpdateCell) {
        logMessage("Строка $row: Пропущена (нет даты)");
        continue;
    }

    try {
        $lastUpdate = PHPExcel_Shared_Date::ExcelToPHPObject($lastUpdateCell)->format('Y-m-d');
    } catch (Exception $e) {
        logMessage("Строка $row: Ошибка при обработке даты: " . $e->getMessage());
        continue;
    }

    if (!is_numeric($meterNumber) || !is_numeric($meterValue)) {
        logMessage("Строка $row: Невалидные данные счётчика: [$meterNumber, $meterValue]");
        continue;
    }

    // Поиск счётчика
    $res = $adb->pquery("SELECT m.metersid, m.meter, mcf.cf_1319 AS house_id 
                         FROM vtiger_meters m 
                         INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
                         INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
                         WHERE crm.deleted = 0 AND RIGHT(m.meter, 5) = ?", array($meterNumber));

    if ($adb->num_rows($res) == 0) {
        logMessage("Строка $row: Счётчик $meterNumber не найден.");
        continue;
    }

    $meterId = $adb->query_result($res, 0, 'metersid');
    $houseId = $adb->query_result($res, 0, 'house_id');

    // Проверка на уже существующее показание
    $existing = $adb->pquery("SELECT md.metersdataid, md.data 
                              FROM vtiger_metersdata md 
                              INNER JOIN vtiger_metersdatacf mdcf ON md.metersdataid = mdcf.metersdataid 
                              INNER JOIN vtiger_crmentity vc ON vc.crmid = md.metersdataid 
                              WHERE vc.deleted = 0 AND mdcf.cf_1317 = ? 
                              AND MONTH(mdcf.cf_1325) = MONTH(?) 
                              AND YEAR(mdcf.cf_1325) = YEAR(?) 
                              AND mdcf.cf_1521 = 0
                              ORDER BY mdcf.cf_1325 DESC LIMIT 1", array($meterId, $lastUpdate, $lastUpdate));

    $lastValue = $adb->query_result($existing, 0, 'data');

    if ($adb->num_rows($existing) > 0) {
        $mdid = $adb->query_result($existing, 0, 'metersdataid');
        if ($meterValue > $lastValue) {
            $adb->pquery("UPDATE vtiger_metersdata SET data = ? WHERE metersdataid = ?", array($meterValue, $mdid));
            $adb->pquery("UPDATE vtiger_metersdatacf SET cf_1325 = ? WHERE metersdataid = ?", array($lastUpdate, $mdid));
            logMessage("Обновлено: Счётчик #$meterNumber → $meterValue ($lastUpdate)");
        } else {
            logMessage("Игнорировано: Счётчик #$meterNumber → $meterValue не больше существующего ($lastValue)");
        }
    } else {
        $record = Vtiger_Record_Model::getCleanInstance("MetersData");
        $record->set('data', $meterValue);
        $record->set('cf_1317', $meterId);
        $record->set('cf_1325', $lastUpdate);
        $record->set('cf_1333', $houseId);
        $record->set('cf_1327', 'Excel-файл');
        $record->set('assigned_user_id', 1);
        $record->set('mode', 'create');
        $record->save();
        logMessage("Добавлено: Счётчик #$meterNumber → $meterValue ($lastUpdate)");
    }
}

logMessage("Обработка завершена.");

function logMessage($msg) {
    $timestamp = date('[Y-m-d H:i:s] ');
    $logLine = $timestamp . $msg;
    echo $logLine . "\n";

    $logDir = __DIR__ . '/phpexcel';
    $logFile = $logDir . '/metEl2.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND);
}
