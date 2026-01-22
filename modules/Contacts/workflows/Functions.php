<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
 // ini_set('display_errors','on'); error_reporting(E_ALL); // STRICT DEVELOPMENT
include_once 'modules/Invoice/Invoice.php';

function Contacts_sendCustomerPortalLoginDetails2($entityData){
	$adb = PearDatabase::getInstance();
	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];
	$entityDelta = new VTEntityDelta();
	$email = $entityData->get('email');

	$isEmailChanged = $entityDelta->hasChanged($moduleName, $entityId, 'email') && $email;//changed and not empty
	$isPortalEnabled = $entityData->get('portal') == 'on' || $entityData->get('portal') == '1';

	if ($isPortalEnabled) {
		//If portal enabled / disabled, then trigger following actions
		$sql = "SELECT id, user_name, user_password, isactive FROM vtiger_portalinfo WHERE id=?";
		$result = $adb->pquery($sql, array($entityId));

		$insert = true;
		if ($adb->num_rows($result)) {
			$insert = false;
			$dbusername = $adb->query_result($result,0,'user_name');
			$isactive = $adb->query_result($result,0,'isactive');
			if($email == $dbusername && $isactive == 1 && !$entityData->isNew()){
				$update = false;
			} else if($isPortalEnabled) {
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=? WHERE id=?";
				$adb->pquery($sql, array($email, 1, $entityId));
				$update = true;
			} else {
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=? WHERE id=?";
				$adb->pquery($sql, array($email, 0, $entityId));
				$update = false;
			}
		}

		//generate new password
		$password = makeRandomPassword();
		$enc_password = Vtiger_Functions::generateEncryptedPassword($password);

		//create new portal user
		$sendEmail = false;
		if ($insert) {
			$sql = "INSERT INTO vtiger_portalinfo(id,user_name,user_password,cryptmode,type,isactive) VALUES(?,?,?,?,?,?)";
			$params = array($entityId, $email, $enc_password, 'CRYPT', 'C', 1);
			$adb->pquery($sql, $params);
			$sendEmail = true;
		}

		//update existing portal user password
		if ($update && $isEmailChanged) {
			$sql = "UPDATE vtiger_portalinfo SET user_password=?, cryptmode=? WHERE id=?";
			$params = array($enc_password, 'CRYPT', $entityId);
			$adb->pquery($sql, $params);
			$sendEmail = true;
		}

		//trigger send email
		if ($sendEmail && $entityData->get('emailoptout') == 0) {
			global $current_user,$HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
			require_once("modules/Emails/mail.php");
			$emailData = Contacts::getPortalEmailContents($entityData,$password,'LoginDetails');
			$subject = $emailData['subject'];
			if(empty($subject)) {
				$subject = 'Customer Portal Login Details';
			}

			$contents = $emailData['body'];
			$contents= decode_html(getMergedDescription($contents, $entityId, 'Contacts'));
			if(empty($contents)) {
				require_once 'config.inc.php';
				global $PORTAL_URL;
				$contents = 'LoginDetails';
				$contents .= "<br><br> User ID : $email";
				$contents .= "<br> Password: ".$password;
				$portalURL = vtranslate('Ссылка на портал ',$moduleName).'<a href="'.$PORTAL_URL.'" style="font-family:Arial, Helvetica, sans-serif;font-size:13px;">'. vtranslate('перейти', $moduleName).'</a>';
				$contents .= "<br>".$portalURL;
			}
			$subject = decode_html(getMergedDescription($subject, $entityId,'Contacts'));
			send_mail('Contacts', $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents,'','','','','',true);
		}
	} else {
		$sql = "UPDATE vtiger_portalinfo SET user_name=?,isactive=0 WHERE id=?";
		$adb->pquery($sql, array($email, $entityId));
	}
}

function setPersonalAccount($entityData){
	$db = PearDatabase::getInstance();

	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];
	$entityDelta = new VTEntityDelta();
	$ls = $entityData->get('cf_1225');

	if ( empty($ls) ) {
		$quer = $db->pquery("SELECT id FROM paccount_last_id Limit 1");
	  	$qwer = $db->fetchByAssoc($quer);
		$id = $qwer['id'];
		$new = (int) $id + 1;

			$newls = str_pad($id, 6, "0", STR_PAD_LEFT);
			$db->pquery("UPDATE paccount_last_id set id='$new' limit 1");
			$db->pquery("UPDATE vtiger_contactscf set cf_1225='2$newls' WHERE contactid=$entityId");
	}
	
}
function russian_date($month){
		// $date=explode(".", date("d.m.Y"));
		switch ($month){
		case 1: $m='январь'; break;
		case 2: $m='февраль'; break;
		case 3: $m='март'; break;
		case 4: $m='апрель'; break;
		case 5: $m='май'; break;
		case 6: $m='июнь'; break;
		case 7: $m='июль'; break;
		case 8: $m='август'; break;
		case 9: $m='сентябрь'; break;
		case 10: $m='октябрь'; break;
		case 11: $m='ноябрь'; break;
		case 12: $m='декабрь'; break;
		}
		// echo $date[0].'&nbsp;'.$m.'&nbsp;'.$date[2];
		return $m;
}
public function test ($ws_entity){

		echo "sdfsdf";
		exit();

}
function greateInvoice($ws_entity){
	
	// echo 'test';
	// exit();
	// WS id
	$ws_id = $ws_entity->getId();
	// var_dump($ws_id);
	$module = $ws_entity->getModuleName();
	if (empty($ws_id) || empty($module)) {
		return;
	}
        // CRM id
        $crmid = vtws_getCRMEntityId($ws_id);
        if ($crmid <= 0) {
            return;
        }

        // era
        $db = PearDatabase::getInstance();
    
        //получение объекта со всеми данными о текущей записи Модуля "MyModule"
        $myModuleInstance = Vtiger_Record_Model::getInstanceById($crmid); 
        $assigned_user_id = $myModuleInstance->get('assigned_user_id');

        $sql = "SELECT * FROM vtiger_flats as a inner join vtiger_crmentity as b ON a.flatsid=b.crmid 
        						inner join vtiger_flatscf as c ON b.crmid=c.flatsid where b.deleted=0 and c.cf_1235=$crmid";   
			$quer = $db->pquery($sql);
		$res = $db->fetchByAssoc($quer);
		$flatsid = $res['flatsid'];

		$quer2 = $db->pquery("SELECT * FROM vtiger_crmentityrel where crmid='$flatsid' and module='Flats' and relmodule='Services' LIMIT 1");
		$res2 = $db->fetchByAssoc($quer2);

		$serviceid = $res2['relcrmid'];

		if ($serviceid) {
			
			$service = Vtiger_Record_Model::getInstanceById($serviceid, 'Services'); 
			$subject = $service->get('servicename');
			$amount = $service->get('unit_price');


			$today = date('Y-m-d');
			$duedate = date('Y-m-10');
			$daten = date('n', strtotime('-1 month'));
			$daten_year = date('Y', strtotime('-1 month'));
			$datelabel = russian_date($daten);
			$subject = html_entity_decode($subject).' за ('.$datelabel.' '.$daten_year')';

	        $invoice = Vtiger_Record_Model::getCleanInstance('Invoice'); 
	        $invoice->set('subject', $subject);
	        $invoice->set('contact_id', $crmid);
	        $invoice->set('duedate', $duedate);
	        $invoice->set('invoicestatus', 'AutoCreated');
	        $invoice->set('cf_1265', $flatsid);
	        // $invoice->set('hdnSubTotal', $amount);
	        // $invoice->set('hdnGrandTotal', $amount);
	        $invoice->set('total', $amount);
	        $invoice->set('sub_total', $amount);
	        // $invoice->set('pre_tax_total', $amount);
	        $invoice->set('balance', $amount);
	        $invoice->set('assigned_user_id', 1);
	        //$invoice->column_fields['assigned_user_id'] = $current_user->id;
	        $invoice->save();
	        $invoiceid = $invoice->getId();

	        // echo '<pre>';
	        // var_dump($invoice);
	        // echo '</pre>';
	        // die;
	      

	        $db->pquery("insert into test (text) values ('$invoiceid')");

	        if ($invoiceid) {
	        	$db->pquery("UPDATE vtiger_invoice SET total='$amount', subtotal='$amount' WHERE invoiceid='$invoiceid'");

	        	$db->pquery("INSERT INTO vtiger_inventoryproductrel (id,productid,sequence_no,quantity,listprice,incrementondel,margin,producttotal,grand_total) values ('$invoiceid','$serviceid',1,1,'$amount',0,'$amount','$amount','$amount')");
	        }
        }//if
}

?>
