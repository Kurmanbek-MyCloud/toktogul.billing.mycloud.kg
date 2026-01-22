<?php
require_once 'config.php';
require_once 'config.inc.php';
require_once 'user_privileges/user_privileges_1.php';
require_once 'includes/main/WebUI.php';
require_once 'include/utils/utils.php';
require_once 'vtlib/Vtiger/Module.php';
// echo $data;
$current_user = Users::getActiveAdminUser();
$user = Users_Record_Model::getCurrentUserModel();

class CRM{
	public function createFlat($data){
		try{
			global $adb;
			$flats = Vtiger_Record_Model::getCleanInstance("Flats");
			$flats->set("flat",$data['flat']);
			if(!$data['assigned_user_id']){
				return array("success"=>false,"message"=>"Поле ответсвенного не может быть пустым");
			}
			$flats->set("assigned_user_id",$data['assigned_user_id']);
			$flats->set("cf_1420",$data['cf_1420']);
			$flats->set("cf_1261",$data['cf_1261']);
			$flats->set("cf_1235",$data['cf_1235']);
			$flats->set("cf_1444",$data['cf_1444']);
			$flats->set("cf_1448",$data['cf_1448']);
			$flats->set("cf_1450",$data['cf_1450']);
			$flats->set("cf_1452",$data['cf_1452']);
			$flats->set("cf_1478",$data['cf_1478']);
			$flats->set("cf_1446",$data['cf_1446']);


			$flats->set('mode', 'create');
			$flats->save();
			$dataId = $flats->getId();
			return array("success"=>true,"message"=>array("id"=>$dataId));

		}

		catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}

	public function updateFlat($data){
		try{
			global $adb;
			$flats = Vtiger_Record_Model::getInstanceById($data['id']);
			$flats->set("flat",$data['flat']);
			if(!$data['assigned_user_id']){
				return array("success"=>false,"message"=>"Поле ответсвенного не может быть пустым");
			}
			$flats->set("assigned_user_id",$data['assigned_user_id']);
			$flats->set("cf_1420",$data['cf_1420']);
			$flats->set("cf_1261",$data['cf_1261']);
			$flats->set("cf_1444",$data['cf_1444']);
			if($data['cf_1235']){
				$flats->set("cf_1235",$data['cf_1235']);
			}
			if($data['cf_1448']){
				$flats->set("cf_1448",$data['cf_1448']);
			}
			if($data['cf_1450']){
				$flats->set("cf_1450",$data['cf_1450']);
			}
			if($data['cf_1452']){
				$flats->set("cf_1452",$data['cf_1452']);
			}
			if($data['cf_1478']){
				$flats->set("cf_1478",$data['cf_1478']);
			}
			if($data['cf_1446']){
				$flats->set("cf_1446",$data['cf_1446']);
			}

			$flats->set('mode', 'edit');
			$flats->save();
			$dataId = $flats->getId();
			return array("success"=>true,"message"=>array("update"=>true,"id"=>$dataId));
		}

		catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}
	public function createPayment($data){
		try{
			global $adb;
			$payments = Vtiger_Record_Model::getCleanInstance("SPPayments");
			if(!$data['assigned_user_id']){
				return array("success"=>false,"message"=>"Поле ответсвенного не может быть пустым");
			}
			$payments->set('type_payment',$data['type_payment']);
			$payments->set('pay_type',$data['pay_type']);
			$payments->set('spstatus',$data['spstatus']);
			$payments->set('assigned_user_id', $data['assigned_user_id']);
			$payments->set("created_user_id",$data['created_user_id']);
			$payments->set("amount",$data['amount']);
			$payments->set("payer",$data['payer']);
			$payments->set("pay_date",$data['pay_date']);
			if($data['cf_1416']){
				$payments->set("cf_1416",$data['cf_1416']);
			}			
			$payments->set('mode', 'create');
			$payments->save();
			$dataId = $payments->getId();
			$acl = Vtiger_AccessControl::loadUserPrivileges(25);

			return array("success"=>true,"message"=>array("create"=>true,"module"=>"SPPayments","id"=>$dataId));
		}
		catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}
	public function updatePayment($data){
		try{
			global $adb;
			$payments = Vtiger_Record_Model::getInstanceById($data['id']);
			if(!$data['assigned_user_id']){
				return array("success"=>false,"message"=>"Поле ответсвенного не может быть пустым");
			}
			$payments->set("assigned_user_id",$data['assigned_user_id']);
			if($data['spstatus']){
				$payments->set("spstatus",$data['spstatus']);
			}
			if($data['payer']){
				$payments->set("payer",$data['payer']);
			}
			if($data['amount']){
				$payments->set("amount",$data['amount']);
			}
			if($data['pay_type']){
				$payments->set("pay_type",$data['pay_type']);
			}
			if($data['type_payment']){
				$payments->set("type_payment",$data['type_payment']);
			}
			if($data['pay_date']){
				$payments->set("pay_date",$data['pay_date']);
			}
			if($data['created_user_id']){
				$payments->set("created_user_id",$data['assigned_user_id']);
			}
			$payments->set('mode', 'edit');
			$payments->save();
			$dataId = $payments->getId();
			return array("success"=>true,"message"=>array("update"=>true,"id"=>$dataId));
		}

		catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}
}

?>