<?php 
function update_invoice_status($ws_entity) {
  // require_once '../includes/Loader.php';
  // require_once 'utils/utils.php';
  // echo'asadsdsdf';
  // echo '<pre>';
	global $adb;
  global $current_user;
  $current_user = Users::getActiveAdminUser();
  $payment_id = explode('x',$ws_entity->getId())[1];
  $contact_id = explode('x',$ws_entity->get('payer'))[1];
  $flat_id = explode('x',$ws_entity->get('cf_1416'))[1];
  
  $payments_summ_sql = $adb->pquery("SELECT round(SUM(amount)) as summ FROM sp_payments as SP
                                  inner join sp_paymentscf pcf on pcf.payid = SP.payid 
                                  INNER JOIN  vtiger_crmentity AS SCE ON SP.payid = SCE.crmid 
                                  WHERE SCE.deleted = 0
                                  AND pay_type = 'Receipt'
                                  AND cf_1416 = ?",array($flat_id));
  
  $payments_summ = $adb->query_result($payments_summ_sql,0,'summ');

  $invoices_sql = $adb->pquery("SELECT * FROM vtiger_invoice AS I
                                    inner join vtiger_invoicecf icf on icf.invoiceid = I.invoiceid
																		INNER JOIN vtiger_crmentity AS CE ON I.invoiceid = CE.crmid
																		WHERE deleted = 0
																		AND invoicestatus not IN ('Cancel')
																		AND contactid=?
																		ORDER BY icf.cf_1265 ASC", array($contact_id));

  // $invoice = CRMEntity::getInstance("Invoice");
  // $invoice->retrieve_entity_info('311445', 'Invoice');;
  // $invoice->id = '311445';
  // $invoice->mode = 'edit';
  // $invoice->column_fields['invoicestatus'] = 'Paid';
  // $invoice->save('Invoice');
  // $invoice->set('invoicestatus','Paid');
  // $invoice->set('productid',631);
  // $invoice->set('listprice',25.00000000);
  // $invoice->set('assigned_user_id',1);
  // $invoice->set('mode','edit');
  // $invoice->save();
  
  $invoice_total = 0 ;
  // var_dump($invoices_sql);
  // var_dump($adb->num_rows($invoices_sql));
  for ($i=0; $i < $adb->num_rows($invoices_sql); $i++) { 
    $invoice_id = $adb->query_result($invoices_sql,$i,'invoiceid');
    $invoice_amount = $adb->query_result($invoices_sql,$i,'total');
    $invoice_status = $adb->query_result($invoices_sql,$i,'invoicestatus');
    $invoice_total += $invoice_amount;
    // var_dump('Cycle');
    if ($invoice_total < $payments_summ && $invoice_status != 'Executed') {
      // var_dump("payment total: $payments_summ | inv total: $invoice_total | inv amount: $invoice_amount ");
      // var_dump($invoice_total);
      // var_dump($invoice_status);//Paid
      // var_dump($invoice_total);
      // var_dump($adb->query_result($payments_summ_sql,0,'summ'));
      // var_dump($ws_entity->get('payer'));
      $adb->pquery("UPDATE vtiger_invoice Set invoicestatus = 'Paid' where invoiceid = ?", array($invoice_id));
      // var_dump($invoice_id);
      // var_dump($invoice_amount);
      // var_dump($invoice_status);
      // var_dump($invoice_total);
      // var_dump('Elementarno');
    }
  }

  // var_dump("Vrode vse");
  // echo '</pre>';
  // exit(); 
	// $q = $adb->pquery("SELECT COUNT(*) as res FROM vtiger_notifications WHERE recordid = ? AND assigned_to = ?", array($recordID, $to));
	// $f = $adb->query_result_rowdata($q, 0);
	// return $f['res'] != 0; 
}