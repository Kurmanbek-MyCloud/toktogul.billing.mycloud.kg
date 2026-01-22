<?php

// Устанавливаем часовой пояс в самом начале
// date_default_timezone_set('Asia/Bishkek');

// echo("В разработке");
// exit;

chdir('../');

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

$logFile = 'connector_quant/connector_quant.log';
$logger = new CustomLogger($logFile);

// Получаем счетчики для обработки
$res = $adb->pquery("SELECT vm.meter FROM vtiger_meters vm 
                    INNER JOIN vtiger_crmentity vc on vm.metersid = vc.crmid 
                    WHERE vc.deleted = 0", array());
$meters_to_process = [];
for ($i = 0; $i < $adb->num_rows($res); $i++) {
    $meters_to_process[] = $adb->query_result($res, $i, 'meter');
}

if (isset($_GET['get_meters'])) {
    echo json_encode(['meters' => $meters_to_process]);
    exit;
}

if (isset($_GET['sync_meters'])) {
    // Получаем список счетчиков из API
    $url = "http://data.quant.kg:433/api/billing/meters";
    $headers = [
        'x-api-key: 3c6b75546d15fe041ef9bcee0'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);

    $logger->log("Синхронизация счетчиков: Ответ от API: " . $response);

    if (curl_errno($ch)) {
        $logger->log("Синхронизация счетчиков: Ошибка cURL - " . curl_error($ch));
        curl_close($ch);
        echo json_encode(['status' => 'error', 'message' => 'Ошибка cURL: ' . curl_error($ch)]);
        exit;
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $meters_from_api = json_decode($response, true);
        if (is_array($meters_from_api)) {
            $logger->log("Счетчиков в базе: " . count($meters_to_process) . " - " . implode(', ', $meters_to_process));
            $processed_count = 0;
            $skipped_count = 0;
            $not_found_count = 0;
            
            foreach ($meters_from_api as $meter_data) {
                $meter_id = isset($meter_data['id']) ? $meter_data['id'] : null;
                $last_value = isset($meter_data['lastValue']) ? $meter_data['lastValue'] : null;
                $updated_at = isset($meter_data['updatedAt']) ? $meter_data['updatedAt'] : null;
                
                if (!$meter_id) {
                    continue;
                }
                
                // Проверяем, есть ли такой счетчик в нашей базе
                // Используем строгое сравнение с приведением типов
                $found = false;
                foreach ($meters_to_process as $db_meter) {
                    if ((string)$meter_id === (string)$db_meter) {
                        $found = true;
                        break;
                    }
                }
                
                if ($found) {
                    if ($last_value !== null && $updated_at !== null) {
                        $logger->log("Счетчик $meter_id: Найден в базе, обрабатываем. Значение: $last_value");
                        add_indication_from_meters_api($adb, $meter_id, $last_value, $updated_at, $logger);
                        $processed_count++;
                    } else {
                        $logger->log("Счетчик $meter_id: Найден в базе, но отсутствуют lastValue или updatedAt. Пропускаем.");
                        $skipped_count++;
                    }
                } else {
                    $not_found_count++;
                    $logger->log("Счетчик $meter_id из API не найден в базе данных");
                }
            }
            
            $logger->log("Синхронизация завершена: обработано $processed_count, пропущено $skipped_count, не найдено в базе: $not_found_count");
            echo json_encode([
                'status' => 'ok',
                'processed' => $processed_count,
                'skipped' => $skipped_count
            ]);
        } else {
            $logger->log("Синхронизация счетчиков: Неверный формат ответа от API");
            echo json_encode(['status' => 'error', 'message' => 'Неверный формат ответа от API']);
        }
    } else {
        $logger->log("Синхронизация счетчиков: Ошибка API - HTTP $http_code. Ответ: $response");
        echo json_encode(['status' => 'error', 'message' => "HTTP $http_code", 'response' => $response]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Получаем только последние логи по каждому счетчику
    $logFile = 'connector_quant/connector_quant.log';
    $logs = [];
    if (file_exists($logFile)) {
        $logLines = array_reverse(file($logFile));
        $processed_meters = [];
        foreach ($logLines as $log) {
            foreach ($meters_to_process as $meter) {
                if (strpos($log, "Счетчик $meter:") !== false && !isset($processed_meters[$meter])) {
                    $logs[] = $log;
                    $processed_meters[$meter] = true;
                    break;
                }
            }
            if (!empty($meters_to_process) && count($processed_meters) === count($meters_to_process)) {
                break;
            }
        }
    }

    echo json_encode([
        'meters' => $meters_to_process,
        'logs' => $logs
    ]);
    exit;
}

function add_indication_from_meters_api($adb, $meter_number, $last_value, $updated_at, $logger)
{
    $new_indication = floatval($last_value);
    $date_indication_db = date('Y-m-d', strtotime($updated_at));
    
    $logger->log("Счетчик $meter_number: Обработка показания из API. Значение: $new_indication, Дата: $date_indication_db");

    $result = $adb->run_query_allrecords("SELECT vm.metersid FROM vtiger_meters vm
           INNER JOIN vtiger_crmentity vc ON vc.crmid = vm.metersid
           WHERE vc.deleted = 0 AND vm.meter = '$meter_number'");
    
    if (empty($result) || !isset($result[0]['metersid'])) {
        $logger->log("Счетчик $meter_number: Не найден в базе. Пропускаем.");
        return;
    }
    
    $meters_id = $result[0]['metersid'];

    $get_flatsid = $adb->pquery("SELECT vf.flatsid FROM vtiger_flatscf vf 
                                              INNER JOIN vtiger_crmentity vc on vf.flatsid = vc.crmid 
                                              INNER JOIN vtiger_meterscf vm on vm.cf_1319 = vf.flatsid 
                                              INNER JOIN vtiger_crmentity vc2 on vc2.crmid = vm.metersid 
                                              WHERE vc.deleted = 0 and vc2.deleted = 0 and vm.metersid = ?", array($meters_id));

    if ($adb->num_rows($get_flatsid) == 0) {
        $logger->log("Счетчик $meter_number: Не найден flatsid в базе. Пропускаем.");
        return;
    }

    $flats_id = $adb->query_result($get_flatsid, 0, 'flatsid');

    // Проверяем, есть ли уже показание за этот месяц
    $existing_data = $adb->pquery(
        "SELECT md.metersdataid, md.data FROM vtiger_metersdata md
         INNER JOIN vtiger_metersdatacf mdcf ON md.metersdataid = mdcf.metersdataid
         INNER JOIN vtiger_crmentity vc ON md.metersdataid = vc.crmid
         WHERE vc.deleted = 0 AND mdcf.cf_1317 = ? AND DATE_FORMAT(mdcf.cf_1325, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')
         ORDER BY mdcf.cf_1325 DESC LIMIT 1",
        array($meters_id, $date_indication_db)
    );

    if ($adb->num_rows($existing_data) > 0) {
        $last_value = $adb->query_result($existing_data, 0, 'data');
        $metersdataid = $adb->query_result($existing_data, 0, 'metersdataid');
        if ($new_indication > $last_value) {
            $adb->pquery("UPDATE vtiger_metersdata SET data = ? WHERE metersdataid = ?", array($new_indication, $metersdataid));
            $adb->pquery("UPDATE vtiger_metersdatacf SET cf_1325 = ? WHERE metersdataid = ?", array($date_indication_db, $metersdataid));
            $logger->log("Счетчик $meter_number: Показание ОБНОВЛЕНО с $last_value на $new_indication.");
        } else {
            $logger->log("Счетчик $meter_number: Новое показание ($new_indication) не больше существующего ($last_value). Игнорируем.");
        }
    } else {
        $last_data = $adb->pquery(
            "SELECT md.data FROM vtiger_metersdata md
             INNER JOIN vtiger_metersdatacf mdcf ON md.metersdataid = mdcf.metersdataid
             INNER JOIN vtiger_crmentity vc ON md.metersdataid = vc.crmid
             WHERE vc.deleted = 0 AND mdcf.cf_1317 = ? ORDER BY mdcf.cf_1325 DESC LIMIT 1",
            array($meters_id)
        );

        $add_new = true;
        if ($adb->num_rows($last_data) > 0) {
            $last_value = $adb->query_result($last_data, 0, 'data');
            if ($new_indication <= $last_value) {
                $add_new = false;
                $logger->log("Счетчик $meter_number: Новое показание ($new_indication) не больше последнего ($last_value). Игнорируем.");
            }
        }

        if ($add_new) {
            $indication = Vtiger_Record_Model::getCleanInstance("MetersData");
            $indication->set('cf_1317', $meters_id);
            $indication->set('data', $new_indication);
            $indication->set('cf_1325', $date_indication_db);
            $indication->set('cf_1333', $flats_id);
            $indication->set('cf_1327', 'quant.kg');
            $indication->set('mode', 'create');
            $indication->save();
            $indication_id = $indication->getId();
            if ($indication_id) {
                $logger->log("Счетчик $meter_number: Показание ДОБАВЛЕНО. ID: $indication_id");
            } else {
                $logger->log("Счетчик $meter_number: ОШИБКА при добавлении показания.");
            }
        }
    }
    $logger->log("================================================================================================");
}

?>