<?php
/**
 * Проверка получения показаний Elehant через cntdev API.
 *
 * Логи пишет в файл через CustomLogger.
 *
 * Запуск из браузера (пример):
 *   /check_elehant_api.php?auth=email:token&limit=50&onlyElehant=1
 *
 * Запуск из консоли (пример):
 *   php check_elehant_api.php --auth="email:token" --limit=50
 *
 * Параметры:
 * - auth: строка для заголовка Authorization (email:token). Если не задано, берется из env ELEHANT_AUTH.
 * - limit: сколько счетчиков проверить (по умолчанию 50)
 * - offset: смещение (по умолчанию 0)
 * - metersid: проверить только один metersid
 * - metersid_min: проверять только metersid > metersid_min
 * - onlyElehant: 1/0 (по умолчанию 1) — (устарело) ранее фильтровали по полю в vtiger_meterscf; на сервере поля может не быть
 * - keep_zeros: 1/0 (по умолчанию 0) — если 1, не срезать ведущие нули (не приводить к int)
 * - url: кастомный URL API (по умолчанию https://cntdev.ru/api?)
 */
// Скрипт может лежать как в корне сайта, так и в подпапке.
// Подбираем рабочую директорию так, чтобы существовал includes/Loader.php.
$scriptDir = __DIR__;
if (file_exists($scriptDir . '/includes/Loader.php')) {
    chdir($scriptDir);
} elseif (file_exists($scriptDir . '/../includes/Loader.php')) {
    chdir($scriptDir . '/..');
} else {
    // Оставляем текущую директорию как есть — дальше будет понятная ошибка include.
}

require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/InventoryUtils.php';
require_once 'Logger.php';

vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

date_default_timezone_set('Asia/Bishkek');

global $current_user;
global $adb;
$current_user = Users::getActiveAdminUser();

$logPath = 'api_elehant/elehant_api_check.log';
// На всякий случай создаем директорию под лог, если CustomLogger сам не создает.
@mkdir(dirname($logPath), 0755, true);
$logger = new CustomLogger($logPath);

// CLI параметры (для запуска через "php check_elehant_api.php --auth=... --limit=...")
$cli = [];
if (PHP_SAPI === 'cli') {
    $cli = getopt('', [
        'auth:',
        'limit::',
        'offset::',
        'metersid::',
        'metersid_min::',
        'onlyElehant::',
        'keep_zeros::',
        'url::',
    ]);
}

function getRawParam(string $name)
{
    global $cli;
    if (isset($_GET[$name])) {
        return $_GET[$name];
    }
    if (isset($cli[$name])) {
        return $cli[$name];
    }
    return null;
}

function getIntParam(string $name, int $default = 0): int
{
    $v = getRawParam($name);
    if ($v === null || $v === '') {
        return $default;
    }
    return (int)$v;
}

function getBoolParam(string $name, bool $default = false): bool
{
    $vRaw = getRawParam($name);
    if ($vRaw === null || $vRaw === '') {
        return $default;
    }
    $v = (string)$vRaw;
    return $v === '1' || strtolower($v) === 'true' || strtolower($v) === 'yes';
}

function safeStr($v): string
{
    if ($v === null) {
        return '';
    }
    if (is_scalar($v)) {
        return (string)$v;
    }
    return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function normalizeReading($v): ?float
{
    if ($v === null) {
        return null;
    }
    if (is_int($v) || is_float($v)) {
        return (float)$v;
    }
    $s = trim((string)$v);
    if ($s === '') {
        return null;
    }
    $s = str_replace(["\r", "\n", "\t", ' '], '', $s);
    $s = str_replace(',', '.', $s);
    $s = preg_replace('/[^0-9\.\-]/', '', $s);
    if ($s === '' || $s === '-' || $s === '.' || $s === '-.') {
        return null;
    }
    if (!is_numeric($s)) {
        return null;
    }
    return (float)$s;
}

function buildElehantKey(string $meterRaw, bool $keepZeros): array
{
    // Как в add_meter_elehant.php: берутся последние 5 символов поля meter.
    $last5 = substr($meterRaw, -5);
    if ($keepZeros) {
        $suffix = $last5;
    } else {
        // В исходном коде идет (int)RIGHT(m.meter,5), что срезает ведущие нули.
        $suffix = (string)((int)$last5);
    }
    return [$last5, '2-1-' . $suffix];
}

// По умолчанию используем ту же авторизацию, что и в add_meter_elehant.php,
// чтобы можно было запускать просто: php check_elehant_api.php
$defaultAuth = 'Anarbaev1955@mail.ru:b0580d62-3514-4f13-bcca-0a99cedede98';

$authRaw = getRawParam('auth');
$auth = $authRaw !== null && $authRaw !== '' ? (string)$authRaw : (string)getenv('ELEHANT_AUTH');
if (empty($auth)) {
    $auth = $defaultAuth;
}
$urlRaw = getRawParam('url');
$apiUrl = $urlRaw !== null && $urlRaw !== '' ? (string)$urlRaw : 'https://cntdev.ru/api?';
$limit = getIntParam('limit', 0); // 0 = без лимита (проверить все)
$limit = $limit < 0 ? 0 : $limit;
$offset = max(0, getIntParam('offset', 0));
$metersid = getIntParam('metersid', 0);
$metersidMin = getIntParam('metersid_min', 0);
$onlyElehant = getBoolParam('onlyElehant', true);
$keepZeros = getBoolParam('keep_zeros', false);

$logger->log("=== Проверка Elehant API: старт ===");
$logger->log("Параметры: limit=$limit, offset=$offset, metersid=$metersid, metersid_min=$metersidMin, onlyElehant=" . ($onlyElehant ? '1' : '0') . ", keep_zeros=" . ($keepZeros ? '1' : '0'));
$logger->log("URL API: " . $apiUrl);
$logger->log("Авторизация присутствует: " . (!empty($auth) ? 'да' : 'нет') . " (len=" . strlen((string)$auth) . ")");

if (empty($auth)) {
    $logger->log("ОШИБКА: Пустая авторизация. Передай ?auth=email:token или установи env ELEHANT_AUTH.");
    header('Content-Type: text/plain; charset=utf-8');
    echo "ОШИБКА: Пустая авторизация. Передай ?auth=email:token или установи env ELEHANT_AUTH.\n";
    exit;
}

$baseSql = "SELECT vm.metersid
            FROM vtiger_meters vm
            INNER JOIN vtiger_crmentity vc ON vc.crmid = vm.metersid
            WHERE vc.deleted = 0";
$baseParams = [];

if ($metersid > 0) {
    $baseSql .= " AND vm.metersid = ?";
    $baseParams[] = $metersid;
} else {
    if ($metersidMin > 0) {
        $baseSql .= " AND vm.metersid > ?";
        $baseParams[] = $metersidMin;
    }
}

// onlyElehant тут не применяем (на твоей базе нет cf_1491, а других маркеров у нас нет)
if ($onlyElehant) {
    $logger->log("ВНИМАНИЕ: onlyElehant=1 запрошен, но фильтрация отключена (нет поля-провайдера в базе).");
}

header('Content-Type: text/plain; charset=utf-8');
echo "Log file: $logPath\n";

// Если limit=0 — проверяем все, но выбираем metersid пачками, чтобы не умирать по памяти/таймаутам базы.
$batchSize = ($limit > 0) ? $limit : 500;
$batchOffset = $offset;
$processedMeters = 0;
$cmpMatch = 0;
$cmpMismatch = 0;
$cmpNoBilling = 0;
$cmpNoApi = 0;
$cmpApiError = 0;
$cmpNonNumeric = 0;

while (true) {
    $sql = $baseSql . " ORDER BY vm.metersid ASC LIMIT $batchOffset, $batchSize";
    $params = $baseParams;

    $logger->log("SQL: " . $sql);
    $logger->log("Параметры SQL: " . safeStr($params));

    $res = $adb->pquery($sql, $params);
    if ($res === false) {
        // В Vtiger/ADODB часто доступно через $adb->database->ErrorMsg()
        $dbErr = '';
        $dbErrNo = '';
        if (isset($adb->database) && is_object($adb->database)) {
            if (method_exists($adb->database, 'ErrorMsg')) {
                $dbErr = (string)$adb->database->ErrorMsg();
            }
            if (method_exists($adb->database, 'ErrorNo')) {
                $dbErrNo = (string)$adb->database->ErrorNo();
            }
        }
        $logger->log("ОШИБКА БД: pquery вернул false. ErrorNo=" . safeStr($dbErrNo) . " ErrorMsg=" . safeStr($dbErr));
        echo "ОШИБКА БД: запрос не выполнился.\n";
        echo "ErrorNo: " . ($dbErrNo !== '' ? $dbErrNo : '-') . "\n";
        echo "ErrorMsg: " . ($dbErr !== '' ? $dbErr : '-') . "\n";
        echo "Смотри лог: $logPath\n";
        exit;
    }

    $totalRaw = $adb->num_rows($res);
    $total = (int)$totalRaw;
    $logger->log("Выбрано счетчиков в пачке: $total (raw=" . safeStr($totalRaw) . "), offset=$batchOffset, batchSize=$batchSize");
    echo "Выбрано счетчиков в пачке: $total (offset=$batchOffset)\n";

    if ($total <= 0) {
        break;
    }

    for ($i = 0; $i < $total; $i++) {
        $mid = (int)$adb->query_result($res, $i, 'metersid');

    // 2) Детали счетчика и последнее показание как в add_meter_elehant.php
    $meters = $adb->run_query_allrecords("SELECT RIGHT(m.meter, 5) AS meter,
        vtm.data AS meter_data,
        vmcf.cf_1325 AS date_add_meter,
        vmcf.metersdataid AS id_meter,
        vmcf.cf_1333 AS house_id
        FROM vtiger_meters m
        INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
        INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
        INNER JOIN vtiger_metersdatacf vmcf ON vmcf.cf_1317 = m.metersid
        INNER JOIN vtiger_metersdata vtm ON vtm.metersdataid = vmcf.metersdataid
        WHERE vmcf.metersdataid = (SELECT MAX(metersdataid)
            FROM vtiger_metersdatacf cf
            INNER JOIN vtiger_crmentity crmm ON crmm.crmid = cf.metersdataid
            WHERE cf_1317 = $mid AND crmm.deleted = 0)
        AND m.metersid = $mid AND crm.deleted = 0");

    // Если показаний нет — берем только номер счетчика (как в оригинале)
    if (!is_array($meters) || empty($meters) || $meters[0] == null) {
        $meter_sql = $adb->run_query_allrecords("SELECT RIGHT(m.meter, 5) AS meter, m.meter AS meter_full
            FROM vtiger_meters m
            INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
            INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
            AND m.metersid = $mid AND crm.deleted = 0");

        $meterLast5 = isset($meter_sql[0]['meter']) ? (string)$meter_sql[0]['meter'] : '';
        $meterFull = isset($meter_sql[0]['meter_full']) ? (string)$meter_sql[0]['meter_full'] : '';
        $meterData = '';
        $dateAdd = '';
        $houseId = '';
        $hasLastReading = 0;
    } else {
        $meterLast5 = (string)$meters[0]['meter'];
        $meterFull = ''; // В этой выборке полного meter нет
        $meterData = (string)$meters[0]['meter_data'];
        $dateAdd = (string)$meters[0]['date_add_meter'];
        $houseId = (string)$meters[0]['house_id'];
        $hasLastReading = 1;
    }

    if ($meterLast5 === '') {
        $logger->log("metersid=$mid => ПРОПУСК (пустой номер счетчика)");
        continue;
    }

    // Ключ строим из последних 5 цифр (как в add_meter_elehant.php: (int)RIGHT(...,5) может срезать нули)
    $last5 = $meterLast5;
    $suffix = $keepZeros ? $last5 : (string)((int)$last5);
    $key = '2-1-' . $suffix;
    $payload = json_encode(['list' => [$key]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $logger->log("---- metersid=$mid last5='$last5' ключ='$key' есть_последнее_показание=$hasLastReading показание_в_биллинге='$meterData' дата_в_биллинге='$dateAdd' house_id='$houseId' meter_full='$meterFull' ----");

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $auth,
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    $curlErrNo = curl_errno($curl);
    $curlErr = curl_error($curl);
    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curlErrNo) {
        $logger->log("HTTP=$httpCode ОШИБКА_CURL($curlErrNo): $curlErr");
        continue;
    }

    $logger->log("HTTP=$httpCode ответ_сыро=" . safeStr($response));

    $decoded = json_decode((string)$response, true);
    if (!is_array($decoded)) {
        $logger->log("ОШИБКА_JSON: " . json_last_error_msg());
        $cmpApiError++;
        continue;
    }

    $status = $decoded['status'] ?? null;
    $count = $decoded['data']['count'] ?? null;
    $metersArr = $decoded['data']['meters'] ?? null;

    $value = null;
    $updated = null;
    if (is_array($metersArr) && isset($metersArr[0]) && is_array($metersArr[0])) {
        $value = $metersArr[0]['value'] ?? null;
        $updated = $metersArr[0]['updated'] ?? null;
    }

    $apiUpdatedHuman = '';
    if (is_numeric($updated)) {
        $apiUpdatedHuman = date('Y-m-d H:i:s', (int)$updated);
    } elseif (is_string($updated) && trim($updated) !== '') {
        $apiUpdatedHuman = trim($updated);
    }

    $logger->log("распарсено: status=" . safeStr($status) . " count=" . safeStr($count) . " value=" . safeStr($value) . " updated=" . safeStr($updated) . ($apiUpdatedHuman !== '' ? " ($apiUpdatedHuman)" : ''));

    // СРАВНЕНИЕ: ничего не записываем/не перезаписываем, только сравниваем и логируем.
    if ($status !== 'success') {
        $cmpApiError++;
        $logger->log("СРАВНЕНИЕ: ОШИБКА_API status=" . safeStr($status) . " data=" . safeStr($decoded['data'] ?? null));
    } else {
        $apiNum = normalizeReading($value);
        $billingNum = normalizeReading($meterData);

        if ($apiNum === null) {
            $cmpNoApi++;
            $logger->log("СРАВНЕНИЕ: НЕТ_ПОКАЗАНИЯ_В_API api_value_raw=" . safeStr($value));
        } elseif ($billingNum === null) {
            if ($meterData === '' || $meterData === null) {
                $cmpNoBilling++;
                $logger->log("СРАВНЕНИЕ: НЕТ_ПОКАЗАНИЯ_В_БИЛЛИНГЕ billing_value_raw=" . safeStr($meterData) . " api=" . safeStr($apiNum));
            } else {
                $cmpNonNumeric++;
                $logger->log("СРАВНЕНИЕ: ПОКАЗАНИЕ_В_БИЛЛИНГЕ_НЕ_ЧИСЛО billing_value_raw=" . safeStr($meterData) . " api=" . safeStr($apiNum));
            }
        } else {
            $delta = $apiNum - $billingNum;
            $eps = 0.0001;
            if (abs($delta) < $eps) {
                $cmpMatch++;
                $logger->log("СРАВНЕНИЕ: СОВПАЛО биллинг=" . safeStr($billingNum) . " api=" . safeStr($apiNum) . " разница=" . safeStr($delta));
            } else {
                $cmpMismatch++;
                $direction = $delta > 0 ? 'API_БОЛЬШЕ_БИЛЛИНГА' : 'API_МЕНЬШЕ_БИЛЛИНГА';
                $logger->log("СРАВНЕНИЕ: НЕ_СОВПАЛО ($direction) биллинг=" . safeStr($billingNum) . " api=" . safeStr($apiNum) . " разница=" . safeStr($delta));
            }
        }
    }
        $processedMeters++;
    }

    // Если пользователь задал конкретный limit — выполняем один батч и выходим.
    if ($limit > 0) {
        break;
    }

    $batchOffset += $batchSize;
}

$logger->log("=== Проверка Elehant API: завершено ===");
$logger->log("ИТОГО: обработано=$processedMeters совпало=$cmpMatch расхождение=$cmpMismatch нет_в_биллинге=$cmpNoBilling нет_в_api=$cmpNoApi ошибок_api=$cmpApiError не_число_в_биллинге=$cmpNonNumeric");
echo "Обработано счетчиков: $processedMeters\n";
echo "Итого: совпало=$cmpMatch расхождение=$cmpMismatch нет_в_биллинге=$cmpNoBilling нет_в_api=$cmpNoApi ошибок_api=$cmpApiError не_число_в_биллинге=$cmpNonNumeric\n";
echo "Done.\n";

