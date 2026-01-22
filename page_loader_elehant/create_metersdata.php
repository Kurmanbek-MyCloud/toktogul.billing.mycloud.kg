<?php
// Устанавливаем часовой пояс
date_default_timezone_set('Asia/Bishkek');

chdir('../');
include_once 'includes/Loader.php';
include_once 'include/utils/utils.php';
include_once 'include/utils/InventoryUtils.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');
// Подключаем PHPExcel
require_once 'libraries/PHPExcel/PHPExcel.php';

global $adb;
global $current_user;
$current_user = Users::getActiveAdminUser();

// Включаем буферизацию вывода
ob_start();

// Создаем простой логгер
function writeLog($message)
{
    $logFile = 'page_loader_elehant/create_metersdata.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// writeLog("Начало обработки данных в create_metersdata.php");
// writeLog("Текущая рабочая директория: " . getcwd());

// Получаем текущий месяц
$currentMonth = date('n'); // n - месяц без ведущего нуля (1-12)
$currentYear = date('Y');

writeLog("Текущий месяц: $currentMonth, год: $currentYear");

// Ищем временные файлы с данными
// После chdir('../') мы находимся в /var/www/toktogul.billing.mycloud.kg
// main.php создает файлы в корневой директории, поэтому ищем там
$tempFiles = glob('temp_data_*.json');

// writeLog("Ищем файлы по шаблону: temp_data_*.json");

// writeLog("Ищем временные файлы в текущей директории");
// writeLog("Найдено файлов: " . count($tempFiles));
if (!empty($tempFiles)) {
    writeLog("Список найденных файлов: " . implode(', ', $tempFiles));
} else {
    // Показываем содержимое текущей директории
    $currentFiles = scandir('.');
    writeLog("Содержимое текущей директории: " . implode(', ', $currentFiles));
}

if (empty($tempFiles)) {
    writeLog("Временные файлы с данными не найдены");
    exit("Нет данных для обработки");
}

// Обрабатываем каждый временный файл
foreach ($tempFiles as $tempFile) {
    // Проверяем, не обрабатывается ли файл в данный момент (через lock файл)
    $lockFile = $tempFile . '.lock';
    if (file_exists($lockFile)) {
        // Проверяем, не устарел ли lock файл (если старше 5 минут, считаем его устаревшим)
        if (time() - filemtime($lockFile) > 300) {
            writeLog("Удаляем устаревший lock файл: " . $lockFile);
            @unlink($lockFile);
        } else {
            writeLog("Файл $tempFile уже обрабатывается (найден lock файл) - пропускаем");
            continue;
        }
    }
    
    // Создаем lock файл
    file_put_contents($lockFile, date('Y-m-d H:i:s'));
    writeLog("Начинаем обработку файла: " . $tempFile);

    // Читаем данные из временного файла
    $jsonData = file_get_contents($tempFile);
    $fileData = json_decode($jsonData, true);

    if (!$fileData) {
        writeLog("Ошибка при чтении JSON данных из файла: " . $tempFile);
        continue;
    }

    writeLog("Данные загружены. Всего строк: " . $fileData['totalRows']);

    // Отладочная информация о структуре данных
    // writeLog("Структура данных:");
    // writeLog("- headers: " . (isset($fileData['headers']) ? 'есть' : 'нет'));
    // writeLog("- data: " . (isset($fileData['data']) ? 'есть' : 'нет'));
    // writeLog("- structuredData: " . (isset($fileData['structuredData']) ? 'есть' : 'нет'));

    // if (isset($fileData['headers'])) {
    //     writeLog("Заголовки: " . implode(', ', $fileData['headers']));
    // }

    // if (isset($fileData['structuredData']) && !empty($fileData['structuredData'])) {
    //     $firstRow = $fileData['structuredData'][0];
    //     // writeLog("Первый ряд структурированных данных:");
    //     // foreach ($firstRow as $key => $value) {
    //     //     writeLog("  '$key' => '$value'");
    //     // }
    // }

    // Фильтруем данные по текущему месяцу
    $filteredData = array();
    $stoppedAtRow = 0;

    foreach ($fileData['structuredData'] as $rowIndex => $row) {
        // Ищем столбец "Последнее обновление"
        $lastUpdateColumn = null;
        foreach ($row as $columnName => $value) {
            // Более гибкий поиск столбца с датой обновления
            if (
                strpos($columnName, 'Последнее обновление') !== false ||
                strpos($columnName, 'обновление') !== false ||
                strpos($columnName, 'update') !== false ||
                strpos($columnName, 'дата') !== false ||
                strpos($columnName, 'date') !== false ||
                strpos($columnName, 'время') !== false ||
                strpos($columnName, 'time') !== false
            ) {
                $lastUpdateColumn = $columnName;
                // writeLog("Найден столбец для даты обновления: '$columnName'");
                break;
            }
        }

        if ($lastUpdateColumn && isset($row[$lastUpdateColumn])) {
            $dateString = $row[$lastUpdateColumn];
            
            // Проверяем, если дата пустая - останавливаем обработку
            if (empty($dateString) || trim($dateString) === '') {
                writeLog("Строка $rowIndex: дата пустая. Останавливаем обработку.");
                $stoppedAtRow = $rowIndex;
                break;
            }
            
            // writeLog("Строка $rowIndex: дата обновления = '$dateString'");

            // Парсим дату (формат: 09-10-2025 04:36:46)
            if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $dateString, $matches)) {
                $day = $matches[1];
                $month = intval($matches[2]);
                $year = intval($matches[3]);

                // writeLog("Парсинг даты: день=$day, месяц=$month, год=$year");

                // Проверяем, соответствует ли месяц текущему
                if ($month == $currentMonth && $year == $currentYear) {
                    $filteredData[] = $row;
                    // writeLog("Строка $rowIndex добавлена в фильтрованные данные");
                } else {
                    writeLog("Строка $rowIndex не соответствует текущему месяцу. Останавливаем обработку.");
                    $stoppedAtRow = $rowIndex;
                    break; // Прекращаем обработку, так как остальные данные уже не в текущем месяце
                }
            } else {
                writeLog("Не удалось распарсить дату в строке $rowIndex: '$dateString'. Останавливаем обработку.");
                $stoppedAtRow = $rowIndex;
                break;
            }
        } else {
            writeLog("Столбец 'Последнее обновление' не найден в строке $rowIndex");
            writeLog("Доступные столбцы в строке $rowIndex: " . implode(', ', array_keys($row)));
        }
    }

    writeLog("Фильтрация завершена. Отфильтровано строк: " . count($filteredData));
    writeLog("Остановились на строке: $stoppedAtRow");

    // Обрабатываем каждую отфильтрованную строку
    $processedCount = 0;
    foreach ($filteredData as $rowIndex => $row) {
        // Извлекаем нужные данные из строки
        $number_schetchik = '';
        $indications = '';
        $date_indications = '';

        foreach ($row as $columnName => $value) {
            if (
                strpos($columnName, 'Серийный номер') !== false ||
                strpos($columnName, 'номер') !== false
            ) {
                $number_schetchik = $value;
            } elseif (strpos($columnName, 'Показания') !== false) {
                $indications = $value;
            } elseif (
                strpos($columnName, 'Последнее обновление') !== false ||
                strpos($columnName, 'обновление') !== false
            ) {
                $date_indications = $value;
            }
        }

        // Проверяем, что все необходимые данные найдены
        if (!empty($number_schetchik) && !empty($indications) && !empty($date_indications)) {
            // Вызываем функцию add_indications
            $result = add_indications($adb, $number_schetchik, $indications, $date_indications, 'writeLog');

            if ($result) {
                $processedCount++;
            }
        } else {
            writeLog("Строка $rowIndex: пропущена - не все данные найдены (счетчик: " . ($number_schetchik ?: 'нет') . ", показания: " . ($indications ?: 'нет') . ", дата: " . ($date_indications ?: 'нет') . ")");
        }
    }

    writeLog("Обработано строк: $processedCount из " . count($filteredData));

    // ВАЖНО: Удаляем только временный JSON файл (temp_data_*.json)
    // XLSX файлы в папке files/ НЕ удаляются и должны оставаться для истории
    if (file_exists($tempFile)) {
        @unlink($tempFile);
        writeLog("Временный JSON файл удален: " . $tempFile . " (XLSX файл остается в папке files/)");
    }
    if (file_exists($lockFile)) {
        @unlink($lockFile);
        writeLog("Lock файл удален: " . $lockFile);
    }
}

writeLog("Обработка данных завершена");

// Очищаем буфер вывода
ob_end_clean();

// Функция для добавления показаний счетчика
function add_indications($adb, $number_schetchik, $indications, $date_indications, $logger)
{
    // Проверка существующего показания за тот же месяц
    $check_meter = $adb->pquery("SELECT vm.metersid FROM vtiger_meters vm 
                                    INNER JOIN vtiger_meterscf vm2 on vm2.metersid = vm.metersid 
                                    INNER JOIN vtiger_crmentity vc on vc.crmid = vm.metersid 
                                    WHERE vc.deleted = 0 and vm.meter = ?", array($number_schetchik));
    $meters_id = $adb->query_result($check_meter, 0, 'metersid');

    if ($check_meter && $adb->num_rows($check_meter) > 0) {
        $check_indication = $adb->pquery("SELECT vm.metersdataid, vm.`data`, vm2.cf_1325, vf.flatsid 
                                            FROM vtiger_metersdata vm
                                            INNER JOIN vtiger_metersdatacf vm2 ON vm.metersdataid = vm2.metersdataid
                                            INNER JOIN vtiger_crmentity vc ON vc.crmid = vm.metersdataid
                                            INNER JOIN vtiger_meters vm3 ON vm3.metersid = vm2.cf_1317
                                            INNER JOIN vtiger_crmentity vc2 ON vc2.crmid = vm3.metersid
                                            INNER JOIN vtiger_flatscf vf on vf.flatsid = vm2.cf_1333 
                                            INNER JOIN vtiger_crmentity vc3 on vc3.crmid = vf.flatsid 
                                             WHERE vc.deleted = 0 AND vc2.deleted = 0 and vc3.deleted = 0 and vm2.cf_1317 = ?
                                            ORDER BY vc.createdtime DESC
                                            LIMIT 1;", array($meters_id));

        $indication_id = $adb->query_result($check_indication, 0, 'metersdataid');
        $indication_data = $adb->query_result($check_indication, 0, 'data');
        $date_indication = $adb->query_result($check_indication, 0, 'cf_1325');
        $flats_id = $adb->query_result($check_indication, 0, 'flatsid');
        
        // Если flats_id не найден из показаний, пытаемся получить из связи счетчика с квартирой
        if (empty($flats_id)) {
            $flats_query = $adb->pquery("SELECT vf.flatsid 
                                         FROM vtiger_meters vm
                                         INNER JOIN vtiger_meterscf vm2 ON vm2.metersid = vm.metersid
                                         INNER JOIN vtiger_flatscf vf ON vf.flatsid = vm2.cf_1318
                                         INNER JOIN vtiger_crmentity vc ON vc.crmid = vf.flatsid
                                         WHERE vc.deleted = 0 AND vm.metersid = ?
                                         LIMIT 1", array($meters_id));
            if ($adb->num_rows($flats_query) > 0) {
                $flats_id = $adb->query_result($flats_query, 0, 'flatsid');
            }
        }
        
        if ($adb->num_rows($check_indication) > 0) {

            // Парсим дату из файла (формат: 09-10-2025 04:36:46)
            if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $date_indications, $matches)) {
                $file_month = intval($matches[2]);
                $file_year = intval($matches[3]);

                // Парсим дату из базы данных (формат: 2025-10-04 или 09-10-2025)
                // Проверяем оба возможных формата
                if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $date_indication, $db_matches)) {
                    // Формат YYYY-MM-DD
                    $db_month = intval($db_matches[2]);
                    $db_year = intval($db_matches[1]); // ИСПРАВЛЕНО: было [3], должно быть [1]
                } elseif (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $date_indication, $db_matches)) {
                    // Формат DD-MM-YYYY
                    $db_month = intval($db_matches[2]);
                    $db_year = intval($db_matches[3]);
                } else {
                    $db_matches = null;
                }
                
                if ($db_matches) {

                    // Проверяем, совпадает ли месяц и год
                    if ($file_month == $db_month && $file_year == $db_year) {
                        // Сравниваем показания
                        if ($indications > $indication_data) {
                            $logger("Счетчик $number_schetchik: новое показание ($indications) > текущее ($indication_data) - можно обновить");

                            // Преобразуем дату из формата "09-10-2025 04:36:46" в "09-10-2025"
                            $date_for_db = '';
                            if (preg_match('/(\d{1,2}-\d{1,2}-\d{4})/', $date_indications, $date_matches)) {
                                $date_for_db = $date_matches[1];
                            }

                            // Обновляем показание в vtiger_metersdata
                            $adb->pquery("UPDATE vtiger_metersdata SET data = ? WHERE metersdataid = ?", array($indications, $indication_id));

                            // Обновляем cf_1325 в vtiger_metersdatacf
                            $adb->pquery("UPDATE vtiger_metersdatacf SET cf_1325 = ? WHERE metersdataid = ?", array($date_for_db, $indication_id));


                            // Здесь будет логика обновления показания
                        } else {
                            $logger("Новое показание для счетчика #$number_schetchik: Дата: $date_indications - $indications не больше существующего: $indication_data. Игнорируем.");
                        }

                    } else {
                        $logger("Счетчик $number_schetchik: месяц/год не совпадают (файл: $file_month/$file_year, база: $db_month/$db_year) - можно добавить новое показание");

                        // Преобразуем дату из формата "09-10-2025 04:36:46" в "09-10-2025"
                        $date_for_db = '';
                        if (preg_match('/(\d{1,2}-\d{1,2}-\d{4})/', $date_indications, $date_matches)) {
                            $date_for_db = $date_matches[1];
                        }
                        
                        // Проверяем, нет ли уже показания с такой же датой для этого счетчика
                        $duplicate_check = $adb->pquery("SELECT vm.metersdataid 
                                                         FROM vtiger_metersdata vm
                                                         INNER JOIN vtiger_metersdatacf vm2 ON vm.metersdataid = vm2.metersdataid
                                                         INNER JOIN vtiger_crmentity vc ON vc.crmid = vm.metersdataid
                                                         WHERE vc.deleted = 0 AND vm2.cf_1317 = ? AND vm2.cf_1325 = ?
                                                         LIMIT 1", array($meters_id, $date_for_db));
                        
                        if ($adb->num_rows($duplicate_check) > 0) {
                            $logger("Счетчик $number_schetchik: показание с датой $date_for_db уже существует - пропускаем дубликат");
                        } else {
                            $MetersData = Vtiger_Record_Model::getCleanInstance("MetersData");
                            $MetersData->set('data', $indications);
                            $MetersData->set('cf_1317', $meters_id);
                            $MetersData->set('cf_1325', $date_for_db);
                            $MetersData->set('cf_1327', 'сайт cntdev.ru');
                            if (!empty($flats_id)) {
                                $MetersData->set('cf_1333', $flats_id);
                            }
                            $MetersData->set('assigned_user_id', 1);
                            $MetersData->set('mode', 'create');
                            $MetersData->save();
                            $MetersData_id = $MetersData->getId();

                            if ($MetersData_id) {
                                $logger("Показание добавлено для счетчика #$number_schetchik: $indications");
                            } else {
                                $logger("Ошибка при добавлении показание для счетчика #$number_schetchik: $indications");
                            }
                        }
                    }
                } else {
                    $logger("Счетчик $number_schetchik: ошибка парсинга даты из базы ($date_indication)");
                }
            } else {
                $logger("Счетчик $number_schetchik: ошибка парсинга даты из файла ($date_indications)");
            }
        } else {
            $logger("Счетчик $number_schetchik: показания не найдены в базе - можно добавить новое показание");

            // Преобразуем дату из формата "09-10-2025 04:36:46" в "09-10-2025"
            $date_for_db = '';
            if (preg_match('/(\d{1,2}-\d{1,2}-\d{4})/', $date_indications, $date_matches)) {
                $date_for_db = $date_matches[1];
            }
            
            // Проверяем, нет ли уже показания с такой же датой для этого счетчика
            $duplicate_check = $adb->pquery("SELECT vm.metersdataid 
                                             FROM vtiger_metersdata vm
                                             INNER JOIN vtiger_metersdatacf vm2 ON vm.metersdataid = vm2.metersdataid
                                             INNER JOIN vtiger_crmentity vc ON vc.crmid = vm.metersdataid
                                             WHERE vc.deleted = 0 AND vm2.cf_1317 = ? AND vm2.cf_1325 = ?
                                             LIMIT 1", array($meters_id, $date_for_db));
            
            if ($adb->num_rows($duplicate_check) > 0) {
                $logger("Счетчик $number_schetchik: показание с датой $date_for_db уже существует - пропускаем дубликат");
            } else {
                $MetersData = Vtiger_Record_Model::getCleanInstance("MetersData");
                $MetersData->set('data', $indications);
                $MetersData->set('cf_1317', $meters_id);
                $MetersData->set('cf_1325', $date_for_db);
                $MetersData->set('cf_1327', 'сайт cntdev.ru');
                
                if (!empty($flats_id)) {
                    $MetersData->set('cf_1333', $flats_id);
                }
                
                $MetersData->set('assigned_user_id', 1);
                $MetersData->set('mode', 'create');
                $MetersData->save();
                $MetersData_id = $MetersData->getId();

                if ($MetersData_id) {
                    $logger("Показание добавлено для счетчика #$number_schetchik: $indications");
                } else {
                    $logger("Ошибка при добавлении показание для счетчика #$number_schetchik: $indications");
                }
            }
        }
    } else {
        $logger("Счетчик $number_schetchik: не найден в базе данных");
    }


    // ===============================================================================================

    return true;
}

// Функция для очистки имени переменной
function sanitizeVariableName($name)
{
    // Убираем все символы кроме букв, цифр и подчеркиваний
    $name = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    // Убираем множественные подчеркивания
    $name = preg_replace('/_+/', '_', $name);
    // Убираем подчеркивания в начале и конце
    $name = trim($name, '_');
    // Если имя пустое, используем дефолтное
    if (empty($name)) {
        $name = 'column';
    }
    return $name;
}
?>