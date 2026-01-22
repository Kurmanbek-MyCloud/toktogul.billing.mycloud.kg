<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_clean();
ini_set('memory_limit', -1);
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Bishkek');
set_time_limit(0);


chdir('../');
var_dump('test');
require_once(__DIR__ . '/../include/database/PearDatabase.php');
require_once(__DIR__ . '/../libraries/tcpdf/tcpdf.php');
require_once(__DIR__ . '/../vendor/autoload.php');



$dataJson = $argv[1]; // Получаем переданный аргумент командной строки
var_dump($dataJson);
$data = json_decode($dataJson, true); // Декодируем JSON-строку в ассоциативный массив

$invoiceIDs = $data['invoiceIDs'];
$currentStreet = $data['currentStreet'];

var_dump($invoiceIDs);
var_dump($currentStreet);
exit();

$outputDirectory = __DIR__ . '/../downloaded_invoices/';

$mergedFilename = $outputDirectory . $currentStreet . '_' . date('Y-m-d') . '.pdf';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetAuthor('VTigerCRM - Billing');
$pdf->SetTitle('Merged Invoices');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(10, 10, 7, 7);
$pdf->SetAutoPageBreak(TRUE, 7);
$pdf->AddPage();

foreach ($invoiceIDs as $invoiceId) {
	$html = getHtml($invoiceId);
	$pdf->writeHTML($html);
}
// if (isset($_GET['module'])) {
// 	if ($_GET['module'] == 'Invoice') {
// 		if (isset($_GET['selectedIds'])) {
// 			$selectedIds = $_GET['selectedIds'];
// 			$idList = json_decode($selectedIds);
// 			$viewName = $_GET['viewname'];

// 			if ($selectedIds == "all") {
// 				$idList = getIdList($viewName);
// 			}

// 			foreach ($invoiceIDs as $id) {
// 				$html = getHtml($id);
// 				$pdf->writeHTML($html);
// 			}
// 		}
// 	}
// }

$pdf->Output($mergedFilename, 'F');

// function getIdList($viewName)
// {
// 	global $adb;
// 	$idList = [];
// 	$ids_sql = "SELECT invoiceid FROM vtiger_invoice i 
// 			INNER JOIN vtiger_crmentity crm ON crm.crmid = i.invoiceid 
// 			WHERE deleted = 0";
// 	$ids_result = $adb->run_query_allrecords($ids_sql);
// 	foreach ($ids_result as $value) {
// 		array_push($idList, $value['invoiceid']);
// 	}
// 	return $idList;
// }

function save_img($id, $personalAccount)
{
	require __DIR__ . '/../config.inc.php';
	$URL = 'https://subscriber.billing-app.mycloud.kg/';
	$width = $height = 300;
	$url = $URL . "main?organization_id=2%26account_number=$personalAccount"; // Формирование нового URL-адреса
	$image_link = "http://chart.googleapis.com/chart?chs={$width}x{$height}&cht=qr&chl=$url"; //Direct link to image
	$split_image = pathinfo($url);


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $image_link);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	curl_close($ch);
	$file_name = __DIR__ . '/../qrcodes/' . "qr_$id" . ".png";
	$file = fopen($file_name, 'w+') or die("X_x");
	fwrite($file, $response);
	fclose($file);
	// echo $file_name."<br>";
	return $file_name;
}
function getHtml($invoiceId)
{

	global $adb;

	$resultDB = $adb->run_query_allrecords(
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
		cf_1484 AS street,
		cf_1444 AS house,
		flat AS house_no,
		cf_1446 AS flat_no,
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

	$row = $resultDB[0];

	$duedate = $row['duedate'];
	$monthsList = array(
		"1" => "Январь",
		"2" => "Февраль",
		"3" => "Март",
		"4" => "Апрель",
		"5" => "Май",
		"6" => "Июнь",
		"7" => "Июль",
		"8" => "Август",
		"9" => "Сентябрь",
		"10" => "Октябрь",
		"11" => "Ноябрь",
		"12" => "Декабрь"
	);
	$month = date('m', strtotime($duedate)) + 0;
	if ($month == 0)
		$month = 12;
	$invoiceMonth = $monthsList[$month];
	$invoiceYear = date('Y', strtotime("$duedate  -0month"));

	if ($duedate == null) {
		$invoicePeriod = $row['subject'];
	} else {
		$invoicePeriod = "$invoiceMonth $invoiceYear г.";
	}
	// $invoicePeriod = "Апрель 2023 г.";

	$lastname = $row['lastname'];

	$debt = round($row['debt'], 2);
	$personalAccount = $row['ls'];
	$createdtime = date("d-m-Y", strtotime($row['createdtime']));
	$house_no = $row['house_no'];
	$flat_no = $row['flat_no'];
	$street = $row['street'];
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
	// $org_logo = $row['logoname'];

	// $targetPath = "barcodes/$personalAccount.png";
	// $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
	// file_put_contents($targetPath, $generator->getBarcode($personalAccount, $generator::TYPE_CODE_128));

	$URL = "https://subscriber.billing-app.mycloud.kg/main?organization_id=2&account_number=$personalAccount";


	// $logo = "test/logo/" . $org_logo;
	$qr_code = save_img($resultDB[0]['contactid'], $personalAccount);

	$html1 = "
	
	<table border= \"0\"class=\"head\" cellpadding=\"1\" >
		<tr class=\"td_border_top\">
			<td width=\"34%\">
				<strong>ЭСЕП-БИЛДИРМЕ / СЧЕТ-ИЗВЕЩЕНИЕ</strong><br>
				Мөөнөтү / Период счета: $invoicePeriod <br>
				Эсеп жазылды / Счёт выписан: $createdtime

			</td>
			<td width=\"33%\" class = \"space\" style=\" padding: 0;\" align=\"center\">
				" . $org_name . " <br>
				ИНН " . $org_inn . "<br>" . $org_address . "
				<br>
			</td>
			<td width=\"32%\" style=\" text-align:right \">
				Эсеп / Лицевой счет: <span style=\" font-size: 10px; font-weight: bold\">$personalAccount</span><br>
				Аты жөнү / ФИО: <b>$lastname</b><br>
				Дареги / Адрес: <span style=\" font-size: 8px;font-weight: bold\"> ул. $street $house_no $flat_no</span>
			</td>
		</tr>
	</table>
";


	$html2 = "	
		<table class=\"body\" cellpadding=\"1\">

		<tr class=\"table-head\">
			<td  width=\" 54% \" colspan=\"4\">Коммуналдык тейлөө / Ком. услуга</td>
			<td width=\" 16% \">Карыз  Задолженность</td>
			<td width=\" 15% \">Тариф</td>	
			<td width=\" 15% \">Сумма</td>
		</tr>
	";

	$sql = "
			select service.servicename, service.serviceid, inventoryproductrel.accrual_base, 
			inventoryproductrel.previous_reading, inventoryproductrel.current_reading, 
			inventoryproductrel.quantity, inventoryproductrel.listprice, 
			inventoryproductrel.margin,
			inventoryproductrel.grand_total
			from vtiger_inventoryproductrel inventoryproductrel 
			left join vtiger_service service on service.serviceid = inventoryproductrel.productid
			where inventoryproductrel.id = $invoiceId
			ORDER BY inventoryproductrel.accrual_base DESC
		";

	$resultDB = $adb->run_query_allrecords($sql);
	$itogo_all = 0;
	$itogo = 0;
	$pereplata = 0;
	$zadolzhennost = $debt;
	if ($debt < 0) {
		$pereplata = abs($debt);
		$zadolzhennost = 0;
	}

	foreach ($resultDB as $key => $row) {

		$serviceName = $row['servicename'];
		$listPrice = round($row['listprice'], 2);
		$margin = round($row['margin'], 2);
		$itogo += $margin;
		$itogo_all += $margin;

		$html2 .= "
			<tr>
				<td colspan=\"4\">$serviceName</td>
				<td>$zadolzhennost</td>
				<td>$listPrice</td>
				<td>$itogo</td>
			</tr>
			";
	}
	$itogo_all += $debt;

	if ((int) $itogo_all < 0) {
		$itogo_all = 0;
	}



	$html2 .= "
				<tr>
					<td width=\"15%\" style = \"border: none;\" rowspan=\"3\"><img src = \"$qr_code\" width=\"75\" ></td>
					<td width=\" 35% \" style = \"border: none; text-align: left;\">Ваш личный кабинет <br>находится по этому QR - коду</td>
					<td width=\" 39% \" style = \"border: none; text-align: right;\">Карыз / Задолженность:</td>
					<td width=\" 11% \" style=\"text-align: right;\">$zadolzhennost</td>
				</tr>
				<tr>
					<td width=\" 30% \" style = \"border: none; text-align: right;\">$URL</td>
					<td width=\" 44% \" style = \"border: none; text-align: right;\">Ашыкча төлөм / Переплата:</td>
					<td width=\" 11% \" style=\"text-align: right;\">$pereplata</td>
				</tr>
				<tr>
					<td width=\" 42% \" style = \"border: none; text-align: right;\"></td>
					<td width=\" 32% \" style = \"border: none; text-align: right;\"><b>Жалпы төлөм / Итого к оплате:</b></td>
					<td width=\" 11% \" style=\"text-align: right;\"><b>$itogo_all</b></td>
				</tr>
			</table>";

	$html3 = "	<tr><td align=\"center\"><b>Коммуналдык тейлөө / Ком. услуга</b></td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td align=\"left\" colspan=\"2\"> Аты жөнү: <b>$lastname</b></td></tr>
				<tr><td align=\"left\" colspan=\"2\"> Эсеп/Л.счет:<b><span style=\"font-size: 10px;\">$personalAccount</span></b></td></tr>
				<tr><td align=\"left\" colspan=\"2\"> Дареги: <b>$street $house_no $flat_no</b></td></tr>
				<tr><td align=\"left\" colspan=\"2\"> <b>Карыз: </b>$zadolzhennost</td></tr>
				<tr><td align=\"left\" colspan=\"2\"> <b>Ашыкча төлөм: </b> $pereplata</td></tr>
				<tr><td align=\"left\" colspan=\"2\"> <b>Жалпы төлөм: </b> 
				<span style=\"font-size: 8px;\">$itogo_all</span></td>
				<br>
				<img src = \"$qr_code\" width=\"50\" style=\"text-align: center;\">
				</tr>
				
				";




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
				<td width="79%"> 
					$html1
					$html2
				</td>
				<td width="20%">
					$html3
				</td>
			</tr>
			</table>
		</div>
EOD;

	return $htmlResult;

}