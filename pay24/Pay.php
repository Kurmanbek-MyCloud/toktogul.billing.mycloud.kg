<?php

class Pay
{

	public function run()
	{

		$input = file_get_contents('php://input');
		$payment = json_decode($input);

		if (isset($payment->token))
		{

			if ($payment->token == 'aSQ4eYqjKVAr8tvF')
			{

				if (isset($payment->account_number))
				{

					if (empty($payment->account_number))
					{
						throw new Exception('Пустой лицевой счет');
					}

					else
					{

						$accountNumber = $payment->account_number;

						if (isset($payment->action))
						{

							if ($payment->action == 'check')
							{
								return $this->checkAccount($accountNumber);
							}

							elseif ($payment->action == 'pay')
							{

								if (isset($payment->refill_date_time))
								{

									if (empty($payment->refill_date_time))
									{
										throw new Exception('Дата пополнения пуста');
									}

									else
									{

										$refillDateTime = $payment->refill_date_time;

										if (isset($payment->amount))
										{

											if (empty($payment->amount))
											{
												throw new Exception('Сумма пополнения пуста');
											}

											else
											{
												$amount = $payment->amount;
												return $this->makePayment($accountNumber, $refillDateTime, $amount);
											}
										}

										else
										{
											throw new Exception('Не определено поле суммы пополнения баланса');
										}
									}
								}

								else
								{
									throw new Exception('Не определено поле даты пополнения баланса');
								}
							}

							else
							{
								throw new Exception('Не верное или пустое действие');
							}
						}

						else
						{
							throw new Exception('Не определено поле действия');
						}
					}
				}

				else
				{
					throw new Exception('Не определено поле лицевого счета');
				}
			}

			else
			{
				throw new Exception('Не верный токен');
			}
		}

		else
		{
			throw new Exception('Не определено поле токена');
		}
	}



	public function checkAccount($accountNumber)
	{

		global $dbConn;

		$sql = "SELECT contactdetails.firstname, contactdetails.lastname
			FROM vtiger_contactdetails contactdetails
			JOIN vtiger_contactscf contactscf ON contactscf.contactid = contactdetails.contactid
			JOIN vtiger_crmentity crmentity ON crmentity.crmid = contactdetails.contactid
			WHERE contactscf.cf_1225 = $accountNumber AND crmentity.deleted = 0";

		$result = $dbConn->query($sql);

		if ($result)
		{

			if ($result->num_rows == 0)
			{
				throw new Exception('Абонента с данным лицевым счетом не существует. ЛС - '.$accountNumber);
			}

			elseif ($result->num_rows == 1)
			{

				$row = $result->fetch_assoc();

				$firstname = $row['firstname'];
				$lastname = $row['lastname'];

				return $firstname.' '.$lastname;
			}

			else
			{
				throw new Exception('В системе оказалось больше одного абонента с данным лицевым счетом. ЛС - '.$accountNumber);
			}
		}

		else
		{
			throw new Exception('При проверке существования абонента с данным лицевым счетом произошла ошибка. ЛС - '.$accountNumber
				.'. Ошибка MySQL - '.$dbConn->error);
		}

	}


	public function makePayment($accountNumber, $refillDateTime, $amount)
	{

		global $dbConn, $logger;

		$payid = $this->getNewCrmId();
		$dateTime = date('Y-m-d H:i:s');
		$contactid = $this->findContactBy($accountNumber);

		$sql1 = "insert into vtiger_crmentity (
			crmid, smcreatorid, smownerid, modifiedby, 
			setype, description, createdtime, modifiedtime, 
			viewedtime, status, version, presence,
			deleted, smgroupid, source, label
			) values (
			$payid, 1, 1, 1, 
			'SPPayments', NULL, '$dateTime', '$dateTime', 
			NULL, NULL, 0, 1,
			0, 0, 'pay24', '-'
			)";

		$sql2 = "insert into sp_payments (
			payid, pay_no, pay_date, pay_details, 
			pay_type, payer, doc_no, related_to, 
			type_payment, amount, spstatus, debit, 
			coracc_subacc, analytics_code, target_code, spcompany, 
			tags
			) values (
			$payid, '-', '$refillDateTime', '',
			'Receipt', $contactid, 0, 0,
			'Cashless Transfer', $amount, 'Executed', '',
			'', '', '', 'Default',
			''
			)";

		$sql3 = "insert into sp_paymentscf (payid, cf_1295) values ($payid, 'Pay24')";

		$result1 = $dbConn->query($sql1);
		$result2 = $dbConn->query($sql2);
		$result3 = $dbConn->query($sql3);


		if ($result1 && $result2 && $result3)
		{

			$sql4 = "update vtiger_contactdetails set balance = balance + $amount where contactid = $contactid";
			$result4 = $dbConn->query($sql4);

			if ($result4)
			{

				$logger->log('Пополнение баланса прошло успешно'
					.'. ЛС - '.$accountNumber.', дата пополнения - '.$refillDateTime.', сумма - '.$amount
					.'. ID платежа - '.$payid.', ID контакта - '.$contactid);

				return 'Пополнение баланса прошло успешно';
			}

			else
			{
				throw new Exception('Не удалось обновить баланс абонента. Ошибка MySQL - '.$dbConn->error
					.'. ЛС - '.$accountNumber.', дата пополнения - '.$refillDateTime.', сумма - '.$amount
					.'. ID платежа - '.$payid.', ID контакта - '.$contactid);
			}
		}

		else
		{
			throw new Exception('Не удалось создать платеж. Ошибка MySQL - '.$dbConn->error
				.'. ЛС - '.$accountNumber.', дата пополнения - '.$refillDateTime.', сумма - '.$amount
				.'. ID платежа - '.$payid.', ID контакта - '.$contactid);
		}
	}


	public function getNewCrmId()
	{

		global $dbConn;

		$sql = "update vtiger_crmentity_seq set id = @id := id + 1";

		$result = $dbConn->query($sql);

		if ($result)
		{

			$result = $dbConn->query("select @id");

			if ($result)
			{
				$row = $result->fetch_assoc();
				return $row['@id'];
			}

			else
			{
				throw new Exception('Не удалось получить новый crmid. Ошибка MySQL - '.$dbConn->error);
			}
		}

		else
		{
			throw new Exception('Не удалось инкрементировать crmentity_seq. Ошибка MySQL - '.$dbConn->error);
		}
	}


	public function findContactBy($accountNumber)
	{

		global $dbConn;

		$sql = "select contactdetails.contactid
			from vtiger_contactdetails contactdetails
			join vtiger_contactscf contactscf on contactscf.contactid = contactdetails.contactid
			join vtiger_crmentity crmentity on crmentity.crmid = contactdetails.contactid
			where contactscf.cf_1225 = $accountNumber and crmentity.deleted = 0";

		$result = $dbConn->query($sql);

		if ($result)
		{

			if ($result->num_rows == 0)
			{
				throw new Exception('Абонента с данным лицевым счетом не существует. ЛС - '.$accountNumber);
			}

			elseif ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				return $row['contactid'];
			}

			else
			{
				throw new Exception('В системе оказалось больше одного абонента с данным лицевым счетом. ЛС - '.$accountNumber);
			}
		}

		else
		{
			throw new Exception('При поиске абонента по лицевому счету произошла ошибка. ЛС - '.$accountNumber
				.'. Ошибка MySQL - '.$dbConn->error);
		}
	}

