<?php


//ini_set('display_errors', 1);
//error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Bishkek');

require_once 'DataBase.php';
require_once 'Logger.php';
require_once 'Pay.php';

$logger = new Logger('pay24.log');

try
{
	$dbConn = DataBase::getConn();
	$pay = new Pay();
	$response = $pay->run();
	$dbConn->close();
	print json_encode(array('success' => true, 'message' => $response, 'status' => 0));
}

catch (Exception $e)
{

	$message = $e->getMessage();
	$logger->log($message);

	if (strpos($message, 'Абонента с данным лицевым счетом не существует') === false)
	{
		// $message = 'Ошибка. Обратитесь к администратору системы';
		$status = 2;
	}
	else
	{
		$message = 'Абонент не найден';
		$status = 1;
	}

	print json_encode(array('success' => false, 'message' => $message, 'status' => $status));
}

