<?php

// Делал Курманбек. Если будут вопросы пишите мне! (17.01.2026)
// Логику не трогать !!!

var_dump('Test - 1');

require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';
require_once 'Logger.php';
var_dump('Test - 1');

vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

$logger = new CustomLogger('createInvoice_new.log');
global $current_user;
$current_user = Users::getActiveAdminUser();
global $adb;

$deactivated_logs = []; // Логи для деактивированных домов
$without_service_logs = []; // Логи для домов без услуг
$without_meters_logs = []; // Логи для домов без счетчиков


// $res = $adb->pquery("SELECT DISTINCT c.contactid, fcf.flatsid, fcf.cf_1448, fcf.cf_1261, f.flat, fcf.cf_1420, fcf.cf_1454  
//                         FROM vtiger_flats f
//                         INNER JOIN vtiger_flatscf fcf ON fcf.flatsid = f.flatsid
//                         INNER JOIN vtiger_crmentity crm ON crm.crmid = f.flatsid
//                         LEFT JOIN vtiger_contactdetails c ON c.contactid = fcf.cf_1235
//                         LEFT JOIN vtiger_contactscf cf ON cf.contactid = c.contactid
//                         LEFT JOIN vtiger_crmentity ccrm on cf.contactid = ccrm.crmid
//                         WHERE crm.deleted = 0 
//                         AND (ccrm.deleted = 0 OR ccrm.deleted IS NULL)
//                         ORDER BY fcf.cf_1448 ", array()); // Поиск данных по улицам


$res = $adb->pquery("SELECT DISTINCT c.contactid, fcf.flatsid, fcf.cf_1448, fcf.cf_1261, f.flat, fcf.cf_1420, fcf.cf_1454  
                        FROM vtiger_flats f
                        INNER JOIN vtiger_flatscf fcf ON fcf.flatsid = f.flatsid
                        INNER JOIN vtiger_crmentity crm ON crm.crmid = f.flatsid
                        LEFT JOIN vtiger_contactdetails c ON c.contactid = fcf.cf_1235
                        LEFT JOIN vtiger_contactscf cf ON cf.contactid = c.contactid
                        LEFT JOIN vtiger_crmentity ccrm on cf.contactid = ccrm.crmid
                        WHERE crm.deleted = 0 AND fcf.flatsid = 135593
                        AND (ccrm.deleted = 0 OR ccrm.deleted IS NULL)
                        ORDER BY fcf.cf_1448 ", array()); // Поиск данных по улицам



$due_date = new DateTime();
$period = new DateTime();
$period->modify("-2 month"); // Если нужно сгенерировать за конкретный месяц, просто сминусуйте!!! 
$invoice_period = clone $period; // Сохраняем период счета для использования в функции
$date = $period->format('Y-m-d');
$firstDay = new DateTime(date('Y-m') . '-04-01');
$fifteenthDay = new DateTime(date('Y-m') . '-04-15');

$translate_month = array(
    'Jan' => 'Январь',
    'Feb' => 'Февраль',
    'Mar' => 'Март',
    'Apr' => 'Апрель',
    'May' => 'Май',
    'Jun' => 'Июнь',
    'Jul' => 'Июль',
    'Aug' => 'Август',
    'Sep' => 'Сентябрь',
    'Oct' => 'Октябрь',
    'Nov' => 'Ноябрь',
    'Dec' => 'Декабрь'
);

$theme = "За " . $translate_month[$invoice_period->format("M")] . " " . $invoice_period->format("Y") . " года";
// var_dump($theme);
// exit();
// $theme = "За Август 2025 г";

// $theme = "Тестовый счет";

// Изменяем дату на предыдущий месяц для поиска предыдущего счета
$period->modify('previous month');
// Получаем аббревиатуру предыдущего месяца
$previous_month_abbr = $period->format('M');
// Переводим аббревиатуру в название месяца
$previous_month_name = "За " . $translate_month[$previous_month_abbr] . " " . $period->format("Y") . " года";

// $logger->log("Генерация счетов для улицы: $currentStreet ");
// var_dump($adb->num_rows($res));

for ($i = 0; $i < $adb->num_rows($res); $i++) {
// for ($i = 0; $i < 1; $i++) {
    $contactid = $adb->query_result($res, $i, 'contactid');
    $flatid = $adb->query_result($res, $i, 'flatsid');
    $quantity = $adb->query_result($res, $i, 'cf_1261');
    $number_flat = $adb->query_result($res, $i, 'flat');
    $ls = $adb->query_result($res, $i, 'cf_1420');
    $currentStreet = $adb->query_result($res, $i, 'cf_1448');

    var_dump("$i Обработка с ID - $flatid \n");


    $deactivated = $adb->query_result($res, $i, 'cf_1454');

    if ($deactivated) { // Если деактивирован, не создается счет
        $deactivated_logs[] = "#$i! ЛС: $ls, Улица: $currentStreet, Дом: $number_flat  - Деактивирован ";
        continue;
    }


    $services_data = $adb->pquery("SELECT DISTINCT s.serviceid, s.unit_price, scf.cf_1297, vp.taxpercentage 
                FROM vtiger_crmentityrel rel
                INNER JOIN vtiger_service s ON s.serviceid = rel.relcrmid 
                INNER JOIN vtiger_producttaxrel vp ON vp.productid = rel.relcrmid
                INNER JOIN vtiger_servicecf scf ON scf.serviceid = s.serviceid
                INNER JOIN vtiger_crmentity crm ON crm.crmid = s.serviceid
                WHERE rel.relmodule = 'Services'
                AND crm.deleted = 0
                AND rel.crmid = ?", array($flatid));

    if ($adb->num_rows($services_data) > 0) {
        // Проверка на существование счета для этого дома с такой же темой (периодом)
        $existing_invoice = $adb->pquery("SELECT vi.invoiceid 
                                          FROM vtiger_invoice vi
                                          INNER JOIN vtiger_invoicecf vicf ON vi.invoiceid = vicf.invoiceid
                                          INNER JOIN vtiger_crmentity vc ON vi.invoiceid = vc.crmid
                                          WHERE vc.deleted = 0
                                          AND vi.subject = ?
                                          AND vicf.cf_1265 = ?
                                          LIMIT 1", array($theme, $flatid));
        
        if ($adb->num_rows($existing_invoice) > 0) {
            // Счет уже существует, пропускаем создание
            $existing_invoice_id = $adb->query_result($existing_invoice, 0, 'invoiceid');
            $logger->log("#$i Счет уже существует! Пропуск создания. ID существующего счета: $existing_invoice_id, ID Дома: $flatid, Улица: $currentStreet, Тема: $theme");
            continue;
        }
        
        $invoice = Vtiger_Record_Model::getCleanInstance("Invoice");
        $invoice->set('contact_id', $contactid);
        $invoice->set('cf_1265', $flatid);
//    $invoice->set('cf_1261', $number_of_residents);
        $invoice->set('subject', $theme);
        // $invoice->set('cf_1490', $type_flat);
        // $invoice->set('cf_1492', $number_flat);
        $invoice->set('duedate', $due_date->format('Y-m-10'));
        $invoice->set('invoicestatus', 'AutoCreated');
        $invoice->set('assigned_user_id', 1);
        $invoice->set('currency_id', 1);
        $invoice->set('mode', 'create');

        $invoice->save();

        $invoice_id = $invoice->getId();

        // $one_interations = TRUE;
        $metersdataid = null; // Инициализация переменной для показаний счетчиков
        $has_missing_readings = false; // Флаг отсутствия показаний за нужный месяц
        if ($invoice_id != null) {
            for ($j = 0; $j < $adb->num_rows($services_data); $j++) {

                $service_id = $adb->query_result($services_data, $j, 'serviceid');
                $listprice = $adb->query_result($services_data, $j, 'unit_price');
                $accrual_base = $adb->query_result($services_data, $j, 'cf_1297');
                $tax_percent = $adb->query_result($services_data, $j, 'taxpercentage');

                // if ($one_interations == true) {
                //   $total_all_penalty = add_penalty($adb, $current_user, $flatid, $previous_month_name, $invoice_id);
                //   $one_interations = false;
                // }

                if ($accrual_base == 'Количество проживающих') { // Дворовой

                    if ($service_id == 77958) {
                        if ($quantity <= 1) {
                            $listprice = $listprice / 12;
                            $quantity = 1;
                        } else {
                            if ($quantity == 0 || $quantity == null) {
                                $quantity = 1;
                            }
                            $add_price = 355.66;
                            $listprice = (($listprice + ($add_price * ($quantity - 1))) / 12) / $quantity;
                        }
                    }
                    add_service_to_invoice($invoice_id, $service_id, 0, 0, $listprice, $accrual_base, $quantity, $tax_percent);
                }
                if ($accrual_base == 'Счетчик') {
                    // Сохраняем последний metersdataid, если есть несколько счетчиков
                    $current_metersdataid = add_meters_to_service($invoice_id, $service_id, $flatid, $listprice, $accrual_base, $tax_percent, $currentStreet, $number_flat, $ls, $invoice_period);
                    if ($current_metersdataid != null) {
                        $metersdataid = $current_metersdataid;
                    } else {
                        // Если показаний нет за нужный месяц, отмечаем флаг
                        $has_missing_readings = true;
                        break; // Прерываем цикл услуг
                    }
                }
            }
            
            // Если есть проблемы с показаниями счетчиков, удаляем счет
            if ($has_missing_readings && $invoice_id != null) {
                try {
                    $invoice_to_delete = Vtiger_Record_Model::getInstanceById($invoice_id, "Invoice");
                    $invoice_to_delete->delete();
                    $invoice_id = null; // Обнуляем ID, чтобы не логировать успех
                } catch (Exception $e) {
                    // Игнорируем ошибки удаления
                }
            } else {
                // Обновляем долг только если счет не удален
                update_flat_debt_by_flatid($flatid);
            }

            // Флаг использования показаний обновляется внутри функции add_meters_to_service для каждого счетчика
            // $sql = "UPDATE vtiger_crmentity SET createdtime = '2023-12-11' WHERE crmid = ?";
            // $adb->pquery($sql, array($invoice_id));

        }
        if ($has_missing_readings) {
            // Уже залогировано в функции add_meters_to_service
        } else {
            $invoice_id != null ? $logger->log("#$i Счет успешно создан ID: $invoice_id ID Дома: $flatid Улица: $currentStreet ") : $logger->log("#$i! Ошибка при создании Счета ! ID Дома: $flatid ");
        }
    } else {
        $without_service_logs[] = "У данного дома нету услуги! ЛС: $ls, Улица: $currentStreet, Дом: $number_flat ID дома: $flatid";
    }
    // exit();
}

// Вывод всех логов для деактивированных домов и домов без услуг в конце
foreach ($deactivated_logs as $log) {
    $logger->log($log);
}
foreach ($without_service_logs as $log) {
    $logger->log($log);
}
foreach ($without_meters_logs as $log) {
    $logger->log($log);
}
exit();

function add_meters_to_service($invoice_id, $service_id, $flatid, $listprice, $accrual_base, $tax_percent, $currentStreet, $number_flat, $ls, $period)
{
    global $adb, $without_meters_logs, $logger;
    
    // Определяем период счета (первый и последний день месяца)
    $period_start = new DateTime($period->format('Y-m-01')); // Первый день месяца
    $period_end = new DateTime($period->format('Y-m-t')); // Последний день месяца
    $period_start_str = $period_start->format('Y-m-d');
    $period_end_str = $period_end->format('Y-m-d');
    $period_month = $period->format('Y-m'); // Для SQL запросов (формат: 2025-12)
    $period_year = (int)$period->format('Y'); // Год для SQL запроса
    $period_month_num = (int)$period->format('m'); // Месяц для SQL запроса (1-12)
    
    // Определяем следующий месяц для поиска предыдущего показания
    $next_month = clone $period;
    $next_month->modify('+1 month');
    $next_month_year = (int)$next_month->format('Y');
    $next_month_num = (int)$next_month->format('m');
    
    // Для читаемого формата периода в логах
    $translate_month = array(
        'Jan' => 'Январь', 'Feb' => 'Февраль', 'Mar' => 'Март', 'Apr' => 'Апрель',
        'May' => 'Май', 'Jun' => 'Июнь', 'Jul' => 'Июль', 'Aug' => 'Август',
        'Sep' => 'Сентябрь', 'Oct' => 'Октябрь', 'Nov' => 'Ноябрь', 'Dec' => 'Декабрь'
    );
    $period_readable = $translate_month[$period->format('M')] . ' ' . $period->format('Y');
    $meters = $adb->pquery("SELECT * FROM vtiger_meterscf vm 
                              INNER JOIN vtiger_crmentity vc ON vm.metersid = vc.crmid 
                              WHERE vc.deleted = 0
                              AND vm.cf_1319 = ?", array($flatid));
    $metersdataid = null; // Инициализация переменной
    if ($adb->num_rows($meters) > 0) {
        for ($k = 0; $k < $adb->num_rows($meters); $k++) {
            $metersid = $adb->query_result($meters, $k, 'metersid');
            $well = $adb->query_result($meters, $k, 'cf_1462');
            $accrual_base = $accrual_base . ' ' . $well;

            // Получаем ПРЕДЫДУЩЕЕ показание из биллинга ЗА НУЖНЫЙ МЕСЯЦ (период счета)
            // Например, для счета за декабрь - это показание за декабрь
            $prev_meter_data = $adb->pquery("SELECT md.data, md.metersdataid, mdcf.cf_1325 as reading_date
                                FROM vtiger_metersdata md 
                                INNER JOIN vtiger_metersdatacf mdcf ON mdcf.metersdataid = md.metersdataid
                                INNER JOIN vtiger_crmentity crm ON crm.crmid = md.metersdataid
                                WHERE mdcf.cf_1317 = ? # id счетчика
                                AND crm.deleted = 0 
                                AND YEAR(mdcf.cf_1325) = ? # год периода
                                AND MONTH(mdcf.cf_1325) = ? # месяц периода
                                ORDER BY mdcf.cf_1325 desc, crm.createdtime DESC
                                LIMIT 1", array($metersid, $period_year, $period_month_num));

            // Проверка наличия данных за нужный месяц
            if ($adb->num_rows($prev_meter_data) > 0) {
                $prev_md_raw = $adb->query_result($prev_meter_data, 0, 'data');
                $metersdataid = $adb->query_result($prev_meter_data, 0, 'metersdataid');
                $prev_reading_date = $adb->query_result($prev_meter_data, 0, 'reading_date');
                
                $prev_md = ($prev_md_raw !== null && $prev_md_raw !== '') ? floatval($prev_md_raw) : null;
                
                // Ищем ТЕКУЩЕЕ показание в биллинге (за СЛЕДУЮЩИЙ месяц после периода счета)
                // Например, для счета за декабрь ищем показание за январь следующего года
                $current_from_next_month = false;
                $current_md = null;
                $current_md_raw = null;
                
                // Ищем показание за следующий месяц (это и есть "текущее" для расчета расхода)
                $current_meter_data = $adb->pquery("SELECT md.`data` as current_reading, md.metersdataid, mdcf.cf_1325 as current_reading_date
                              FROM vtiger_metersdata md
                              INNER JOIN vtiger_metersdatacf mdcf ON md.metersdataid = mdcf.metersdataid 
                              INNER JOIN vtiger_crmentity vc ON vc.crmid = md.metersdataid 
                              WHERE vc.deleted = 0
                              AND mdcf.cf_1317 = ? # id счетчика
                              AND mdcf.cf_1333 = ? # id дома
                              AND YEAR(mdcf.cf_1325) = ? # год следующего месяца
                              AND MONTH(mdcf.cf_1325) = ? # месяц следующий месяц
                              ORDER BY mdcf.cf_1325 ASC, vc.createdtime ASC
                              LIMIT 1", array($metersid, $flatid, $next_month_year, $next_month_num));
                
                $current_reading_date = null; // Дата текущего показания для логирования
                $current_metersdataid = null; // ID текущего показания
                if ($adb->num_rows($current_meter_data) > 0) {
                    $current_md_raw = $adb->query_result($current_meter_data, 0, 'current_reading');
                    $current_md = ($current_md_raw !== null && $current_md_raw !== '') ? floatval($current_md_raw) : null;
                    $current_reading_date = $adb->query_result($current_meter_data, 0, 'current_reading_date');
                    $current_metersdataid = $adb->query_result($current_meter_data, 0, 'metersdataid'); // Получаем ID текущего показания
                    $current_from_next_month = true;
                } else {
                    // Если не найдено показание за следующий месяц, счет не создаем
                    $next_month_readable = $translate_month[$next_month->format('M')] . ' ' . $next_month->format('Y');
                    if (isset($logger)) {
                        $logger->log("Счет НЕ создан - нет показания за $next_month_readable! ЛС: $ls, Улица: $currentStreet, Дом: $number_flat, ID счетчика: $metersid, Колодец: $well, Период: $period_readable, Предыдущее показание (за период): $prev_md (дата: $prev_reading_date)");
                    }
                    return null; // Возвращаем null, чтобы указать, что счет не создан
                }
                
                // Обработка предыдущего показания
                if ($prev_md === null) {
                    $prev_md = 0;
                }
                
                // Расчет расхода: текущее (за следующий месяц) - предыдущее (за период)
                // Например, для декабря: показание за январь - показание за декабрь = расход за декабрь
                if ($current_md === null || $current_md == 0) {
                    $quantity = 0;
                } else if ($current_md <= $prev_md) {
                    // Если текущее показание меньше или равно предыдущему, считаем разницу 0
                    $quantity = 0;
                } else {
                    // Расход = текущее показание - предыдущее показание
                    $quantity = $current_md - $prev_md;
                }
                
                // Логирование показаний счетчика
                if (isset($logger)) {
                    $next_month_readable = $translate_month[$next_month->format('M')] . ' ' . $next_month->format('Y');
                    $current_source = $current_from_next_month ? " (взято из биллинга за $next_month_readable)" : "";
                    $current_date_info = "";
                    if ($current_reading_date) {
                        $current_date_info = " (дата: $current_reading_date)";
                    }
                    $logger->log("Показания счетчика - ЛС: $ls, Улица: $currentStreet, Дом: $number_flat, ID счетчика: $metersid, Колодец: $well | Период: $period_readable ($period_year-$period_month_num) | Предыдущее (за период): $prev_md (дата: $prev_reading_date), Текущее (за $next_month_readable): $current_md$current_date_info$current_source, Разница: $quantity, ID показания: $metersdataid");
                }
                
                add_service_to_invoice($invoice_id, $service_id, $prev_md, $current_md, $listprice, $accrual_base, $quantity, $tax_percent, $metersdataid, $current_metersdataid);

                // Обновляем флаг использования показаний для каждого счетчика
                if ($metersdataid != null) {
                    try {
                        $meterDataInstance = Vtiger_Record_Model::getInstanceById($metersdataid, "MetersData");
                        $meterDataInstance->set('cf_1521', true);
                        $meterDataInstance->set('mode', 'edit');
                        $meterDataInstance->save();
                    } catch (Exception $e) {
                        // Логируем ошибку, но продолжаем работу
                        global $logger;
                        if (isset($logger)) {
                            $logger->log("Ошибка при обновлении флага показаний metersdataid: $metersdataid - " . $e->getMessage());
                        }
                    }
                }
            } else {
                // Если нет показаний за нужный месяц, логируем и возвращаем null
                if (isset($logger)) {
                    $logger->log("Счет НЕ создан - нет показаний за период $period_readable ($period_month)! ЛС: $ls, Улица: $currentStreet, Дом: $number_flat, ID счетчика: $metersid, Колодец: $well");
                }
                // Не добавляем в массив, так как уже залогировано выше
                return null; // Возвращаем null, чтобы указать, что показаний нет
            }
        }
        return $metersdataid;
    } else {
        $without_meters_logs[] = "#У данного дома нету счетчика! ЛС: $ls, Улица: $currentStreet, Дом: $number_flat ";
        return null;
    }
}


function add_service_to_invoice($invoice_id, $service_id, $prev_md, $current_md, $listprice, $accrual_base, $quantity, $tax_percent, $prev_reading_id = null, $current_reading_id = null)
{
    global $adb, $logger;

    $margin_without_tax = ($listprice * $quantity);  // Сумма БЕЗ налога (база)

    // Расчёт налога (налог включён в итоговую сумму)
    // 1. Итого = база × (1 + процент/100) - добавляем налог к базе
    // 2. Налог = итого × процент / (100 + процент) - формула "налог включён"
    $tax_amount = 0;
    if ($tax_percent > 0) {
        $margin = $margin_without_tax * (1 + $tax_percent / 100);  // Итого С налогом
        $tax_amount = $margin * $tax_percent / (100 + $tax_percent);  // Налог включён
    } else {
        $margin = $margin_without_tax;
    }

    // Логирование налога
    if (isset($logger)) {
        $logger->log("НАЛОГ: Invoice ID: $invoice_id, Service ID: $service_id, База: $accrual_base, Сумма без налога: " . round($margin_without_tax, 2) . ", Налог $tax_percent%: " . round($tax_amount, 2) . ", Итого с налогом: " . round($margin, 2));
    }

    // Добавлены поля previous_reading_id и current_reading_id для связи с MetersData (26.01.2026)
    $sql = "INSERT INTO vtiger_inventoryproductrel(id, productid, quantity, listprice, margin, accrual_base, previous_reading, current_reading, tax1, previous_reading_id, current_reading_id) VALUES(?,?,?,?,?,?,?,?,?,?,?)";

    $params = array($invoice_id, $service_id, $quantity, $listprice, $margin, $accrual_base, $prev_md, $current_md, $tax_percent, $prev_reading_id, $current_reading_id);
    $adb->pquery($sql, $params);
    // echo $listprice."- listprice<br>";
    // echo $quantity."- quantity<br>";
    // echo $margin."- total<br>";
    $total = get_total_sum_by_service($invoice_id);
    // echo $total."-grand total<br>";
    if ($total) {
        update_invoice_total_field($total, $invoice_id);
    }

    return $tax_amount; // Возвращаем сумму налога для возможного использования
}

function update_invoice_total_field($total, $invoiceid)
{
    global $adb;
    $sql = "UPDATE vtiger_invoice SET total=?, balance=?, subtotal=?, pre_tax_total=?, taxtype=? WHERE invoiceid=?";
    $adb->pquery($sql, array($total, $total, $total, $total, 'group_tax_inc', $invoiceid));
}

function get_total_sum_by_service($invoiceid)
{
    global $adb;
    $sql = "SELECT SUM(margin) AS total FROM vtiger_inventoryproductrel WHERE id=?";
    $result = $adb->pquery($sql, array($invoiceid));
    $total = $adb->query_result($result, 0, 'total');
    return $total;
}

function update_flat_debt_by_flatid($flatid)
{
    global $adb;

    $adb->pquery("UPDATE vtiger_flatscf fcf
                SET fcf.cf_1289 = 
                    ((SELECT IFNULL((SELECT ROUND(SUM(total), 3) AS summ 
                                    FROM vtiger_invoice AS I
                                    INNER JOIN vtiger_invoicecf AS ICF ON ICF.invoiceid = I.invoiceid
                                    INNER JOIN vtiger_crmentity AS CE ON I.invoiceid = CE.crmid
                                    WHERE deleted = 0
                                        AND invoicestatus NOT IN ('Cancel')
                                        AND ICF.cf_1265 = fcf.flatsid), 0)) 
                        -
                        (SELECT IFNULL((SELECT ROUND(SUM(amount), 3) AS summ 
                                    FROM sp_payments AS SP
                                    INNER JOIN sp_paymentscf AS SPCF ON SPCF.payid = SP.payid
                                    INNER JOIN vtiger_crmentity AS SCE ON SP.payid = SCE.crmid 
                                    WHERE SCE.deleted = 0
                                        AND pay_type = 'Receipt'
                                        AND SPCF.cf_1416 = fcf.flatsid), 0))
                        +
                        (SELECT IFNULL((SELECT ROUND(SUM(penalty_amount), 3) AS summ 
                                    FROM vtiger_penalty vp 
                                    INNER JOIN vtiger_invoicecf vi ON vp.cf_to_ivoice = vi.invoiceid
                                    INNER JOIN vtiger_crmentity vc ON vp.penaltyid = vc.crmid
                                    WHERE vc.deleted = 0
                                        AND vi.cf_1265 = fcf.flatsid), 0)))
                WHERE fcf.flatsid = ?", array($flatid));
}

function add_penalty($adb, $current_user, $flatid, $previous_month_name, $invoice_id)
{
    $penalty_info = $adb->run_query_allrecords("select fax, website, code from vtiger_organizationdetails");
    $penalty_percent = (float)$penalty_info[0]['fax'];
    $penalty_start = (int)$penalty_info[0]['code'];

    $present_day = new DateTime();
    $present_day->modify('first day of last month');
    $first_day_of_last_month = $present_day->format('Y-m-d'); // Получить первый день прошлого месяца
    $last_day_of_last_month = $present_day->format('Y-m-t'); // Получить последний день прошлого месяца
    $start_day_of_last_month = $present_day->format('Y-m-' . $penalty_start); // Получить день начала начисления пени прошлого месяца

    $period = new DateTime();
    $date = $period->format('Y-m-d');
    $firstDay = new DateTime(date('Y-m') . '-04-01');
    $fifteenthDay = new DateTime(date('Y-m') . '-04-15');

    // Используем параметризованный запрос для безопасности
    $invoices_result = $adb->pquery("SELECT * FROM vtiger_invoice vi 
        INNER JOIN vtiger_crmentity vc ON vc.crmid = vi.invoiceid
        INNER JOIN vtiger_invoicecf vicf ON vi.invoiceid = vicf.invoiceid 
        WHERE vc.deleted = 0
        AND (
            vi.subject = ?
            OR vi.invoiceid = (
                SELECT invoiceid FROM vtiger_invoicecf i
                INNER JOIN vtiger_crmentity vc2 ON i.invoiceid = vc2.crmid
                WHERE vc2.deleted = 0
                AND i.cf_1265 = ?
                ORDER BY vc2.createdtime DESC 
                LIMIT 1 OFFSET 1
            )
        )
        AND vicf.cf_1265 = ?", array($previous_month_name, $flatid, $flatid));
    
    // Преобразуем результат в массив для совместимости
    $invoices = array();
    if ($invoices_result) {
        for ($idx = 0; $idx < $adb->num_rows($invoices_result); $idx++) {
            $row = array();
            $row['invoiceid'] = $adb->query_result($invoices_result, $idx, 'invoiceid');
            $row['total'] = $adb->query_result($invoices_result, $idx, 'total');
            $invoices[] = $row;
        }
    }

    $total = 0;
    $total_all_penalty = 0;
    foreach ($invoices as $invoice) {
        $last_invoiceid = $invoice['invoiceid'];
        $last_invoice_amount = $invoice['total'];
        $invoice_total_result = $adb->pquery("SELECT IFNULL(SUM(round(total,2)),0) as total_sum FROM vtiger_invoice AS di 
            INNER JOIN vtiger_invoicecf AS dicf ON dicf.invoiceid = di.invoiceid
            INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = di.invoiceid
            WHERE deleted = 0 AND dicf.cf_1265 = ?", array($flatid));
        $invoice_total = $adb->query_result($invoice_total_result, 0, 'total_sum');

        $pay_total_result = $adb->pquery("SELECT IFNULL (SUM(round(amount,2)),0) as total_sum FROM sp_payments AS p
            INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
            INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
            WHERE deleted = 0 AND pcf.cf_1416 = ?
            AND pcrm.createdtime <= ?", array($flatid, $first_day_of_last_month));
        $pay_total = $adb->query_result($pay_total_result, 0, 'total_sum');

        $penalty_total_result = $adb->pquery("SELECT sum(vp.penalty_amount) as total_sum FROM vtiger_flats vf 
            inner join vtiger_invoicecf vi on vi.cf_1265 = vf.flatsid 
            inner join vtiger_penalty vp on vp.cf_to_ivoice = vi.invoiceid 
            inner join vtiger_crmentity vc on vc.crmid = vp.penaltyid 
            where vf.flatsid = ? and vc.deleted = 0", array($flatid));
        $penalty_total = $adb->query_result($penalty_total_result, 0, 'total_sum');
        
        // Тут мы берем общий тотал на момент первого дня прошлого месяца без учета суммы нового счета
        $total = (float)$invoice_total - (float)$pay_total + (float)$penalty_total - $last_invoice_amount;

        if ($period >= $firstDay && $period <= $fifteenthDay) {
            $watering = $adb->pquery("SELECT accrual_base, listprice FROM vtiger_inventoryproductrel vi
                                WHERE accrual_base = 'Полив'
                                AND id = ?", array($flatid));

            if ($adb->num_rows($watering) > 0) {
                $watering_amount = $adb->query_result($watering, 0, 'listprice');
                $total -= $watering_amount;
            }
        }
        $description = [];
        if ($total > 0) {
            $total_between_first_and_start_result = $adb->pquery("SELECT IFNULL (SUM(round(amount,2)),0) as total_sum FROM sp_payments sp 
                                INNER JOIN sp_paymentscf sp2 ON sp.payid = sp2.payid 
                                INNER JOIN vtiger_crmentity vc ON vc.crmid = sp.payid 
                                WHERE vc.deleted = 0
                                AND sp2.cf_1416 = ?
                                AND vc.createdtime BETWEEN ? and ?", array($flatid, $first_day_of_last_month, $start_day_of_last_month));
            $total_between_first_and_start = $adb->query_result($total_between_first_and_start_result, 0, 'total_sum');

            if ($total_between_first_and_start < $last_invoice_amount) {

                $invoice_minus_payment = $last_invoice_amount - $total_between_first_and_start;

                $payments_data = $adb->pquery("SELECT sp.amount, vc.createdtime  FROM sp_payments sp 
                                        INNER JOIN sp_paymentscf sp2 ON sp.payid = sp2.payid 
                                        INNER JOIN vtiger_crmentity vc ON vc.crmid = sp.payid 
                                        WHERE vc.deleted = 0
                                        AND sp2.cf_1416 = $flatid
                                        AND vc.createdtime BETWEEN '$start_day_of_last_month' and '$last_day_of_last_month'", array());

                $total_payments = $adb->num_rows($payments_data);
                if ($total_payments == 0) {

                    $date1 = new DateTime("$start_day_of_last_month");
                    $date2 = new DateTime("$last_day_of_last_month");
                    $interval = $date1->diff($date2);

                    $daysDifference = $interval->days;

                    $penalty_equal = ($invoice_minus_payment * $penalty_percent / 100) * $daysDifference;

                    $description[] = "$penalty_equal за $daysDifference дней, между  $start_day_of_last_month - $last_day_of_last_month ' от суммы $invoice_minus_payment";

                    $total_all_penalty += $penalty_equal;
                } else {
                    $previous_pay_date = $start_day_of_last_month;

                    for ($x = 0; $x < $adb->num_rows($payments_data); $x++) {
                        $pay_amount = $adb->query_result($payments_data, $x, 'amount');
                        $pay_date = $adb->query_result($payments_data, $x, 'createdtime');

                        if ($invoice_minus_payment > 0) {
                            $date1 = new DateTime("$previous_pay_date");
                            $date2 = new DateTime("$pay_date");
                            $interval = $date1->diff($date2);

                            $daysDifference = $interval->days;

                            $penalty_equal = ($invoice_minus_payment * $penalty_percent / 100) * $daysDifference;

                            $description[] = "$penalty_equal за $daysDifference дней, между $previous_pay_date - $pay_date от суммы $invoice_minus_payment";

                            $invoice_minus_payment -= $pay_amount;
                            $previous_pay_date = $pay_date;
                            $total_all_penalty += $penalty_equal;

                            if ($x == $total_payments - 1) {

                                $date1 = new DateTime("$previous_pay_date");
                                $date2 = new DateTime("$last_day_of_last_month");
                                $interval = $date1->diff($date2);

                                $daysDifference = $interval->days;

                                $penalty_equal = ($invoice_minus_payment * $penalty_percent / 100) * $daysDifference;

                                $description[] = "$penalty_equal за $daysDifference дней, между $previous_pay_date - $last_day_of_last_month от суммы $invoice_minus_payment";

                                $total_all_penalty += $penalty_equal;

                            }
                        }
                    }
                }
            }
        } elseif (abs($total) < $last_invoice_amount && $total < 0) {

            $invoice_minus_payment = $last_invoice_amount - abs($total);

            $total_between_first_and_start_result = $adb->pquery("SELECT IFNULL (SUM(round(amount,2)),0) as total_sum FROM sp_payments sp 
                                INNER JOIN sp_paymentscf sp2 ON sp.payid = sp2.payid 
                                INNER JOIN vtiger_crmentity vc ON vc.crmid = sp.payid 
                                WHERE vc.deleted = 0
                                AND sp2.cf_1416 = ?
                                AND vc.createdtime BETWEEN ? and ?", array($flatid, $first_day_of_last_month, $start_day_of_last_month));
            $total_between_first_and_start = $adb->query_result($total_between_first_and_start_result, 0, 'total_sum');

            if ($total_between_first_and_start < $invoice_minus_payment) {

                $invoice_minus_payment = $invoice_minus_payment - $total_between_first_and_start;

                $payments_data = $adb->pquery("SELECT sp.amount, vc.createdtime  FROM sp_payments sp 
                                        INNER JOIN sp_paymentscf sp2 ON sp.payid = sp2.payid 
                                        INNER JOIN vtiger_crmentity vc ON vc.crmid = sp.payid 
                                        WHERE vc.deleted = 0
                                        AND sp2.cf_1416 = $flatid
                                        AND vc.createdtime BETWEEN '$start_day_of_last_month' and '$last_day_of_last_month'", array());

                $total_payments = $adb->num_rows($payments_data);

                $previous_pay_date = $start_day_of_last_month;

                for ($x = 0; $x < $adb->num_rows($payments_data); $x++) {
                    $pay_amount = $adb->query_result($payments_data, $x, 'amount');
                    $pay_date = $adb->query_result($payments_data, $x, 'createdtime');

                    if ($invoice_minus_payment > 0) {

                        $date1 = new DateTime("$previous_pay_date");
                        $date2 = new DateTime("$pay_date");
                        $interval = $date1->diff($date2);

                        $daysDifference = $interval->days;

                        $penalty_equal = ($invoice_minus_payment * $penalty_percent / 100) * $daysDifference;

                        $description[] = "$penalty_equal за  $daysDifference  дней, между  $previous_pay_date  -  $pay_date  от суммы $invoice_minus_payment";

                        $invoice_minus_payment -= $pay_amount;
                        $previous_pay_date = $pay_date;
                        $total_all_penalty += $penalty_equal;

                        if ($x == $total_payments - 1) {

                            $date1 = new DateTime("$previous_pay_date");
                            $date2 = new DateTime("$last_day_of_last_month");
                            $interval = $date1->diff($date2);

                            $daysDifference = $interval->days;

                            $penalty_equal = round(($invoice_minus_payment * $penalty_percent / 100) * $daysDifference, 2);

                            $description[] = "$penalty_equal за $daysDifference дней, между $previous_pay_date - $last_day_of_last_month от суммы $invoice_minus_payment";

                            $total_all_penalty += $penalty_equal;
                        }
                    }
                }
            }
        }
        if (!empty($description)) {
            $penalty = Vtiger_Record_Model::getCleanInstance("Penalty");
            $penalty->set('penalty_amount', $total_all_penalty);
            $penalty->set('cf_to_ivoice', $invoice_id);
            $penalty->set('cf_type_penalty', 'Неоплачено');
            $penalty->set('cf_penalty_description', $description);
            $penalty->set('assigned_user_id', $current_user->id);
            $penalty->set('mode', 'create');
            $penalty->save();
        }
    }
    return ($total_all_penalty);
}