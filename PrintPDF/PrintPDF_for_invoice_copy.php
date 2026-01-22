<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_clean();
ini_set('memory_limit', -1);
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Bishkek');
set_time_limit(0);

chdir('../');

$finalPrice = 18.28;
$taxRate = 3;

// $listPrice = $finalPrice / (1 + $taxRate / 100);
$listPrice = round($finalPrice / (1 + $taxRate/100),2);

echo "listPrice: " . round($listPrice, 2);
exit();

require_once 'include/database/PearDatabase.php';
require_once 'libraries/tcpdf/tcpdf.php';
require 'vendor/autoload.php';

if (isset($_GET['module'])) {

	if ($_GET['module'] == 'Invoice') {

		if (isset($_GET['selectedIds'])) {

			$selectedIds = $_GET['selectedIds'];
			$idList = json_decode($selectedIds);
			$viewName = $_GET['viewname'];
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
				$html = getHtml($id);
				$pdf->writeHTML($html);
			}

			// New tab
			// $pdf->Output('Invoices_' . date('YmdHis') . '.pdf', 'I');

			$pdf->Output('Invoices_' . date('YmdHis') . '.pdf', 'I');
			// $pdf->Output('Invoices_'.date('YmdHis').'.pdf', 'D');
		}
	}
}

function getIdList($viewName)
{
	global $adb;
	$idList = [];
	$ids_sql = "SELECT invoiceid FROM vtiger_invoice i 
			INNER JOIN vtiger_crmentity crm ON crm.crmid = i.invoiceid 
			WHERE deleted = 0";
	$ids_result = $adb->run_query_allrecords($ids_sql);
	foreach ($ids_result as $value) {
		array_push($idList, $value['invoiceid']);
	}
	return $idList;
}

// function save_img($id, $personalAccount)
// {
// 	require "./config.inc.php";
// 	$URL = 'https://subscriber.billing-app.mycloud.kg/';
// 	$width = $height = 300;
// 	$url = $URL . "main?organization_id=2%26account_number=$personalAccount"; // Формирование нового URL-адреса
// 	$image_link = "http://chart.googleapis.com/chart?chs={$width}x{$height}&cht=qr&chl=$url"; //Direct link to image
// 	$split_image = pathinfo($url);


// 	$ch = curl_init();
// 	curl_setopt($ch, CURLOPT_URL, $image_link);
// 	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 	$response = curl_exec($ch);
// 	curl_close($ch);
// 	$file_name = "qrcodes/" . "qr_$id" . ".png";
// 	$file = fopen($file_name, 'w+') or die("X_x");
// 	fwrite($file, $response);
// 	fclose($file);
// 	// echo $file_name."<br>";
// 	return $file_name;
// }
function getHtml($invoiceId)
{
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
		f.flatsid, 
		vu.last_name,
		vu.first_name,
		((SELECT IFNULL((SELECT SUM(round(total, 1)) FROM vtiger_invoice AS di 
		INNER JOIN vtiger_invoicecf AS dicf ON dicf.invoiceid = di.invoiceid
		INNER JOIN vtiger_crmentity AS icrm ON icrm.crmid = di.invoiceid
		WHERE icrm.deleted = 0 AND dicf.cf_1265 = f.flatsid  AND di.invoiceid != i.invoiceid),0))
		-
		(SELECT IFNULL ((SELECT SUM(round(amount, 1)) FROM sp_payments AS p
		INNER JOIN sp_paymentscf AS pcf ON pcf.payid = p.payid
		INNER JOIN vtiger_crmentity AS pcrm ON pcrm.crmid = p.payid
		WHERE pcrm.deleted = 0 AND pcf.cf_1416 = f.flatsid),0))) AS debt
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
	$debt = round($row['debt'], 2);
	$personalAccount = $row['ls'];
	$createdtime = date("d-m-Y", strtotime($row['createdtime']));
	$house_no = $row['house_no'];
	$flat_no = $row['flat_no'];
	$street = $row['street'];
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
	$org_logo = $row['logoname'];
	$org_logo = $row['logoname'];
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
				<span style=\"font-size: 7px;\">Төлүш керек / Оплатить до: $paytime</span>

			</td>
			<td width=\"31%\" class = \"space\" style=\" padding: 0;\" align=\"center\">
				МП \"Токтогул Водоканал\" <br>
				ИНН " . $org_inn . "<br>" . "Жалал-Абадская Область" . "<br>" . "Токтогульский Район" . "<br>" . "с. Токтогул ул. Кудаш Момункулов, д.22
				<br>
			</td>
			<td width=\"32%\" style=\" text-align:right \">
				Эсеп / Лицевой счет: <span style=\" font-size: 10px; font-weight: bold\">$personalAccount</span><br>
				Аты жөнү / ФИО: <b>$lastname</b><br>
				Дареги / Адрес: <span style=\" font-size: 8px;font-weight: bold\">$area ул. $street $house_no $flat_no<br></span>
				Контролер: <span style=\" font-size: 8px;font-weight: bold\">$user_last_name $user_first_name</span>
			</td>
		</tr>
	</table>
";

	$present_day = new DateTime();
	$present_day->modify('first day of last month');
	$first_day_of_last_month = $present_day->format('Y-m-d'); // Получить первый день прошлого месяца
	$last_day_of_last_month = $present_day->format('Y-m-t'); // Получить последний день прошлого месяца

	$penalty_total = $adb->run_query_allrecords("SELECT sum(vp.penalty_amount) FROM vtiger_flats vf 
            INNER JOIN vtiger_invoicecf vi on vi.cf_1265 = vf.flatsid 
            INNER JOIN vtiger_penalty vp on vp.cf_to_ivoice = vi.invoiceid 
            INNER JOIN vtiger_crmentity vc on vc.crmid = vp.penaltyid 
            INNER JOIN vtiger_crmentity vc2 on vi.invoiceid = vc2.crmid
            WHERE vf.flatsid = $flatsid 
            AND vc.deleted = 0
            AND vc2.createdtime BETWEEN '$first_day_of_last_month' AND '$last_day_of_last_month'");

	$penalty_total = round($penalty_total[0][0], 2);

	$sql = "
			select service.servicename, service.serviceid, inventoryproductrel.accrual_base, 
			inventoryproductrel.previous_reading, inventoryproductrel.current_reading, 
			inventoryproductrel.quantity, inventoryproductrel.listprice, 
			inventoryproductrel.margin,
			inventoryproductrel.grand_total,
			inventoryproductrel.tax_final, 
			inventoryproductrel.pre_tax_total
			from vtiger_inventoryproductrel inventoryproductrel 
			left join vtiger_service service on service.serviceid = inventoryproductrel.productid
			where inventoryproductrel.id = $invoiceId
			ORDER BY inventoryproductrel.accrual_base ASC
		";

	$result = $adb->run_query_allrecords($sql);

	$check = [];

	foreach ($result as $row) {
		if (isset($row['serviceid'])) {
			$check[] = $row['serviceid'];
		}
	}
	// var_dump($check);
	$check_service = [30083, 30084, 80448];
	// Perform actions with each $serviceId
	if (in_array($check[0], $check_service)) {
		$html2 = "	
		<table class=\"body\" cellpadding=\"1\">

		<tr class=\"table-head\">
			<td  width=\" 32% \" colspan=\"4\">Коммуналдык кызмат / Ком. услуга</td>
			<td  width=\" 15% \" colspan=\"4\">Описание</td>
			<td width=\" 11% \" style=\"text-align: center;\">Предыдущее показание</td>
			<td width=\" 11% \" style=\"text-align: center;\">Последнее показание</td>
			<td width=\" 7% \" style=\"text-align: right;\">Расход (м3)</td>
			<td width=\" 7% \" style=\"text-align: right;\">Тариф</td>	
			<td width=\" 7% \" style=\"text-align: right;\">Налог</td>	
			<td width=\" 10% \" style=\"text-align: right;\">Сумма</td>
		</tr>";
	} else {
		$html2 = "
					<table class=\"body\" cellpadding=\"1\">
	
			<tr class=\"table-head\">
				<td  width=\" 64% \" colspan=\"4\">Коммуналдык кызмат / Ком. услуга</td>
				<td width=\" 12% \">Кол-во проживающих</td>
				<td width=\" 7% \" style=\"text-align: right;\">Тариф</td>	
				<td width=\" 7% \" style=\"text-align: right;\">Налог</td>	
				<td width=\" 10% \" style=\"text-align: right;\">Сумма</td>
			</tr>";
	}
	$serviceName2 = '';
	foreach ($result as $key => $row) {

		$serviceName = $row['servicename'];
		$serviceId = $row['serviceid'];
		$quantity = round($row['quantity'], 2);
		$listPrice = round($row['listprice'], 2);
		$margin = round($row['margin'], 2);
		$previousReading = round($row['previous_reading'], 2);
		$currentReading = round($row['current_reading'], 2);
		$accrualBase = $row['accrual_base'];
		$tax_final = $row['tax_final'];
		$temporary_penalty = $row['pre_tax_total'];

		$margin -= $temporary_penalty;
		if ($serviceName2 == $serviceName) {
			$serviceName = '';
		} else {
			$serviceName2 = $serviceName;
		}
		if ($quantity < 0) {
			$listPrice = 0;
			$margin = 0;
		}


		if ($serviceId == 30083 or $serviceId == 30084 or $serviceId == 80448) {
			$html2 .= "	
	
			<tr>
				<td width=\" 32% \" colspan=\"4\">$serviceName</td>
				<td width=\" 15% \" colspan=\"4\" >$accrualBase</td>
				<td style=\"text-align: center;\">$previousReading</td>
				<td style=\"text-align: center;\">$currentReading</td>
				<td style=\"text-align: right;\">$quantity</td>
				<td style=\"text-align: right;\">$listPrice</td>
				<td style=\"text-align: right;\">$tax_final</td>
				<td style=\"text-align: right;\">$margin</td>
				</tr>";

		} else {
			if ($serviceId == 77958 || $serviceId == 77962) {
				$listPrice = 17.75;
			}
			else if ($serviceId == 77963) {
				$listPrice = 26.13;
			}
			$html2 .= "	
			<tr>
				<td width=\" 64% \" colspan=\"4\">$serviceName</td>
				<td style=\"text-align: right;\">$quantity</td>
				<td style=\"text-align: right;\">$listPrice</td>
				<td style=\"text-align: right;\">$tax_final</td>
				<td style=\"text-align: right;\">$margin</td>
				</tr>";
		}
		$temp_summ += $margin;
	}

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

	if ($itogo_all == 0) {
		return;
	}

	if ($serviceId == 77958 || $serviceId == 78063) { // 	Вода - Дворовый
		// $tariff_description_1 = '60 литров * 306 дней = 18,36 м3(норма)';
		// $tariff_description_2 = '18,36м3 * 17.75(тариф) = 325,89(сумма за норму в год)';
		// $tariff_description_3 = '325,89/12 = 27,13(норма в месяц)';
	} elseif ($serviceId == 77961) { // Вода - Уличный
		// $tariff_description_1 = '35 литров * 365 = 12,77м3(норма)';
		$tariff_description_2 = 'Бир адамга бир айга 20 сом';
		// $tariff_description_3 = '226,75/12 = 18,9 (норма в месяц)';
	} elseif ($serviceId == 77959) { // Вода - Дворовый(Баня)
		// $tariff_description_1 = '100 литров * 306 дней = 30,6 м3(норма)';
		// $tariff_description_2 = '30,6 * 17.75(тариф) = 543,15(сумма за норму в год)';
		// $tariff_description_3 = '543,15/12 = 45,26(норма в месяц)';
	} elseif ($serviceId == 77962 || $serviceId == 77963) { // Вода - Благоустроенный, Канализация - Благоустроенный
		// $tariff_description_1 = '165 литров * 306 дней = 50,49м3(норма)';
		// $tariff_description_2 = '50,49 * 17.75(тариф) = 896,19(сумма за норму в год)';
		// $tariff_description_3 = '896,19/12 = 74,74(норма в месяц)';
	} elseif ($serviceId == 30083 || $serviceId == 30084 || $serviceId == 80448) {

	}

	$html2 .= "
				<tr>
					<td width=\" 51% \" style = \"border: none; text-align: left;\"></td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\">Туум / Пеня</td>
					<td width=\" 10% \" style=\"text-align: right;\">$penalty_total</td>
				</tr>
				<tr>
					<td width=\" 51% \" style = \"border: none; text-align: left;\">$tariff_description_2</td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\">Карыз / Задолженность</td>
					<td width=\" 10% \" style=\"text-align: right;\">$zadolzhennost</td>
				</tr>
				<tr>
					<td width=\" 51% \" style = \"border: none; text-align: left;\"></td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\">Ашыкча төлөм / Переплата</td>
					<td width=\" 10% \" style=\"text-align: right;\">$overpayment</td>
				</tr>
				<tr>
					<td width=\" 51% \" style = \"border: none; text-align: left;\"> <br>Пеня начисляется через 10 дней. Квитанцию хранить 3 года</td>
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