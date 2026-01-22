<?php
namespace OimoV2;

use OimoV2\DataBase;
use OimoV2\MyLogger;
use OimoV2\Pay;
use OimoV2\CRM;
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Bishkek');

require_once __DIR__ . '/DataBase.php';
require_once __DIR__ . '/MyLogger.php';
require_once __DIR__ . '/Pay.php';
require_once __DIR__ . '/CRM.php';

$logger = new MyLogger(__DIR__ . '/payments.log');

class DatabaseException extends \Exception {
}
class PaymentException extends \Exception {
}

try {
	$dbConn = DataBase::getConn();
	$pay = new Pay();
	$CRM = new CRM();

	$response = $pay->run();

	if (is_array($response)) {
		$a = $response['amount'];
		$amount = sprintf("%.2f", $a);
		if ($response['action'] == 'pay') {
			$logger->log($response['command'] . " сумма " . $amount . " номер платежа " . $response['cf_txnid']);
			print json_encode(array('success' => true, 'txn_id' => $response['cf_txnid'], 'sum' => $amount, 'comment' => $response['command'], 'result' => 0));
		} elseif ($response['action'] == 'check_pay_status') {
			// print json_encode($response);
			print json_encode(array('success' => true, 'txn_id' => $response['cf_txnid'], 'sum' => $amount, 'comment' => $response['comment'], 'result' => 0));
		} else {
			print json_encode(array('success' => true, 'comment' => $response, 'result' => 0));
		}
	} else {
		$logger->log($response);
		print json_encode(array('success' => true, 'comment' => $response, 'result' => 0));
	}
} catch (\Exception $e) {
	handleException($e);
} finally {
	$dbConn->close();
}

function handleException($e) {
	global $logger;
	$logger->log($e->getMessage());

	if ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Пустой лицевой счет') !== false) {
		$message = 'Пустой лицевой счет';
		$errorCode = 2;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Абонента с данным лицевым счетом не существует') !== false) {
		$message = 'Абонент не найден';
		$errorCode = 1;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'В системе уже есть оплата с данным идентификатором платежа.') !== false) {
		$message = 'В системе уже есть оплата с данным идентификатором платежа';
		$errorCode = 3;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Не определено поле команды') !== false) {
		$message = 'Не определено поле команды';
		$errorCode = 4;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Не определено поле токена') !== false) {
		$message = 'Не определено поле токена';
		$errorCode = 5;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Индентификатор платежа пустой') !== false) {
		$message = 'Индентификатор платежа пустой';
		$errorCode = 6;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Не определено поле даты') !== false) {
		$message = 'Не определено поле даты';
		$errorCode = 7;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Не определено поле суммы') !== false) {
		$message = 'Не определено поле суммы';
		$errorCode = 8;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Не определен тип оплачиваемой услуги') !== false) {
		$message = 'Не определен тип оплачиваемой услуги';
		$errorCode = 9;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Неправильный формат даты') !== false) {
		$message = 'Неправильный формат даты';
		$errorCode = 10;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Неверная команда') !== false) {
		$message = 'Неверная команда';
		$errorCode = 11;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Указанная услуга не найдена') !== false) {
		$message = 'Указанная услуга не найдена';
		$errorCode = 12;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Услуги отсутствуют') !== false) {
		$message = 'Услуги отсутствуют';
		$errorCode = 13;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'В системе оказалось больше одного абонента с данным лицевым счетом') !== false) {
		$message = 'В системе оказалось больше одного абонента с данным лицевым счетом';
		$errorCode = 14;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Оплата с данным идентификатором платежа не найдена') !== false) {
		$message = 'Оплата с данным идентификатором платежа не найдена';
		$errorCode = 15;
	} elseif ($e instanceof \OimoV2\PaymentException && strpos($e->getMessage(), 'Обнаружено более одного платежа с таким же идентификатором') !== false) {
		$message = 'Обнаружено более одного платежа с таким же идентификатором';
		$errorCode = 16;
	} elseif ($e instanceof DatabaseException) {
		$message = 'Произошла ошибка. Обратитесь в службу поддержки.';
		$errorCode = 500;
	}
	print json_encode([
		'success' => false,
		'message' => $message,
		'result' => $errorCode
	]);
}

