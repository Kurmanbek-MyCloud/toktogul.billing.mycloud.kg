<?php

/*
 * Данный обработчик создает счета и оплату к ним
 *
 * Он должен запускаться 11 числа и сверять показания, которые были с 1 по 10 число
 * прошлого месяца с показаниями с 1 по 10 число текущего месяца, если услуга имеет счетчик
 *
 * */

function CreateInvoice($wsEntity)
{

	global $adb;

    $wsId = $wsEntity->getId();
    $module = $wsEntity->getModuleName();

    if (empty($wsId) || empty($module))
    {
    	writeLog('Не удалось получить wsId и module квартиры');
    	return;
	}

    $flatId = vtws_getCRMEntityId($wsId);

	if ($flatId <= 0)
	{
		writeLog('Не удалось получить ID квартиры');
		return;
	}


	$from = date('Y-m-d 00:00:00', strtotime('first day of this month'));
	$to = date('Y-m-d H:i:s');


	$sql = "select invcf.invoiceid 
		from vtiger_invoicecf invcf
		join vtiger_crmentity ent on ent.crmid = invcf.invoiceid
		where ent.deleted = 0 
		and ent.createdtime >= '$from' 
		and ent.createdtime <= '$to' 
		and invcf.cf_1265 = $flatId";

	$result = $adb->run_query_allrecords($sql);

	if ($result)
	{
		writeLog('Счет на этот месяц уже существует. ID квартиры - '.$flatId);
		return;
	}


    try
	{
		$invoice = new MyInvoice();
		$invoice->create($flatId);
		$invoiceId = $invoice->getId();
		$invoiceTotal = $invoice->getTotal();
		writeLog('Счет успешно создан. ID счета - '.$invoiceId.', ID квартиры - '.$flatId);

		$payment = new MyPayment();
		$payment->create($invoiceId, $invoiceTotal, $flatId);
		writeLog('Платеж успешно создан. ID платежа - '.$payment->getId().', ID счета - '.$invoiceId);
	}

	catch (Exception $e)
	{
		writeLog($e->getMessage());
	}
}


function writeLog($message)
{
	$text = date('Y-m-d H:i:s').': '.$message."\n";
	$open = fopen('invoice.log','a');
	fwrite($open, $text);
	fclose($open);
}



class MyInvoice
{


	public $id = 0;
	public $total = 0;


	public function getId()
	{
		return $this->id;
	}


	public function getTotal()
	{
		return $this->total;
	}


	public function create($flatId)
    {

        $invoice = Vtiger_Record_Model::getCleanInstance('Invoice');
        $invoice->set('assigned_user_id', 1);
        $invoice->set('subject', 'Счет');
        $invoice->set('cf_1265', $flatId);
        $invoice->set('mode', 'create');
        $invoice->save();

        $invoiceId = $invoice->getId();

        if ($invoiceId > 0)
        {

            $lineItems = $this->getLineItems($flatId, $invoiceId);

            $total = 0;

            foreach ($lineItems as $item)
            {
                $this->insertIntoInventory($item);
                $total += $item['margin'];
            }

            $this->updateInvoice($total, $invoiceId);

            $this->id = $invoiceId;
            $this->total = $total;

            return $invoice;
        }

        else
		{
			throw new Exception('Не удалось создать счет. Метод create(). ID квартиры - '.$flatId);
		}
    }


    public function getLineItems($flatId, $invoiceId)
    {

        $lineItems = array();

        $servicesData = $this->getServicesData($flatId, $invoiceId);

        $seq = 1;

        foreach ($servicesData as $data)
        {

			$lineItems[] = array(
                'id' => $invoiceId,
                'productid' => $data['serviceId'],
                'sequence_no' => $seq++,
                'quantity' => $data['quantity'],
                'listprice' => $data['price'],
                'discount_percent' => null,
                'discount_amount' => null,
                'comment' => '',
                'description' => null,
                'incrementondel' => 1,
                'tax1' => null,
                'tax2' => null,
                'tax3' => null,
                'image' => null,
				'purchase_cost' => 0,
				'margin' => $data['total'],
				'producttotal' => 0,
				'netprice' => 0,
				'netotal' => 0,
				'shipping_handling_charge' => 0,
				'pre_tax_total' => null,
				'tax_final' => 0,
				'charge_tax_total' => 0,
				'deduct_tax_total_amount' => 0,
				'grand_total' => 0,
				'accrual_base' => $data['accrualBase'],
				'previous_reading' => $data['prevData'],
				'current_reading' => $data['currData']
            );
        }

        return $lineItems;
    }


    public function getServicesData($flatId, $invoiceId)
    {

        global $adb;

        $sql = "select s.serviceid, s.unit_price, scf.cf_1297
            from vtiger_crmentityrel cer
            join vtiger_crmentity ce on ce.crmid = cer.relcrmid
            join vtiger_service s on s.serviceid = cer.relcrmid
            join vtiger_servicecf scf on scf.serviceid = cer.relcrmid
            where cer.crmid = $flatId
            and cer.relmodule = 'Services'
            and ce.deleted = 0";

        $result = $adb->run_query_allrecords($sql);

        if ($result)
		{

			$servicesData = array();
			$serviceList = array();

			foreach ($result as $row)
			{

				$serviceId = $row['serviceid'];
				$price = $row['unit_price'];
				$accrualBase = $row['cf_1297'];
				$prevData = 0;
				$currData = 0;
				$quantity = 1;

				if (in_array($serviceId, $serviceList))
				{
					throw new Exception('Работа скрипта прервана, так как квартира имеет'
						.' несколько одинаковых услуг. Метод getServicesData().'
						.' ID квартиры - '.$flatId.', ID счета - '.$invoiceId.', ID услуги - '.$serviceId);
				}
				else
				{
					$serviceList[] = $serviceId;
				}


				if ($accrualBase == 'Счетчик')
				{
					$metersData = $this->getMetersData($flatId, $serviceId, $invoiceId);
					$prevData = $metersData['prevData'];
					$currData = $metersData['currData'];
					$quantity = $currData - $prevData;
				}
				elseif ($accrualBase == 'Количество проживающих')
				{
					$quantity = $this->getTenantQuantity($flatId, $invoiceId);
				}
				elseif ($accrualBase == 'Площадь квартиры')
				{
					$quantity = $this->getFlatArea($flatId, $invoiceId);
				}

				$total = $price * $quantity;

				$servicesData[] = array(
					'serviceId' => $serviceId,
					'accrualBase' => $accrualBase,
					'prevData' => $prevData,
					'currData' => $currData,
					'quantity' => $quantity,
					'price' => $price,
					'total' => $total
				);
			}

			return $servicesData;
		}

		else
		{
			throw new Exception('Не удалось получить услуги. Метод getServicesData().'
				.' ID квартиры - '.$flatId.', ID счета - '.$invoiceId);
		}
    }



    public function getMetersData($flatId, $serviceId, $invoiceId)
    {

        global $adb;

        $sql = "select m.metersid 
            from vtiger_meters m
            join vtiger_meterscf mcf on mcf.metersid = m.metersid
            join vtiger_crmentity ce on ce.crmid = m.metersid
            where mcf.cf_1319 = $flatId
            and mcf.cf_1321 = $serviceId
            and ce.deleted = 0";

        $result = $adb->run_query_allrecords($sql);

		if (count($result) == 1)
		{
			$meterId = $result[0]['metersid'];
			return $this->getPrevCurrData($meterId, $invoiceId);
		}
		else
		{
			throw new Exception('При запросе счетчика мы получили количество счетчиков не равное 1.'
				.' Метод getMetersData(). ID квартиры - '.$flatId.', ID услуги - '.$serviceId.', ID счета - '.$invoiceId);
		}
    }


    public function getPrevCurrData($meterId, $invoiceId)
    {

        global $adb;

        $prevFrom = date('Y-m-d', strtotime('first day of previous month'));
        $prevTo = date('Y-m-d', strtotime('+9 days', strtotime('first day of previous month')));

        $prevSql = "select md.data
            from vtiger_metersdata md
            join vtiger_metersdatacf mdcf on mdcf.metersdataid = md.metersdataid
            join vtiger_crmentity ce on ce.crmid = md.metersdataid
            where ce.deleted = 0 
            and mdcf.cf_1317 = $meterId 
            and cf_1325 >= '$prevFrom' 
            and cf_1325 <= '$prevTo'";

        $prevResult = $adb->run_query_allrecords($prevSql);

		if (count($prevResult) == 1)
		{
			$prevData = $prevResult[0]['data'];
		}
		else
		{
			throw new Exception('Запрос на предыдущее показание счетчика дал не корректный результат.'.
				' Метод getPrevCurrData(). ID счетчика - '.$meterId.', ID счета - '.$invoiceId);
		}


        $currFrom = date('Y-m-d', strtotime('first day of this month'));
        $currTo = date('Y-m-d', strtotime('+9 days', strtotime('first day of this month')));

        $currSql = "select md.data
            from vtiger_metersdata md
            join vtiger_metersdatacf mdcf on mdcf.metersdataid = md.metersdataid
            join vtiger_crmentity ce on ce.crmid = md.metersdataid
            where ce.deleted = 0 
            and mdcf.cf_1317 = $meterId 
            and cf_1325 >= '$currFrom' 
            and cf_1325 <= '$currTo'";

        $currResult = $adb->run_query_allrecords($currSql);

        if (count($currResult) == 1)
		{
			$currData = $currResult[0]['data'];
		}
		else
		{
			throw new Exception('Запрос на текущее показание счетчика дал не корректный результат.'
				.' Метод getPrevCurrData(). ID счетчика - '.$meterId.', ID счета - '.$invoiceId);
		}


		if ($prevData && $currData && ($prevData < $currData))
		{
			return array('prevData' => $prevData, 'currData' => $currData);
		}
		else
		{
			throw new Exception('Не корректные данные счетчиков. Метод getPrevCurrData().'
				.' Предыдущее показание - '.$prevData.', текущее показание - '.$currData
				.', ID счетчика - '.$meterId.', ID счета - '.$invoiceId);
		}
    }


    public function getTenantQuantity($flatId, $invoiceId)
	{

		global $adb;

		$sql = "select concf.contactid 
			from vtiger_contactscf concf
			join vtiger_crmentity ent on ent.crmid = concf.contactid
			where concf.cf_1259 = $flatId and ent.deleted = 0";

		$result = $adb->run_query_allrecords($sql);

		if (count($result) > 0)
		{
			return count($result);
		}
		else
		{
			throw new Exception('Не удалось получить жильцов квартиры. Метод getTenantQuantity().'
				.' ID квартиры - '.$flatId.', ID счета - '.$invoiceId);
		}
	}


	public function getFlatArea($flatId, $invoiceId)
	{

		$flat = Vtiger_Record_Model::getInstanceById($flatId);
		$area = $flat->get('cf_1255');

		if ($area > 0)
		{
			return $area;
		}
		else
		{
			throw new Exception('Скрипт был прерван, так как площадь квартиры имеет не корректное значение.'
				.'Метод getFlatArea(). ID квартиры - '.$flatId.', ID счета - '.$invoiceId);
		}

	}



    public function insertIntoInventory($item)
    {

        global $adb;

        $invoiceId = $item['id'];
        $serviceId = $item['productid'];
        $sequenceNo = $item['sequence_no'];
        $quantity = $item['quantity'];
        $listPrice = $item['listprice'];
        $discountPercent = $item['discount_percent'];
        $discountAmount = $item['discount_amount'];
        $comment = $item['comment'];
        $description = $item['description'];
        $incrementOnDel = $item['incrementondel'];
        $tax1 = $item['tax1'];
        $tax2 = $item['tax2'];
        $tax3 = $item['tax3'];
        $image = $item['image'];
        $purchaseCost = $item['purchase_cost'];
        $margin = $item['margin'];
        $productTotal = $item['producttotal'];
        $netPrice = $item['netprice'];
        $netTotal = $item['nettotal'];
        $shippingHandlingCharge = $item['shipping_handling_charge'];
        $preTaxTotal = $item['pre_tax_total'];
        $taxFinal = $item['tax_final'];
        $chargeTaxTotal = $item['charge_tax_total'];
        $deductTaxTotalAmount = $item['deduct_tax_total_amount'];
        $grandTotal = $item['grand_total'];
        $accrualBase = $item['accrual_base'];
        $previousReading = $item['previous_reading'];
        $currentReading = $item['current_reading'];


        $sql = "insert into vtiger_inventoryproductrel (

            id,
            productid,
            sequence_no,
            quantity,
            listprice,
            discount_percent,
            discount_amount,
            comment,
            description,
            incrementondel,
            tax1,
            tax2,
            tax3,
            image,
            purchase_cost,
            margin,
            producttotal,
            netprice,
            nettotal,
            shipping_handling_charge,
            pre_tax_total,
            tax_final,
            charge_tax_total,
            deduct_tax_total_amount,
            grand_total,
            accrual_base,
            previous_reading,
            current_reading
            
            ) values (
            
            '$invoiceId',
            '$serviceId',
            '$sequenceNo',
            '$quantity',
            '$listPrice',
            '$discountPercent',
            '$discountAmount',
            '$comment',
            '$description',
            '$incrementOnDel',
            '$tax1',
            '$tax2',
            '$tax3',
            '$image',
            '$purchaseCost',
            '$margin',
            '$productTotal',
            '$netPrice',
            '$netTotal',
            '$shippingHandlingCharge',
            '$preTaxTotal',
            '$taxFinal',
            '$chargeTaxTotal',
            '$deductTaxTotalAmount',
            '$grandTotal',
            '$accrualBase',
            '$previousReading',
            '$currentReading'

            )";


        $result = $adb->query($sql);

        if ($result === false)
		{
			throw new Exception('Не удалось прикрепить услугу. Метод insertIntoInventory().'
				.' ID услуги - '.$serviceId.', номер строки - '.$sequenceNo.', ID счета - '.$invoiceId);
		}
    }


    public function updateInvoice($total, $invoiceId)
	{

		global $adb;

		$sql = "update vtiger_invoice 
			set subtotal = $total, 
			total = $total, 
			pre_tax_total = $total, 
			balance = $total,
			taxtype = 'group_tax_inc',
			currency_id = 1,
			conversion_rate = 1,
			compound_taxes_info = '[]'
			where invoiceid = $invoiceId";

		$result = $adb->query($sql);

		if ($result === false)
		{
			throw new Exception('Не удалось установить общий итог для счета.'
				.' Метод updateInvoice(). ID счета - '.$invoiceId);
		}
	}
}


class MyPayment
{

	public $id = 0;


	public function getId()
	{
		return $this->id;
	}


	public function create($invoiceId, $invoiceTotal, $flatId)
	{

		$payment = Vtiger_Record_Model::getCleanInstance('SPPayments');
		$payment->set('assigned_user_id', 1);
		$payment->set('related_to', $invoiceId);
		$payment->set('pay_date', date('Y-m-d'));
		$payment->set('pay_type', 'Expense');
		$payment->set('amount', $invoiceTotal);
		$payment->set('mode', 'create');
		$payment->save();

		if ($payment->getId() > 0)
		{
			$this->id = $payment->getId();
			return $payment;
		}

		else
		{
			throw new Exception('Не удалось создать платеж. Метод create(). ID счета - '.$invoiceId);
		}
	}


	public function getOwnerBalance()
	{
		
	}
}






