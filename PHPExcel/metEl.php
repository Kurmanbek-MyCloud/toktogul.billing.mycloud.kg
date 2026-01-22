<?php
chdir('../');

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

require_once 'Logger.php';
$logger = new CustomLogger('phpexcel/metEl.log');

$path = "phpexcel/elehantMeters23_05_2025.xlsx";
if (!file_exists($path)) {
    $logger->log("Файл не найден: $path");
    die("Файл не найден: $path");
}

try {
    require_once 'phpexcel/Classes/PHPExcel.php';
    $reader = PHPExcel_IOFactory::createReaderForFile($path);
    $excel_Obj = $reader->load($path);
    $logger->log("Файл успешно прочитан.");
} catch (Exception $e) {
    $logger->log("Ошибка чтения Excel: " . $e->getMessage());
    die("Ошибка чтения Excel: " . $e->getMessage());
}

$sheet = $excel_Obj->getSheet(0);
$highestRow = $sheet->getHighestRow();
$logger->log("Обработка строк: всего $highestRow");

// Счётчик строк
for ($row = 3; $row <= $highestRow; $row++) {
    $meterNumber = trim($sheet->getCell("B$row")->getValue());
    $meterValue = trim($sheet->getCell("C$row")->getValue());
    $lastUpdateRaw = $sheet->getCell("D$row")->getValue();

    if (empty($meterNumber) || !is_numeric($meterValue)) {
        $logger->log("Строка $row: Пропущена (невалидный номер счётчика или значение)");
        continue;
    }

    try {
        $lastUpdate = PHPExcel_Shared_Date::isDateTime($sheet->getCell("D$row"))
            ? PHPExcel_Shared_Date::ExcelToPHPObject($lastUpdateRaw)->format('Y-m-d')
            : date('Y-m-d', strtotime($lastUpdateRaw));
    } catch (Exception $e) {
        $logger->log("Строка $row: Ошибка обработки даты: " . $e->getMessage());
        continue;
    }

    $meterShort = ltrim($meterNumber, '0');
    $meterQuery = $adb->pquery(
        "SELECT m.metersid, mcf.cf_1319 AS house_id FROM vtiger_meters m
         INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
         INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
         WHERE crm.deleted = 0 AND RIGHT(m.meter, 5) = ?",
        [$meterShort]
    );

    if ($adb->num_rows($meterQuery) == 0) {
        $logger->log("Строка $row: Счётчик $meterShort не найден.");
        continue;
    }

    $meterId = $adb->query_result($meterQuery, 0, 'metersid');
    $houseId = $adb->query_result($meterQuery, 0, 'house_id');

    $existingQuery = $adb->pquery(
        "SELECT md.metersdataid, md.data FROM vtiger_metersdata md
         INNER JOIN vtiger_metersdatacf mdcf ON md.metersdataid = mdcf.metersdataid
         INNER JOIN vtiger_crmentity crm ON crm.crmid = md.metersdataid
         WHERE crm.deleted = 0 AND mdcf.cf_1317 = ? 
         AND MONTH(mdcf.cf_1325) = MONTH(?) 
         AND YEAR(mdcf.cf_1325) = YEAR(?) 
         AND mdcf.cf_1521 = 0
         ORDER BY mdcf.cf_1325 DESC LIMIT 1",
        [$meterId, $lastUpdate, $lastUpdate]
    );

    $lastValue = $adb->num_rows($existingQuery) > 0 ? floatval($adb->query_result($existingQuery, 0, 'data')) : 0;

    if ($adb->num_rows($existingQuery) > 0) {
        $mdid = $adb->query_result($existingQuery, 0, 'metersdataid');
        if ($meterValue > $lastValue) {
            $adb->pquery("UPDATE vtiger_metersdata SET data = ? WHERE metersdataid = ?", [$meterValue, $mdid]);
            $adb->pquery("UPDATE vtiger_metersdatacf SET cf_1325 = ? WHERE metersdataid = ?", [$lastUpdate, $mdid]);
            $logger->log("Обновлено: Счётчик #$meterShort → $meterValue ($lastUpdate)");
        } else {
            $logger->log("Игнорировано: Счётчик #$meterShort → $meterValue не больше текущего ($lastValue)");
        }
    } else {
        if ($meterValue > $lastValue) {
            $record = Vtiger_Record_Model::getCleanInstance("MetersData");
            $record->set('data', $meterValue);
            $record->set('cf_1317', $meterId);
            $record->set('cf_1325', $lastUpdate);
            $record->set('cf_1333', $houseId);
            $record->set('cf_1327', 'Excel-файл');
            $record->set('assigned_user_id', 1);
            $record->set('mode', 'create');
            $record->save();
            $logger->log("Добавлено: Счётчик #$meterShort → $meterValue ($lastUpdate)");
        } else {
            $logger->log("Игнорировано: $meterValue <= $lastValue");
        }
    }
}

$logger->log("Импорт завершён.");
?>
