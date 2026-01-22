<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Bishkek');
require_once 'DataBase.php';
require_once 'MyLogger.php';
require_once 'Pay.php';
require_once 'CRM.php';

$logger = new MyLogger('payment_services/oimo/payments.log');

try {
	$dbConn = DataBase::getConn();
	$pay = new Pay();
	$CRM = new CRM();

	$response = $pay->run();
	if (is_array($response)) {
		$a = $response['amount'];
		$amount = sprintf("%.2f", $a);
		if ((is_int($response['cf_txnid']) || ctype_digit($response['cf_txnid'])) && $response['cf_txnid'] >= 0) {
			$logger->log($response['command']." сумма ".$amount." номер платежа ".$response['cf_txnid']);	
			print json_encode(array('success' => true, 'txn_id' => $response['cf_txnid'], 'sum' => $amount, 'comment' => $response['command'], 'result' => 0));
		} else {
			print json_encode(array('success' => true, 'comment' => $response, 'result' => 0));
		}
	} else {
		$logger->log($response);	
		print json_encode(array('success' => true, 'comment' => $response, 'result' => 0));	
	}
	$dbConn->close();
} catch (Exception $e) {

	$message = $e->getMessage();
	$logger->log($message);

	if (strpos($message, 'Абонента с данным лицевым счетом не существует') === false) {
		$status = 2;
	} else {
		$message = 'Абонент не найден';
		$status = 1;
	}

	print json_encode(array('success' => false, 'message' => $message, 'result' => $status));
}