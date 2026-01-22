<?php

class Pay {


	public function run() {
		$input = file_get_contents('php://input');
		$payment = json_decode($input);
		if (isset($payment->token)) {
			$payment_token = $payment->token;
			$system_ip = $_SERVER['REMOTE_ADDR'];
			$pay_system = $this->check_system($payment_token, $system_ip);
			if ($payment->command == 'show_service') {
				return $this->show_service();
			}
			if ($payment->command == 'check_pay_status') {
				if (isset($payment->txn_id)) {
					return $this->checkStatusId($payment->txn_id);
				} else {
					throw new Exception('Не определен уникальный индентифиикатор платежа');
				}
			}
			if (isset($payment->account)) {
				if (empty($payment->account)) {
					throw new Exception('Пустой лицевой счет');
				} else {
					$accountNumber = $payment->account;
					if (isset($payment->command)) {
						if ($payment->command == 'check') {
							return $this->checkAccount($accountNumber);
						} elseif ($payment->command == 'pay') {
							if (isset($payment->txn_id)) {
								if (!empty($payment->txn_id)) {
									if (isset($payment->txn_date)) {
										if (empty($payment->txn_date)) {
											throw new Exception('Дата пополнения пуста');
										} else {
											$txndate = $payment->txn_date;
											if (strtotime($txndate) === false) {
												throw new Exception("Неправильный формат даты");
											}
											if (isset($payment->sum)) {
												if (empty($payment->sum)) {
													throw new Exception('Сумма пополнения пуста');
												} else {
													$txnid = $payment->txn_id;
													$amount = $payment->sum;
													if (isset($payment->service)) {
														if (empty($payment->service)) {
															throw new Exception('Тип оплачиваемой услуги пустой');
														} else {
															$type_system = $payment->service;
															return $this->makePayment($accountNumber, $txndate, $amount, $txnid, $pay_system, $type_system);
														}
													} else {
														throw new Exception('Не определено поле тип оплачиваемой услуги');
													}
												}
											} else {
												throw new Exception('Не определено поле суммы пополнения баланса');
											}
										}
									} else {
										throw new Exception('Не определено поле даты пополнения баланса');
									}
								} else {
									throw new Exception('Индентифиикатор платежа пустой');
								}
							} else {
								throw new Exception('Не определен уникальный индентифиикатор платежа');
							}
						} else {
							throw new Exception('Не верное или пустое действие');
						}
					} else {
						throw new Exception('Не определено поле действия');
					}
				}
			} else {
				throw new Exception('Не определено поле лицевого счета');
			}
		} else {
			throw new Exception('Не определено поле токена');
		}
	}



	public function checkAccount($accountNumber) {

		global $dbConn;

		$sql = "SELECT c.lastname, cf_1289 AS debt, fcf.cf_1448 AS street, flat FROM vtiger_flats f 
			INNER JOIN vtiger_flatscf fcf ON fcf.flatsid = f.flatsid
			INNER JOIN vtiger_crmentity crm ON crm.crmid = f.flatsid
			LEFT JOIN vtiger_contactdetails c ON c.contactid = fcf.cf_1235
			LEFT JOIN vtiger_contactscf cf ON cf.contactid = c.contactid
			WHERE fcf.cf_1420 = '$accountNumber' 
			AND crm.deleted = 0";


		$result = $dbConn->query($sql);

		if ($result) {

			if ($result->num_rows == 0) {
				throw new Exception('Абонента с данным лицевым счетом не существует. ЛС - ' . $accountNumber);
			} elseif ($result->num_rows == 1) {

				$row = $result->fetch_assoc();

				$lastname = $row['lastname'];
				$debt = round($row['debt'], 2);
				if ($debt < 0) {
					$lastname = $row['lastname'] . ': Переплата ' . abs($debt) . ' сом';
				} elseif ($debt > 0) {
					$lastname = $row['lastname'] . ': Задолженность ' . $debt . ' сом';
				} else {
					$lastname = $row['lastname'];
				}

				return $lastname;
			} else {
				throw new Exception('В системе оказалось больше одного абонента с данным лицевым счетом. ЛС - ' . $accountNumber);
			}
		} else {
			throw new Exception('При проверке существования абонента с данным лицевым счетом произошла ошибка. ЛС - ' . $accountNumber
				. '. Ошибка MySQL - ' . $dbConn->error);
		}
	}

	public function checkStatusId($accountTxnId) {
		global $dbConn;

		$sql = "SELECT cf_txnid, amount
		FROM sp_payments sp 
		INNER JOIN sp_paymentscf spcf on sp.payid = spcf.payid 
		INNER JOIN vtiger_crmentity vc on vc.crmid = spcf.payid 
		WHERE sp.cf_txnid  = $accountTxnId
		AND vc.deleted = 0";

		$result = $dbConn->query($sql);

		if ($result) {

			if ($result->num_rows == 0) {
				throw new Exception('Оплаты с данным идентификатором платежа не существует. Txn_id - ' . $accountTxnId);
			} elseif ($result->num_rows == 1) {

				$row = $result->fetch_assoc();
				$row['command'] = "Платеж с данным идентификатором сохранен в системе.";
				return $row;
			} else {
				throw new Exception('В системе оказалось больше одной оплаты с данным идентификатором платежа. Txn_id - ' . $accountTxnId);
			}
		} else {
			throw new Exception('При проверке существования оплаты с данным идентификатором платежа произошла ошибка. Txn_id - ' . $accountTxnId
				. '. Ошибка MySQL - ' . $dbConn->error);
		}
	}


	public function makePayment($accountNumber, $txndate, $amount, $txnid, $pay_system, $type_system) {
		global $CRM;

		global $dbConn;
		if ($type_system == '-') {
			$type_system = '';
		} else {
			$sql_services = "select cf_1466 from vtiger_cf_1466 where cf_1466id = '$type_system'";
			$result_services = $dbConn->query($sql_services);
			if ($result_services) {
				$type_system = $result_services->fetch_assoc();
			} else {
				$type_system = '';
			}

		}

		$sql = "SELECT sp.cf_txnid 
		FROM sp_payments sp 
		INNER JOIN sp_paymentscf spcf on sp.payid = spcf.payid 
		INNER JOIN vtiger_crmentity vc on vc.crmid = spcf.payid 
		WHERE sp.cf_txnid  = $txnid
		AND vc.deleted = 0";

		$result = $dbConn->query($sql);

		if ($result->num_rows == 0) {
			try {
				$flat_data = $this->findContactBy($accountNumber);

				$data = array(
					"payer" => $flat_data['contactid'],
					"cf_1295" => $pay_system,
					"pay_date" => $txndate,
					"amount" => $amount,
					"cf_txnid" => $txnid,
					"cf_1466" => $type_system,
					"cf_1416" => $flat_data['flatsid']
				);
				return $CRM->createPayment($data);

			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}

		} else {
			throw new Exception('В системе уже есть оплата с данным идентификатором платежа. Txn_id - ' . $txnid);
		}


	}

	public function findContactBy($accountNumber) {
		require_once 'MyLogger.php';
		$logger = new MyLogger('payment_services/oimo/payments.log');
		global $dbConn;

		$sql = "SELECT vf.flatsid ,vc2.contactid
		FROM vtiger_flatscf vf 
		INNER JOIN vtiger_crmentity vc ON vc.crmid = vf.flatsid 
		INNER JOIN vtiger_contactdetails vc2 ON vc2.contactid = vf.cf_1235 
		INNER JOIN vtiger_crmentity vc3 ON vc3.crmid = vc2.contactid 
		WHERE cf_1420 = $accountNumber AND vc.deleted = 0";

		$result = $dbConn->query($sql);


		if ($result) {

			if ($result->num_rows == 0) {
				throw new Exception('Абонента с данным лицевым счетом не существует. ЛС - ' . $accountNumber);
			} elseif ($result->num_rows == 1) {
				$row = $result->fetch_assoc();
				return $row;
			} else {
				throw new Exception('В системе оказалось больше одного абонента с данным лицевым счетом. ЛС - ' . $accountNumber);
			}
		} else {
			throw new Exception('При поиске абонента по лицевому счету произошла ошибка. ЛС - ' . $accountNumber
				. '. Ошибка MySQL - ' . $dbConn->error);
		}
	}
	public function check_system($payment_token, $system_ip) {
		require_once 'MyLogger.php';
		$logger = new MyLogger('payment_services/oimo/payments.log');
		global $dbConn;

		$sql = "select payer_title from vtiger_pymentssystem vp 
		inner join vtiger_crmentity vc on vp.pymentssystemid = vc.crmid 
		where vc.deleted = 0 and cf_payer_token = '$payment_token' and '$system_ip' in (vp.cf_payer_ip_1, vp.cf_payer_ip_2, vp.cf_payer_ip_3, '92.62.72.168')";

		$result = $dbConn->query($sql);


		if ($result) {

			if ($result->num_rows == 0) {
				$logger->log("Не зарегистрирована платежная система IP $system_ip, Token $payment_token");
				throw new Exception('Вы не являетесь зарегистрированной платежной системой');
			} elseif ($result->num_rows == 1) {
				$row = $result->fetch_assoc();
				return $row;
			} else {
				$logger->log("В системе оказалось больше одной платежной системы с IP $system_ip, Token $payment_token");
				throw new Exception('В системе оказалось больше одной платежной системы с токеном ' . $payment_token);
			}
		} else {
			$logger->log('При поиске платежной системы произошла ошибка. Ошибка MySQL - ' . $dbConn->error);
			throw new Exception('При поиске платежной системы произошла ошибка. Ошибка MySQL - ' . $dbConn->error);
		}
	}
	public function show_service() {

		global $dbConn;

		$sql = "select cf_1466id as id, cf_1466 as service from vtiger_cf_1466";

		$result = $dbConn->query($sql);

		if ($result) {

			if ($result->num_rows == 0) {
				throw new Exception('Сервисы отсутствуют.');
			} else {
				$rows = array();
				while ($row = $result->fetch_assoc()) {
					$rows[] = $row;
				}
				return $rows;
			}
		} else {
			throw new Exception('При поиске сервисов произошла ошибка. Ошибка MySQL - ' . $dbConn->error);
		}
	}
}