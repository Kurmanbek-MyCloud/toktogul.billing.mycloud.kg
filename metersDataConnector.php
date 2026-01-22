<?php

exit ("Не пользоваться !!!");

// // Подключение и настройки
// include_once 'includes/Loader.php';
// include_once 'include/utils/utils.php';
// include_once 'include/utils/InventoryUtils.php';
// vimport('includes.http.Request');
// vimport('includes.runtime.Globals');
// vimport('includes.runtime.BaseModel');
// vimport('includes.runtime.Controller');
// vimport('includes.runtime.LanguageHandler');
// global $adb;
// global $current_user;
// $current_user = Users::getActiveAdminUser();

// // Массив для хранения логов
// $logs = [];

// // Получаем список счётчиков
// $meters = $adb->pquery("SELECT RIGHT(m.meter, 5) AS meter  
// FROM vtiger_meters m 
// INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
// INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
// WHERE crm.deleted = 0
// AND m.meter REGEXP '^[0-9]+$' ", array());
// $total = $adb->num_rows($meters);

// if ($total > 0) {
//   echo "<!DOCTYPE html>";
//   echo "<html lang='en'>";
//   echo "<head>";
//   echo "<meta charset='UTF-8'>";
//   echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
//   echo "<title>Стягивание показаний</title>";
//   echo "<style>
//       body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
//       .header { display: flex; align-items: center; justify-content: center; position: relative; margin-bottom: 20px; }
//       .logo { width: 200px; height: auto; position: absolute; left: 0; }
//       h1 { text-align: center; font-size: 2em; margin: 0; }
//       .container { display: flex; }
//       .table-container { width: 50%; padding: 10px; }
//       table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
//       th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
//       th { background-color: #4CAF50; color: white; }
//   </style>";
//   echo "</head>";
//   echo "<body>";

//   // Логотип и заголовок
//   echo "<div class='header'>";
//   echo "<img src='test/logo/kenesh_logo_1.png' alt='Логотип' class='logo'>";
//   echo "<h1>Стягивание показаний с Элеханта</h1>";
//   echo "</div>";

//   echo "<p>Найдено <strong>$total</strong> счётчиков для обработки.</p>";

//   // Контейнер для таблиц
//   echo "<div class='container'>";

//   echo "<style>";
//   echo "th { background-color:rgb(116, 180, 120); color: white; padding: 10px; text-align: left; }";
//   echo "</style>";

//   // Основная таблица
//   echo "<div class='table-container'>";
//   echo "<h2>Обработанные счетчики</h2>";
//   echo "<table>";
//   echo "<thead><tr><th>#</th><th>Счётчик</th></tr></thead>";
//   echo "<tbody>";

//   $requestArray = [];
//   $n_start = 0;
//   $quantity_metersData = 0;

//   for ($i = 0; $i < $total; $i++) {
//     // for ($i = 0; $i < 15; $i++) {
//     $quantity_metersData++;
//     $n = $i + 1;
//     $meter = $adb->query_result($meters, $i, 'meter');
//     $logs = [];
//     $requestArray[] = '2-1-' . $meter;

//     echo "<tr>";
//     echo "<td>$n</td>";
//     echo "<td>$meter</td>";
//     echo "</tr>";

//     if ($n % 10 == 0 || $n == $total) {
//       send_request(json_encode($requestArray), $n_start, $n, $meter);
//       $requestArray = [];
//       $n_start = $n + 1;
//     }
//   }

//   echo "</tbody>";
//   echo "</table>";
//   echo "</div>"; // Закрытие контейнера для основной таблицы

//   // Таблица статусов
//   echo "<div class='table-container'>";
//   echo "<h2>Таблица логов</h2>";
//   echo "<table>";
//   echo "<thead><tr><th>Логи</th></tr></thead>";
//   echo "<tbody>";

//   // Вывод логов
//   foreach ($logs as $log) {
//     echo "<tr><td>" . htmlspecialchars($log) . "</td></tr>";
//   }

//   echo "</tbody>";
//   echo "</table>";
//   echo "</div>"; // Закрытие контейнера для таблицы статусов

//   echo "</div>"; // Закрытие контейнера для обеих таблиц
//   echo "<div class='status'>Обработка завершена для <strong>$quantity_metersData</strong> счётчиков.</div>";

//   echo "</body>";
//   echo "</html>";
// } else {
//   echo "Нет счётчиков для обработки.";
// }



// function send_request($requestArray, $n_start, $n_current, $meter) {

//   global $adb, $logs;

//   // Отправляем запрос к API
//   $curl = curl_init();
//   curl_setopt_array($curl, array(
//     CURLOPT_URL => 'https://cntdev.ru/api',
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_ENCODING => '',
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 0,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     CURLOPT_CUSTOMREQUEST => 'POST',
//     CURLOPT_POSTFIELDS => '{"list":' . $requestArray . '}',
//     CURLOPT_HTTPHEADER => array(
//       'piu_vodokanaltoktogul: b0580d62-3514-4f13-bcca-0a99cedede98',
//       'Authorization: Anarbaev1955@mail.ru:b0580d62-3514-4f13-bcca-0a99cedede98',
//       'Content-Type: application/json'
//     ),
//   ));

//   $response = curl_exec($curl);
//   curl_close($curl);

//   $response = json_decode($response);
// //   echo "<pre>";
// //   var_dump($response);
// //   echo "</pre>";
// //   exit();

//   if ($response->status == 'error') {
//     $logMessage = "Ошибка: {$response->data}";
//     logMetersDataConnector($logMessage);
//     return $logMessage;
//   }


//   if ($response->status == 'success') {
//     // logMetersDataConnector("Запрос успешно обработан для счетчика #$meter.");

//     $meters = $adb->pquery("SELECT * FROM vtiger_meters m 
//                             INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
//                             INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
//                             WHERE crm.deleted = 0
//                             AND m.meter REGEXP '^[0-9]+$'
//                             ", array());

//     $responseArray = [];
//     foreach ($response->data->meters as $meterData) {
//       $meter_no = explode('.', $meterData->id)[2]; // Получаем номер счетчика
//       $value = round($meterData->value, 2); // Показание (округенное до 2 знаков после запятой)
//       $updated_date = date("Y-m-d", $meterData->updated); // Дата обновления
//       $responseArray[$meter_no] = array('value' => $value, 'updated_date' => $updated_date);
//     }
//     // Перебираем показания из базы
//     for ($k = $n_start; $k < $n_current; $k++) {
//       $check_meter_no = ltrim($adb->query_result($meters, $k, 'meter'), '0');
//       $meter_id = $adb->query_result($meters, $k, 'metersid');
//       $house_id = $adb->query_result($meters, $k, 'cf_1319');

//       // var_dump(['check_meter_no' => $check_meter_no, 'meter_id' => $meter_id, 'house_id' => $house_id]);
//       if (array_key_exists($check_meter_no, $responseArray)) {
//         $new_value = $responseArray[$check_meter_no]['value'];
//         $new_date = $responseArray[$check_meter_no]['updated_date'];

//         // Проверка существующего показания за тот же месяц
//         $existing_data = $adb->pquery("SELECT md.metersdataid ,md.data FROM vtiger_metersdata md
//                                         INNER JOIN vtiger_metersdatacf mdcf ON md.metersdataid = mdcf.metersdataid 
//                                         INNER JOIN vtiger_crmentity vc ON md.metersdataid = vc.crmid 
//                                         WHERE vc.deleted = 0
//                                         AND mdcf.cf_1317 = ?
//                                         AND MONTH(mdcf.cf_1325) = MONTH(?) 
//                                         AND YEAR(mdcf.cf_1325) = YEAR(?)
//                                         AND mdcf.cf_1521 = 0 # Не берем те которые были использованы для счета
//                                         ORDER BY mdcf.cf_1325 DESC LIMIT 1", array($meter_id, $new_date, $new_date));
//         $last_value = $adb->query_result($existing_data, 0, 'data');
// //         var_dump('new_value', $new_value);
// //         var_dump('last_value', $last_value);
// //         exit();
//         if ($adb->num_rows($existing_data) > 0) {
//           // Показание за этот месяц уже существует, проверяем нужно ли обновить
//           $metersdataid = $adb->query_result($existing_data, 0, 'metersdataid'); // Получаем ID существующей записи

//           if ($new_value > $last_value) {
//             // Обновляем показание в vtiger_metersdata
//             $adb->pquery("UPDATE vtiger_metersdata SET data = ? WHERE metersdataid = ?", array($new_value, $metersdataid)); // Используем metersdataid для обновления

//             // Обновляем cf_1325 в vtiger_metersdatacf
//             $adb->pquery("UPDATE vtiger_metersdatacf SET cf_1325 = ? WHERE metersdataid = ?", array($new_date, $metersdataid)); // Используем metersdataid для обновления

//             logMetersDataConnector("$k Показание обновлено для счетчика #$check_meter_no: $new_value - $new_date ");
//           } else {
//             logMetersDataConnector("$k Новое показание для счетчика #$check_meter_no: Дата: $new_date - $new_value не больше существующего: $last_value. Игнорируем.");
//           }
//         } else {
//           // Если данных за этот месяц нет — проверяем, больше ли новое показание, чем последнее
//           if ($new_value > $last_value) {

//             // Добавляем новое показание
//             $MetersData = Vtiger_Record_Model::getCleanInstance("MetersData");
//             $MetersData->set('data', $new_value);
//             $MetersData->set('cf_1317', $meter_id);
//             $MetersData->set('cf_1325', $new_date);
//             $MetersData->set('cf_1333', $house_id);
//             $MetersData->set('cf_1327', 'сайт cntdev.ru');
//             $MetersData->set('assigned_user_id', 1);
//             $MetersData->set('mode', 'create');
//             $MetersData->save();
//             $MetersData_id = $MetersData->getId();
//             if ($MetersData_id) {
//               logMetersDataConnector("$k Показание добавлено для счетчика #$check_meter_no: $new_value");
//             } else {
//               logMetersDataConnector("$k Ошибка при добавлении показание для счетчика #$check_meter_no: $new_value");
//             }
//           } else {
//             logMetersDataConnector("$k Новое показание для счетчика #$check_meter_no: $new_value не больше последнего: $last_value. Игнорируем.");
//           }
//         }
//       } else {
//         logMetersDataConnector("$k Новых данных для счетчика #$check_meter_no не получено, пропускаем.");
//       }
//       // exit();
//     }
//   }
// }

// function logMetersDataConnector($message) {
//   global $logs;
//   $logFile = 'metersDataConnector.log';
//   $text = date('Y-m-d H:i:s') . ": $message\n";

//   // Запись в лог-файл
//   file_put_contents($logFile, $text, FILE_APPEND);

//   // Сохранение в локальный массив
//   $logs[] = $message;
// }

// ?>


// <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
// <script>
//   // Функция для отправки AJAX-запроса
//   function sendData() {
//     $.ajax({
//       url: 'metersDataConnector.php', // Здесь укажите путь к вашему PHP файлу
//       type: 'POST',
//       data: {
//         meterData: ' someData'
//       }, // Замените на актуальные данные
//       success: function (response) {
//         console.log('Ответ от сервера:', response);
//         // Вы може те обновить DOM или выполнить другие действия с полученными данными
//       },
//       error: function (xhr, status, error) {
//         console.error('Ошибка:', status, error);
//       }
//     });
//   }
// </script>