<?php
# В первую очерень необходимо залить номера в gapshap и подтвердить шаблон.
ob_clean();
ini_set('memory_limit', -1);
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Bishkek');
set_time_limit(0);
chdir('../');
require_once 'include/database/PearDatabase.php';
require_once 'config.inc.php';

require 'vendor/autoload.php';

if (isset($_GET['module'])) {
	if ($_GET['module'] == 'Invoice') {
		if (isset($_GET['selectedIds'])) {
			$selectedIds = $_GET['selectedIds'];
			$idList = json_decode($selectedIds);
			$viewName = $_GET['viewname'];
			if ($selectedIds == "all") {
				$idList = getIdList($viewName);
			}
            $string = '(' . implode(', ', $idList) . ')';
            $sql_invoices = "select vi.invoiceid, vi.contactid, vc.mobile from vtiger_invoice vi 
            inner join vtiger_invoicecf vi2 on vi2.invoiceid = vi.invoiceid
            inner join vtiger_contactdetails vc on vc.contactid = vi.contactid
            inner join vtiger_contactscf vc2 on vc2.contactid = vc.contactid
            where vi.invoiceid in $string";
            $invoices = $adb->run_query_allrecords($sql_invoices);

            foreach($invoices as $invoice) {
                $invoice_id = $invoice['invoiceid'];
                $contactid = $invoice['contactid'];
                $contact_mobile = $invoice['mobile'];
                // $contact_mobile = '996556013496'; # разкоментируй и подставь свой номер для проверки
                $contact_login = $invoice['cf_1506'];
                $contact_password = $invoice['cf_1508'];
                $messages_to_gp = "Здравствуйте, отправляем счет на электричество, перейдите по ссылке $site_URL"."PrintPDF/PrintPDF_for_invoice.php?module=Invoice&selectedIds=$invoice_id&viewname=21."; 

                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://wf.mycloud.kg/send-pdf',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "sender": "0505700011",
                    "recipientName": "",
                    "recipientNumber": "'.$contact_mobile.'",
                    "message": "'.$messages_to_gp.'"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                ));

                $response = curl_exec($curl);
                echo "<pre>";
                var_dump($response);
                echo "</pre>";
                exit();
                // echo "<pre>";
                // var_dump($site_URL);
                // echo "<pre>";
                // exit();
            //     $sql = "select vc.lastname, vc.mobile, vi.subject, vi.duedate, 
            //     vi.total, vip.margin, vip.previous_reading, vip.current_reading,
            //     vs.servicename, vs.unit_price, vf.flat, ba.telegramid
            //     from vtiger_contactdetails vc 
            //     inner join vtiger_crmentity vc2 on vc2.crmid = vc.contactid
            //     inner join vtiger_invoice vi on vi.contactid = vc.contactid
            //     inner join vtiger_inventoryproductrel vip on vip.id = vi.invoiceid
            //     inner join vtiger_service vs on vs.serviceid = vip.productid 
            //     inner join vtiger_invoicecf vic on vic.invoiceid = vi.invoiceid
            //     inner join vtiger_flats vf on vf.flatsid = vic.cf_1265
            //     inner join bot_auth ba on ba.vtiger_user_id = vc.contactid
            //     where vc2.deleted = 0 and vc.contactid = '$contactid' and vi.invoiceid = '$invoice_id'";

            //     $invsum = $adb->run_query_allrecords($sql);
            //     if (!empty($invsum)) {
            //         $url = 'http://localhost:5075/api/webhook';
            //         $data = array(
            //             'message' => $invsum   // Пример данных, которые вы хотите передать
            //         );
            //         $options = array(
            //         'http' => array(
            //             'method' => 'POST',
            //             'header' => 'Content-Type: application/json',
            //             'content' => json_encode($data)
            //         )
            //         );
            //         $context = stream_context_create($options);
            //         $result = file_get_contents($url, false, $context);
            //     }  
            }
		}
	}
}
echo '<script>window.close();</script>';
exit;
?>