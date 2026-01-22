<?php
namespace OimoV2;
class Pay {
	public function run() {
		$payment = $this->getPaymentData();
		$pay_system = $this->checkSystem($payment);

		switch ($payment->command) {
			case 'check':
				return $this->checkAccount($payment->account);
			case 'pay':
				$this->validatePayment($payment);
				return $this->makePayment($payment, $pay_system);
			case 'show_service':
				return $this->showService();
			case 'check_pay_status':
				return $this->checkPaymentStatus($payment);
			default:
				throw new PaymentException('Неверная команда');
		}
	}

	private function getPaymentData() {
		$input = file_get_contents('php://input');
		$payment = json_decode($input);
		if (empty($payment->token)) {
			throw new PaymentException('Не определено поле токена');
		}

		if (empty($payment->command)) {
			throw new PaymentException('Не определено поле команды');
		}

		return $payment;
	}
	private function checkSystem($payment) {

		$payment_token = $payment->token;
		$system_ip = $_SERVER['REMOTE_ADDR'];

		return $this->check_system($payment_token, $system_ip);
	}
	private function validatePayment($payment) {
		$this->validateProperty($payment, 'txn_id', 'Индентификатор платежа пустой');
		$this->validateProperty($payment, 'txn_date', 'Не определено поле даты');
		$this->validateProperty($payment, 'sum', 'Не определено поле суммы');
		$this->validateProperty($payment, 'service', 'Не определен тип оплачиваемой услуги');

		if (strtotime($payment->txn_date) === false) {
			throw new PaymentException("Неправильный формат даты");
		}
		if (empty($payment->account)) {
			throw new PaymentException('Пустой лицевой счет');
		}

	}
	private function validateProperty($payment, $property, $message) {
		if (empty($payment->$property)) {
			throw new PaymentException($message);
		}
	}
	private function makePayment($payment, $pay_system) {
		if ($payment->service == '-') {
			$type_system = '';
		} else {
			$type_system = $this->getServiceType($payment->service);
		}
		$this->checkDuplicatePayment($payment->txn_id);

		$estate_data = $this->findContactBy($payment->account);
		$data = $this->preparePaymentData($payment, $pay_system, $type_system, $estate_data);

		return $this->processPayment($data);
	}
	private function getServiceType($service_id) {
		global $dbConn;
		$sql = "SELECT cf_1466 FROM vtiger_cf_1466 WHERE cf_1466id = ?";
		$stmt = $dbConn->prepare($sql);
		if (!$stmt) {
			throw new DatabaseException("Ошибка подготовки запроса: " . $dbConn->error);
		}
		$stmt->bind_param("i", $service_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			throw new PaymentException("Указанная услуга не найдена");
		}
		$row = $result->fetch_assoc();
		return $row['cf_1466'];
	}

	private function checkDuplicatePayment($txn_id) {
		global $dbConn;
		$sql = "SELECT sp.cf_txnid FROM sp_payments sp
            INNER JOIN sp_paymentscf spcf on sp.payid = spcf.payid 
            INNER JOIN vtiger_crmentity vc on vc.crmid = spcf.payid 
            WHERE vc.deleted = 0 AND sp.cf_txnid = ?";

		$stmt = $dbConn->prepare($sql);
		if (!$stmt) {
			throw new DatabaseException('Ошибка подготовки запроса: ' . $dbConn->error);
		}
		$stmt->bind_param('s', $txn_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			throw new PaymentException('В системе уже есть оплата с данным идентификатором платежа. Txn_id - ' . $txn_id);
		}
		$stmt->close();
	}

	private function preparePaymentData($payment, $pay_system, $type_system, $estate_data) {
		// Формируем массив данных для старой структуры
		return array(
			"payer" => $estate_data['contactid'],
			"cf_1295" => $pay_system, // pay_system может быть массивом, если что — возьми нужное поле
			"pay_date" => $payment->txn_date, // или $payment->pay_date, если приходит так
			"amount" => $payment->sum,
			"cf_txnid" => $payment->txn_id,
			"cf_1466" => $type_system,
			"cf_1416" => $estate_data['flatsid']
		);
	}
	private function processPayment($data) {
		global $CRM;
		return $CRM->createPayment($data);
	}
	public function findContactBy($accountNumber) {
		global $dbConn;
		$sql = "SELECT vf.flatsid, vc2.contactid
        FROM vtiger_flatscf vf 
        INNER JOIN vtiger_crmentity vc ON vc.crmid = vf.flatsid 
        INNER JOIN vtiger_contactdetails vc2 ON vc2.contactid = vf.cf_1235 
        INNER JOIN vtiger_crmentity vc3 ON vc3.crmid = vc2.contactid 
        WHERE vf.cf_1420 = ? AND vc.deleted = 0";
		$stmt = $dbConn->prepare($sql);
		if (!$stmt) {
			throw new DatabaseException('Ошибка подготовки запроса: ' . $dbConn->error);
		}
		$stmt->bind_param("s", $accountNumber);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 0) {
			$stmt->close();
			throw new PaymentException('Абонента с данным лицевым счетом не существует. ЛС - ' . $accountNumber);
		} elseif ($result->num_rows > 1) {
			$stmt->close();
			throw new PaymentException('В системе оказалось больше одного абонента с данным лицевым счетом. ЛС - ' . $accountNumber);
		}
		$row = $result->fetch_assoc();
		$stmt->close();
		return $row;
	}

	private function check_system($payment_token, $system_ip) {
		global $dbConn;
		$sql = "SELECT payer_title FROM vtiger_pymentssystem vp 
        INNER JOIN vtiger_crmentity vc ON vp.pymentssystemid = vc.crmid 
        WHERE vc.deleted = 0 AND cf_payer_token = ? AND ? IN (vp.cf_payer_ip_1, vp.cf_payer_ip_2, vp.cf_payer_ip_3, '92.62.72.168')";
		$stmt = $dbConn->prepare($sql);
		if (!$stmt) {
			throw new DatabaseException('Ошибка подготовки запроса: ' . $dbConn->error);
		}
		$stmt->bind_param("ss", $payment_token, $system_ip);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 0) {
			throw new DatabaseException("Не зарегистрированная платежная система IP $system_ip, Token $payment_token");
		} elseif ($result->num_rows > 1) {
			throw new DatabaseException('В системе оказалось больше одной платежной системы с токеном - ' . $payment_token . 'IP - ' . $system_ip);
		}
		$data = $result->fetch_assoc();
		$stmt->close();
		return $data;
	}

	private function showService() {
		global $dbConn;

		$sql = "SELECT cf_1466id as id, cf_1466 as service FROM vtiger_cf_1466";

		$result = $dbConn->query($sql);

		if ($result) {

			if ($result->num_rows == 0) {
				throw new PaymentException('Услуги отсутствуют');
			} else {
				$rows = array();
				while ($row = $result->fetch_assoc()) {
					$rows[] = $row;
				}
				return $rows;
			}
		} else {
			throw new DatabaseException('При поиске услуг произошла ошибка. Ошибка MySQL - ' . $dbConn->error);
		}
	}
	private function checkAccount($accountNumber) {
		global $dbConn;

		if (empty($accountNumber)) {
			throw new PaymentException('Пустой лицевой счет');
		}

		$sql = "SELECT c.lastname, cf_1289 AS debt, fcf.cf_1448 AS street, flat FROM vtiger_flats f 
	INNER JOIN vtiger_flatscf fcf ON fcf.flatsid = f.flatsid
	INNER JOIN vtiger_crmentity crm ON crm.crmid = f.flatsid
	LEFT JOIN vtiger_contactdetails c ON c.contactid = fcf.cf_1235
	LEFT JOIN vtiger_contactscf cf ON cf.contactid = c.contactid
	WHERE fcf.cf_1420 = ? 
	AND crm.deleted = 0";
		$stmt = $dbConn->prepare($sql);
		$stmt->bind_param("s", $accountNumber);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows == 0) {
			$stmt->close();
			throw new PaymentException('Абонента с данным лицевым счетом не существует. ЛС - ' . $accountNumber);
		} elseif ($result->num_rows > 1) {
			$stmt->close();
			throw new PaymentException('В системе оказалось больше одного абонента с данным лицевым счетом. ЛС - ' . $accountNumber);
		}

		$row = $result->fetch_assoc();
		$stmt->close();
		$lastname = $row['lastname'];
		$debt = round($row['debt'], 2);
		if ($debt < 0) {
			$lastname .= ': Переплата ' . abs($debt) . ' сом';
		} elseif ($debt > 0) {
			$lastname .= ': Задолженность ' . $debt . ' сом';
		}

		return $lastname;
	}

	public function checkPaymentStatus($payment) {
		global $dbConn;
		if (empty($payment->txn_id)) {
			throw new PaymentException('Индентификатор платежа пустой');
		}
		$sql = "SELECT sp.amount,  fcf.cf_1420 as estate_number, c.lastname, sp.pay_date as cf_pay_date, sp.cf_txnid
        FROM sp_payments sp
        INNER JOIN sp_paymentscf spcf on sp.payid = spcf.payid
        INNER JOIN vtiger_flatscf fcf ON spcf.cf_1416 = fcf.flatsid
        LEFT JOIN vtiger_contactdetails c ON fcf.cf_1235 = c.contactid
        INNER JOIN vtiger_crmentity vc ON sp.payid = vc.crmid
        WHERE vc.deleted = 0 AND sp.cf_txnid = ?";
		$stmt = $dbConn->prepare($sql);
		if (!$stmt) {
			throw new DatabaseException("Ошибка подготовки запроса: " . $dbConn->error);
		}
		$stmt->bind_param("s", $payment->txn_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 0) {
			throw new PaymentException('Оплата с данным идентификатором платежа не найдена. Txn_id - ' . $payment->txn_id);
		} elseif ($result->num_rows > 1) {
			$stmt->close();
			throw new PaymentException('Обнаружено более одного платежа с таким же идентификатором. Txn_id - ' . $payment->txn_id);
		}
		$data = $result->fetch_assoc();
		$stmt->close();
		$data['action'] = "check_pay_status";
		$data['comment'] = "Платеж с данным идентификатором сохранен в системе.";
		return $data;
	}

}
