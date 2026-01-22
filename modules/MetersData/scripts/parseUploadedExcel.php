<?php
require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/InventoryUtils.php';
require_once 'modules/Users/Users.php';
//require_once 'modules/Vtiger/VTJsonCondition.php';

vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.Globals');
vimport('includes.http.Request');

//require_once 'modules/MetersData/scripts/Logger.php';
require_once 'phpexcel/Classes/PHPExcel.php';

function processUploadedExcel($path) {
    global $adb;
    $current_user = Users::getActiveAdminUser();
//    $logger = new CustomLogger('phpexcel/import_excel.log');

//    if (!file_exists($path)) {
//        $logger->log("Файл не найден: $path");
//        echo "Файл не найден: $path";
//        return;
//    }

    try {
        $reader = PHPExcel_IOFactory::createReaderForFile($path);
        $excel_Obj = $reader->load($path);
//        $logger->log("Файл успешно загружен: $path");
    } catch (Exception $e) {
//        $logger->log("Ошибка чтения Excel: " . $e->getMessage());
        echo "Ошибка чтения Excel: " . $e->getMessage();
        return;
    }

    $worksheet = $excel_Obj->getSheet(0);

    for ($row = 3; $row <= $worksheet->getHighestRow(); $row++) {
        $meterNumber = trim($worksheet->getCell('B' . $row)->getValue());
        $meterValue = trim($worksheet->getCell('C' . $row)->getValue());
        $lastUpdate = trim($worksheet->getCell('D' . $row)->getValue());

        if (empty($meterNumber) || !is_numeric($meterValue)) {
//            $logger->log("Пропущена строка $row: пустой номер или значение.");
            continue;
        }

//        $logger->log("Обработка счётчика: $meterNumber, значение: $meterValue, дата: $lastUpdate");

        $meterShort = ltrim($meterNumber, '0');

        $meterQuery = $adb->pquery(
            "SELECT m.metersid, mcf.cf_1319 AS house_id FROM vtiger_meters m 
             INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
             INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
             WHERE crm.deleted = 0 AND RIGHT(m.meter, 5) = ?",
            [$meterShort]
        );

        if ($adb->num_rows($meterQuery) === 0) {
//            $logger->log("Счётчик $meterShort не найден.");
            continue;
        }

        $meterId = $adb->query_result($meterQuery, 0, 'metersid');
        $houseId = $adb->query_result($meterQuery, 0, 'house_id');
        $lastUpdateFormatted = date('Y-m-d', strtotime($lastUpdate));

        $existingQuery = $adb->pquery(
            "SELECT md.metersdataid, md.data FROM vtiger_metersdata md
             INNER JOIN vtiger_metersdatacf mdcf ON md.metersdataid = mdcf.metersdataid
             INNER JOIN vtiger_crmentity crm ON crm.crmid = md.metersdataid
             WHERE crm.deleted = 0 AND mdcf.cf_1317 = ? AND DATE(mdcf.cf_1325) = ? AND mdcf.cf_1521 = 0
             ORDER BY mdcf.cf_1325 DESC LIMIT 1",
            [$meterId, $lastUpdateFormatted]
        );

        $lastValue = $adb->num_rows($existingQuery) > 0 ? floatval($adb->query_result($existingQuery, 0, 'data')) : 0;

        if ($adb->num_rows($existingQuery) > 0) {
            $metersdataid = $adb->query_result($existingQuery, 0, 'metersdataid');
            if ($meterValue > $lastValue) {
                $adb->pquery("UPDATE vtiger_metersdata SET data = ? WHERE metersdataid = ?", [$meterValue, $metersdataid]);
                $adb->pquery("UPDATE vtiger_metersdatacf SET cf_1325 = ? WHERE metersdataid = ?", [$lastUpdateFormatted, $metersdataid]);
//                $logger->log("Обновлено: #$meterNumber = $meterValue ($lastUpdate)");
            } else {
//                $logger->log("Новое значение меньше или равно старому ($lastValue). Пропущено.");
            }
        } else {
            if ($meterValue > $lastValue) {
                $MetersData = Vtiger_Record_Model::getCleanInstance("MetersData");
                $MetersData->set('data', $meterValue);
                $MetersData->set('cf_1317', $meterId);
                $MetersData->set('cf_1325', $lastUpdateFormatted);
                $MetersData->set('cf_1333', $houseId);
                $MetersData->set('cf_1327', 'Импорт из Excel Elehant');
                $MetersData->set('assigned_user_id', 1);
                $MetersData->set('mode', 'create');
                $MetersData->save();
//                $logger->log("Добавлено новое показание для #$meterNumber: $meterValue ($lastUpdate)");
            } else {
//                $logger->log("Новое значение меньше предыдущего. Пропущено.");
            }
        }
    }

    echo "Импорт завершён. Проверяйте журнал: `phpexcel/import_excel.log`";
}
