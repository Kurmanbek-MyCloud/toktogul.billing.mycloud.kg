<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);
ob_clean();
ini_set('memory_limit', -1);
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Bishkek');
set_time_limit(0);

chdir('../');

require_once 'include/database/PearDatabase.php';
require_once 'libraries/tcpdf/tcpdf.php';
require 'vendor/autoload.php';

if (isset($_GET['module'])) {

	if ($_GET['module'] == 'Invoice') {

		if (isset($_GET['selectedIds'])) {

			$selectedIds = $_GET['selectedIds'];
			$idList = json_decode($selectedIds);
			$viewName = $_GET['viewname'];
			$debug = isset($_GET['debug']) && ($_GET['debug'] === '1' || $_GET['debug'] === 'true');
			// var_dump($viewName);
			// exit();
			// $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

			$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
			$pdf->SetAuthor('VTigerCRM - Billing');
			$pdf->SetTitle('Invoices');
			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);
			$pdf->SetMargins(10, 10, 7, 7);
			$pdf->SetAutoPageBreak(TRUE, 7);
			$pdf->AddPage();

			if ($selectedIds == "all") {
				$idList = getIdList($viewName);
			}
			foreach ($idList as $id) {
				$html = getHtml($id, $debug);
				$pdf->writeHTML($html);
			}

			// New tab
			// $pdf->Output('Invoices_' . date('YmdHis') . '.pdf', 'I');

			$pdf->Output('Invoices_' . date('YmdHis') . '.pdf', 'I');
			// $pdf->Output('Invoices_'.date('YmdHis').'.pdf', 'D');
		}
	}
}

function getIdList($viewName) {
	global $adb;
	$idList = [];
	if ($viewName == '21') {
		$ids_sql = "
				SELECT invoiceid FROM vtiger_invoice i 
				INNER JOIN vtiger_crmentity crm ON crm.crmid = i.invoiceid 
				WHERE deleted = 0 ";
		$ids_result = $adb->run_query_allrecords($ids_sql);
		foreach ($ids_result as $value) {
			array_push($idList, $value['invoiceid']);
		}
	} else {
		$view_params_sql = "SELECT * FROM vtiger_cvadvfilter 
				WHERE cvid = $viewName";
		$view_result = $adb->run_query_allrecords($view_params_sql);

		$final_Sql = "SELECT vtiger_invoice.invoiceid FROM vtiger_invoice 
		INNER JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid  
		INNER JOIN vtiger_crmentity crm ON crm.crmid = vtiger_invoice.invoiceid";
		$where = "WHERE ";
		foreach ($view_result as $row) {
			$rel_field = '';
			$tablename = explode(':', $row['columnname'])[0];
			$columnname = explode(':', $row['columnname'])[1];
			$rel_field = trim(explode('(', explode(';', explode(':', $row['columnname'])[2])[0])[1]);
			$value = $row['value'];
			$comparator = $row['comparator'];
			$column_condition = $row['column_condition'];
			if ($tablename != 'vtiger_invoice') {
				$pri_key_sql = "show columns from " . $tablename . " where `Key` = 'PRI'";
				$pri_key_result = $adb->pquery($pri_key_sql, array());
				$primary_key = $adb->query_result($pri_key_result, 0);

				$other_rel_sql = "SELECT f.tablename as reltablename,e.tablename, e.entityidfield, e.fieldname FROM vtiger_field f 
					INNER JOIN vtiger_fieldmodulerel r ON  r.fieldid = f.fieldid
					INNER JOIN vtiger_entityname e ON e.modulename = r.relmodule
					WHERE f.columnname = '$columnname'
					";
				$other_rel = $adb->run_query_allrecords($other_rel_sql);
				if (substr($rel_field, 0, 3) == 'cf_') {
					$inv_table = "vtiger_invoicecf";
				} else {
					$inv_table = "vtiger_invoice";
					if ($rel_field == 'contact_id' || $rel_field == 'account_id') {
						$rel_field = str_replace("_", "", $rel_field);
					}
				}
				if ($tablename != 'vtiger_crmentity') {
					$final_Sql .= " INNER JOIN $tablename on $tablename.$primary_key = $inv_table.$rel_field \n";
				}
				if (count($other_rel) > 0) {
					$final_Sql .= " INNER JOIN " . $other_rel[0]['tablename'] . " ON " . $other_rel[0]['tablename'] . "." . $other_rel[0]['entityidfield'] . " = " . $other_rel[0]['reltablename'] . "." . $columnname . ' ';
					$columnname = $other_rel[0]['fieldname'];
				}
				$comparated_value = getComparatedValue($value, $comparator);
				$where .= " $columnname $comparated_value $column_condition";
			} else {
				$comparated_value = getComparatedValue($value, $comparator);
				$where .= " and $columnname $comparated_value $column_condition";
			}
		}
		$where .= "  and crm.deleted = 0";
		$final_Sql .= $where;

		$ids_result = $adb->run_query_allrecords($final_Sql);

		foreach ($ids_result as $value) {
			array_push($idList, $value['invoiceid']);
		}
	}
	return $idList;
}

function getHtml($invoiceId, $debug = false) {
	// var_dump($invoiceId);
	// exit();

	global $adb;

	$result = $adb->run_query_allrecords(
		"SELECT fcf.cf_1420 AS ls,
		i.accountid,
		cd.lastname,
		cd.contactid,
		a.accountname,
		duedate,
		i.total,
		i.subject,
		vu.phone_mobile as cont_phone,
		crm.createdtime,
		o.organizationname AS org_name,
		o.inn AS org_inn,
		o.address AS org_address,
		o.phone AS org_phone,
		cf_1448 AS street,
		cf_1444 AS house,
		flat AS house_no,
		cf_1446 AS flat_no,
		cf_1450 AS area,
		fcf.cf_1289 AS flat_balance,
		f.flatsid, 
		vu.last_name,
		vu.first_name,
		((SELECT IFNULL((SELECT SUM(round(total, 1)) FROM vtiger_invoice AS di 
		INNER JOIN vtiger_invoicecf AS dicf ON dicf.invoiceid = di.invoiceid
		INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = di.invoiceid
		WHERE icrm.deleted = 0 AND dicf.cf_1265 = f.flatsid AND icrm.createdtime <= crm.createdtime AND di.invoiceid != i.invoiceid),0))
		-
		(SELECT IFNULL ((SELECT SUM(round(amount, 1)) FROM sp_payments AS p
		INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
		INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
		WHERE pcrm.deleted = 0 AND pcf.cf_1416 = f.flatsid AND pcrm.createdtime <= crm.createdtime),0))) AS debt,
	   COALESCE((SELECT m.meter FROM vtiger_meterscf vm 
				INNER JOIN vtiger_meters m ON m.metersid = vm.metersid
				INNER JOIN vtiger_crmentity vc ON vc.crmid = vm.metersid
				WHERE vc.deleted = 0
				AND vm.cf_1319 = fcf.flatsid), 'нету счетчика') AS meters
		FROM vtiger_invoice i 
		INNER JOIN vtiger_invoicecf icf ON icf.invoiceid = i.invoiceid 
		LEFT JOIN vtiger_contactdetails cd ON cd.contactid = i.contactid
		LEFT JOIN vtiger_contactscf cf ON cf.contactid = cd.contactid
		LEFT JOIN vtiger_contactaddress cad ON cad.contactaddressid = cd.contactid
		INNER JOIN vtiger_crmentity crm ON crm.crmid = i.invoiceid 
		LEFT JOIN vtiger_flatscf fcf ON fcf.flatsid = icf.cf_1265
		LEFT JOIN vtiger_flats f ON f.flatsid = icf.cf_1265
		LEFT JOIN vtiger_crmentity fcrm on fcf.flatsid = fcrm.crmid 
		LEFT JOIN vtiger_users vu on vu.id = fcrm.smownerid
		LEFT JOIN vtiger_account a on a.accountid = i.accountid 
		JOIN vtiger_organizationdetails o 
		WHERE crm.deleted = 0
	 and i.invoiceid = $invoiceId"
	);

	$row = $result[0];

	// $duedate = $row['duedate'];
	// $monthsList = array(
	// 	"1" => "Январь",
	// 	"2" => "Февраль",
	// 	"3" => "Март",
	// 	"4" => "Апрель",
	// 	"5" => "Май",
	// 	"6" => "Июнь",
	// 	"7" => "Июль",
	// 	"8" => "Август",
	// 	"9" => "Сентябрь",
	// 	"10" => "Октябрь",
	// 	"11" => "Ноябрь",
	// 	"12" => "Декабрь"
	// );
	// $month = date('m', strtotime($duedate)) + 0;
	// if ($month == 0)
	// 	$month = 12;
	// $invoiceMonth = $monthsList[$month];
	// $invoiceYear = date('Y', strtotime("$duedate  -0month"));

	// if ($duedate == null) {
	// 	$invoicePeriod = $row['subject'];
	// } else {
	// 	$invoicePeriod = "$invoiceMonth $invoiceYear г.";
	// }

	$lastname = $row['lastname'];
	$flatsid = $row['flatsid'];
	// debt (fallback) - старый расчёт из SQL
	$debt = round((float)($row['debt'] ?? 0), 2);
	// flat balance - общий баланс по ЛС (vtiger_flatscf.cf_1289)
	$flat_balance = isset($row['flat_balance']) ? (float)$row['flat_balance'] : null;
	$invoice_total = round((float)($row['total'] ?? 0), 2);
	$personalAccount = $row['ls'];
	$createdtime = date("d-m-Y", strtotime($row['createdtime']));
	$house_no = $row['house_no'];
	$flat_no = $row['flat_no'];
	$street = $row['street'];
	$meter = $row['meters'];
	$area = $row['area'];
	$subject = $row['subject'];
	if ($flat_no == null) {
		$flat_no = '';
	} else if ($flat_no == 0) {
		$flat_no = '';
	} else {
		$flat_no = " кв.$flat_no";
	}
	// Organization information
	$org_name = $row['org_name'];
	$org_inn = $row['org_inn'];
	$org_address = $row['org_address'];
	$org_logo = $row['logoname'] ?? '';
	$user_first_name = $row['first_name'];
	$user_last_name = $row['last_name'];
	$paytime = date('d-m-Y', strtotime($createdtime . "+10 days"));

	$html1 = "
	
	<table border= \"0\"class=\"head\" cellpadding=\"1\" >
		<tr class=\"td_border_top\">
			<td width=\"36%\">
				<strong>ЭСЕП-БИЛДИРМЕ / СЧЕТ-ИЗВЕЩЕНИЕ</strong><br>
				<span style=\"font-size: 7px;\">Мөөнөтү / Период счета: $subject</span><br>
				<span style=\"font-size: 7px;\">Эсеп жазылды / Счёт выписан: $createdtime</span><br>
				<span style=\"font-size: 7px;\">Төлүш керек / Оплатить до: $paytime </span>

			</td>
			<td width=\"31%\" class = \"space\" style=\" padding: 0;\" align=\"center\">
				МП \"Токтогул Водоканал\" <br>
				ИНН " . $org_inn . "<br>" . "Жалал-Абадская Область" . "<br>" . "Токтогульский Район" . "<br>" . "с. Токтогул ул. Кудаш Момункулов, д.22
				<br>
			</td>
			<td width=\"32%\" style=\" text-align:right \">
				Эсеп / Лицевой счет: <span style=\" font-size: 10px; font-weight: bold\">$personalAccount</span><br>
				Аты жөнү / ФИО: <b>$lastname</b><br>
				Дареги / Адрес: <span style=\" font-size: 8px;font-weight: bold\">$area ул. $street $house_no $flat_no</span><br>
				Номер счетчика: <b>$meter</b>
			</td>
		</tr>
	</table>
";

	$present_day = new DateTime();
	$present_day->modify('first day of last month');
	// $first_day_of_last_month = $present_day->format('Y-m-d'); // Получить первый день прошлого месяца
	// $last_day_of_last_month = $present_day->format('Y-m-t'); // Получить последний день прошлого месяца

	$penalty_total = 0;
	$cf_penalty_description = '';
	$penalty_info = $adb->run_query_allrecords("SELECT vp.penalty_amount, vp.cf_penalty_description  FROM vtiger_penalty vp 
					INNER JOIN vtiger_crmentity vc ON vc.crmid = vp.penaltyid 
					WHERE vc.deleted = 0
					AND vp.cf_to_ivoice = $invoiceId");
	if ($penalty_info) {
		$penalty_total = $penalty_info[0]['penalty_amount'];
		$cf_penalty_description = $penalty_info[0]['cf_penalty_description'];
	}
	$penalty_total = round((float)$penalty_total, 2);

	// Если есть общий баланс по ЛС, пересчитываем задолженность/переплату как:
	// debt_before = balance - invoice_total - penalty_current
	// (так пеня остаётся отдельной строкой и не задваивается в "Итого")
	if ($flat_balance !== null) {
		$flat_balance = round((float)$flat_balance, 2);
		$debt = round($flat_balance - $invoice_total - $penalty_total, 2);
	}

	$sql = "
			select service.servicename, service.serviceid, servicecf.cf_1297 as accrual_base, 
			inventoryproductrel.previous_reading, inventoryproductrel.current_reading, 
			inventoryproductrel.quantity, inventoryproductrel.listprice, 
			inventoryproductrel.margin,
			inventoryproductrel.grand_total,
			inventoryproductrel.tax_final, 
			inventoryproductrel.tax1
			from vtiger_inventoryproductrel inventoryproductrel 
			left join vtiger_service service on service.serviceid = inventoryproductrel.productid
			left join vtiger_servicecf servicecf on service.serviceid = servicecf.serviceid 
			where inventoryproductrel.id = $invoiceId
			ORDER BY inventoryproductrel.accrual_base ASC
		";

	$result = $adb->run_query_allrecords($sql);

	$temp_summ = 0;
	$itogo = 0;
	$itogo_all = 0;
	$overpayment = 0;
	$zadolzhennost = 0;

	$check = [];

	foreach ($result as $row) {
		if ($row['accrual_base'] == 'Счетчик') {
			$check[] = $row['accrual_base'];
		}
	}
	// var_dump($result);
	// exit();
	// $check_service = ['Счетчик'];

	$hasMeterAccrual = in_array('Счетчик', $check, true);
	if ($hasMeterAccrual) {
		$html2 = "	
		<table class=\"body\" cellpadding=\"1\">
		<tr class=\"table-head\">
			<td  width=\" 31% \" colspan=\"4\">Коммуналдык кызмат / Ком. услуга</td>
			<td  width=\" 13% \" colspan=\"4\">Метод расчета</td>
			<td width=\" 11% \" style=\"text-align: center;\">Предыдущее показание</td>
			<td width=\" 11% \" style=\"text-align: center;\">Последнее показание</td>
			<td width=\" 10% \" style=\"text-align: right;\">Количество</td>
			<td width=\" 7% \" style=\"text-align: right;\">Тариф</td>	
			<td width=\" 7% \" style=\"text-align: right;\">Налог</td>	
			<td width=\" 10% \" style=\"text-align: right;\">Сумма</td>
		</tr>";
	} else {
		$html2 = "
			<table class=\"body\" cellpadding=\"1\">
			<tr class=\"table-head\">
				<td  width=\" 31% \" colspan=\"4\">Коммуналдык кызмат / Ком. услуга</td>
				<td  width=\" 35% \" colspan=\"4\">Метод расчета</td>
				<td width=\" 10% \">Количество</td>
				<td width=\" 7% \" style=\"text-align: right;\">Тариф</td>	
				<td width=\" 7% \" style=\"text-align: right;\">Налог</td>	
				<td width=\" 10% \" style=\"text-align: right;\">Сумма</td>
			</tr>";
	}
	$serviceName2 = '';
	foreach ($result as $key => $row) {

		$serviceName = $row['servicename'];
		$serviceId = $row['serviceid'];
		$quantityRaw = (float)($row['quantity'] ?? 0);
		$quantity = round($quantityRaw, 2);
		$listPriceRaw = (float)($row['listprice'] ?? 0);
		$listPriceRawRounded = round($listPriceRaw, 2);
		$listPrice = $listPriceRaw;
		$marginRaw = (float)($row['margin'] ?? 0);
		$margin = round($marginRaw, 2);
		$previousReading = round($row['previous_reading'], 3);
		$currentReading = round($row['current_reading'], 3);
		$accrualBase = $row['accrual_base'];


		if ($serviceName2 == $serviceName) {
			$serviceName = '';
		} else {
			$serviceName2 = $serviceName;
		}
		if ($quantityRaw < 0) {
			// $listPrice = 0;
			$marginRaw = 0;
			$margin = 0;
		}

		$tax1 = (float)($row['tax1'] ?? 0);
		// Налог добавляется к цене сверху (не включён в цену)
		// listPrice - это тариф БЕЗ налога (база)
		// margin = listPrice * quantity + налог
		$margin_without_tax = $listPriceRaw * $quantityRaw;
		$tax_final = ($tax1 > 0)
			? round($margin_without_tax * $tax1 / 100, 2)
			: 0;
		$listPriceCell = $debug
			? ('base:' . $listPriceRawRounded . '<br><span style="font-size:6px">tax:' . $tax1 . '%</span>')
			: (string)$listPriceRawRounded;
		// var_dump($listPrice);
		// var_dump($tax_final);
		// exit();

		if ($accrualBase == 'Счетчик') {
			$html2 .= "	
	
			<tr>
				<td width=\" 31% \" colspan=\"4\">$serviceName</td>
				<td width=\" 13% \" >$accrualBase</td>
				<td width=\" 11% \" style=\"text-align: center;\">$previousReading</td>
				<td width=\" 11% \"style=\"text-align: center;\">$currentReading</td>
				<td width=\" 10% \"style=\"text-align: center;\">$quantity</td>
				<td width=\" 7% \"style=\"text-align: center;\">$listPriceCell</td>
				<td width=\" 7% \"style=\"text-align: center;\">$tax_final</td>
				<td width=\" 10% \"style=\"text-align: right;\">$margin</td>
				</tr>";

		} else {

			$html2 .= "	
			<tr>
				<td width=\" 31% \" colspan=\"4\">$serviceName</td>
				<td width=\" 35% \" >$accrualBase</td>
				<td width=\" 10% \" style=\"text-align: center;\">$quantity</td>
				<td width=\" 7% \" style=\"text-align: center;\">$listPriceCell</td>
				<td width=\" 7% \" style=\"text-align: center;\">$tax_final</td>
				<td width=\" 10% \" style=\"text-align: right;\">$margin</td>
				</tr>";
		}
		$temp_summ += $margin;
	}

	// Итоги
	if ($flat_balance !== null) {
		// Если используем общий баланс по ЛС:
		// задолженность/переплата показываем ДО текущего счета,
		// а "Итого к оплате" = max(баланс, 0)
		$zadolzhennost = ($debt > 0) ? $debt : 0;
		$overpayment = ($debt < 0) ? abs($debt) : 0;
		$itogo_all = ($flat_balance > 0) ? $flat_balance : 0;
	} else {
		// Fallback: старый расчёт
		// var_dump($temp_summ);
		if ($debt < 0) {
			$itogo = ($debt + $temp_summ);
			$overpayment = abs($debt);
			if ($itogo < 0) {
				$zadolzhennost = 0;
				$itogo = 0;
			}
		} else {
			$zadolzhennost = $debt;
			$itogo += ($debt + $temp_summ);
		}

		$itogo_all += $itogo + $penalty_total;
	}

	// Раскоментировать если не нужно формировать нулевые счета!
	// if ($itogo_all == 0) {
	// 	return;
	// }

	$html2 .= "
				<tr>
					<td width=\" 51% \" style = \"border: none; text-align: left;\">
					</td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\">Карыз / Задолженность</td>
					<td width=\" 10% \" style=\"text-align: right;\">$zadolzhennost</td>
				</tr>
				<tr >
					<td width=\" 51% \" style = \"border: none; text-align: left;\"></td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\">Ашыкча төлөм / Переплата</td>
					<td width=\" 10% \" style=\"text-align: right;\">$overpayment</td>
				</tr>
				<tr>
					<td width=\" 51% \" style = \"border: none; text-align: left;\">$cf_penalty_description</td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\">Пеня</td>
					<td width=\" 10% \" style=\"text-align: right;\">$penalty_total</td>
				</tr>
				<tr>
					<td width=\" 51% \" style = \"border: none; text-align: left;\"> <br>Оплатить можете в мобильном приложении РСК Банк<br>Пеня начисляется через 10 дней. Квитанцию хранить 3 года</td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\"><b>Жалпы төлөм / Итого к оплате</b></td>
					<td width=\" 10% \" style=\"text-align: right;\"><b>$itogo_all</b></td>
				</tr>
			</table>";

	// $html3 = "	<tr><td align=\"center\"><b>МП \"Лебединовка КомТранс\"</b></td></tr>
	// 			<tr><td>&nbsp;</td></tr>
	// 			<tr><td align=\"left\" colspan=\"2\"> Аты жөнү: <b>$lastname</b></td></tr>
	// 			<tr><td align=\"left\" colspan=\"2\"> Эсеп/Л.счет:<b><span style=\"font-size: 10px;\">$personalAccount</span></b></td></tr>
	// 			<tr><td align=\"left\" colspan=\"2\"> Дареги: <b>$area <br> $street $house_no $flat_no</br></td></tr>
	// 			<tr><td align=\"left\" colspan=\"2\"> <b>Карыз: </b>$zadolzhennost</td></tr>
	// 			<tr><td align=\"left\" colspan=\"2\"> <b>Ашыкча төлөм: </b> $pereplata</td></tr>
	// 			<tr><td align=\"left\" colspan=\"2\"> <b>Жалпы төлөм: </b> 
	// 			<span style=\"font-size: 8px;\">$itogo_all</span></td>
	// 			</tr>
	// 			";




	$htmlResult =
		<<<EOD
		<style>

		.invoice {
			font-size: 8px;
		}
		.body {
			width:100%;
			padding: 0;
			margin: 0;
		}
		.body td {
			border: .5px solid black;
		}
		.head td {

		}
		.table-head {
			font-weight: bold;
		}

		div.invoice {
			border-bottom: .1px dashed black;
			
		}
		.td_border_bot{

		}
		/*.td_border_top{
			border-top: 1px solid black;
			border-right: 1px solid black;
			border-left: 1px solid black;
			border-bottom: 1px solid black;
		}*/


		.space{
			display: flex;
			border: none;
			justify-content: center;
		}

		</style>

		<div nobr="true" class="invoice">
			<table border="1">
			<tr>
				<td> 
					$html1
					$html2
				</td>
			</tr>
			</table>
		</div>
EOD;

	return $htmlResult;

}

function getComparatedValue($value, $comparator) {
	$comparated_value = '';
	$comparator = trim($comparator);
	// $comparator = 'lastmonth';
	switch ($comparator) {
		case 'e':
			$comparated_value = " = '$value' ";
			break;
		case 'n':
			$comparated_value = " != '$value' ";
			break;
		case 'ny':
			$comparated_value = " != '' ";
			break;
		case 'l':
			$comparated_value = " < '$value' ";
			break;
		case 'g':
			$comparated_value = " > '$value' ";
			break;
		case 'm':
			$comparated_value = " <= '$value' ";
			break;
		case 'h':
			$comparated_value = " >= '$value' ";
			break;
		case 'c':
			$comparated_value = "  LIKE '%$value%' ";
			break;
		case "today":
			$value = date('Y-m-d');
			$comparated_value = " = '$value' ";
			break;
		case "yesterday":
			$value = date('Y-m-d', strtotime("-1 day"));
			$comparated_value = " = '$value' ";
			break;
		case "tomorrow":
			$value = date('Y-m-d', strtotime("+1 day"));
			$comparated_value = " = '$value' ";
			break;
		case "thismonth":
			$a = date('Y-m-01');
			$b = date('Y-m-t');
			$comparated_value = " BETWEEN '$a' AND '$b' ";
			break;
		case "lastmonth":
			$a = date('Y-m-01', strtotime("-1 month"));
			$b = date('Y-m-t', strtotime("-1 month"));
			$comparated_value = " BETWEEN '$a' AND '$b' ";
			break;
		case "nextmonth":
			$a = date('Y-m-01', strtotime("+1 month"));
			$b = date('Y-m-t', strtotime("+1 month"));
			$comparated_value = " BETWEEN '$a' AND '$b' ";
			break;
		default:
			break;
	}
	return $comparated_value;
}