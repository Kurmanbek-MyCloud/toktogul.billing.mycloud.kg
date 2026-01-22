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

$meters = $adb->pquery("SELECT RIGHT(m.meter, 5) AS meter  
FROM vtiger_meters m 
INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
WHERE crm.deleted = 0
AND m.meter REGEXP '^[0-9]+$'", array());

$total = $adb->num_rows($meters);
$allMetersArray = [];
for ($i = 0; $i < $total; $i++) {
  $meter = $adb->query_result($meters, $i, 'meter');
  $allMetersArray[] = '2-1-' . $meter;
}

$requestArray = [];
$n_start = 0;
$allProcessedMeters = [];

for ($i = 0; $i < $total; $i++) {
  $n = $i + 1;
  $meter = $adb->query_result($meters, $i, 'meter');
  array_push($requestArray, '2-1-' . $meter);

  if ($n % 100 == 0 || $n == $total) {
    $processedBatch = send_request(json_encode($requestArray), $n_start, $n);
    $allProcessedMeters = array_merge($allProcessedMeters, $processedBatch);
    $requestArray = [];
    $n_start = $n;
  }
}

$notProcessedMeters = array_diff($allMetersArray, $allProcessedMeters);

if (!empty($notProcessedMeters)) {
  $date = date('Y-m-d H:i:s');
  $logData = sprintf(
    "[%s] Всего счетчиков: %d, Обработано: %d, Не обработано: %d. Список необработанных: %s\n",
    $date,
    count($allMetersArray),
    count($allProcessedMeters),
    count($notProcessedMeters),
    implode(', ', $notProcessedMeters)
  );
  file_put_contents('getDifferentMeters.log', $logData, FILE_APPEND);
}

// Формируем итоговый массив с результатами
$result = [
  'Всего счетчиков' => count($allMetersArray),
  'Обработано счетчиков' => count($allProcessedMeters),
  'Не обработано счетчиков' => count($notProcessedMeters),
  'Список счетчиков которые есть в биллинге но нету на сайте Элехант' => $notProcessedMeters
];
// Отправляем данные в формате JSON
echo json_encode($result);

function send_request($requestArray, $n_start, $n_current) {
  global $adb;

  $sentMeters = json_decode($requestArray);

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://cntdev.ru/api',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{"list":' . $requestArray . '}',
    CURLOPT_HTTPHEADER => array(
      'piu_vodokanaltoktogul: b0580d62-3514-4f13-bcca-0a99cedede98',
      'Authorization: Anarbaev1955@mail.ru:b0580d62-3514-4f13-bcca-0a99cedede98',
      'Content-Type: application/json'
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);

  $response = json_decode($response);

  $receivedMeters = [];
  if (isset($response->data->meters)) {
    foreach ($response->data->meters as $meter) {
      $receivedMeters[] = str_replace('.', '-', substr($meter->id, 0));
    }
  }

  // $notProcessedInBatch = array_diff($sentMeters, $receivedMeters);

  // if (!empty($notProcessedInBatch)) {
  //   $date = date('Y-m-d H:i:s');
  //   $logData = sprintf(
  //     "[%s] Batch %d-%d: Не обработаны счетчики: %s\n",
  //     $date,
  //     $n_start,
  //     $n_current,
  //     implode(', ', $notProcessedInBatch)
  //   );
  //   file_put_contents('getDifferentMeters.log', $logData, FILE_APPEND);
  // }

  return $receivedMeters;
}
