<?php
namespace OimoV2;
echo 'start CRM<br>';
chdir('../../');
echo 'after chdir<br>';
require_once 'user_privileges/user_privileges_1.php';
echo 'after user_privileges<br>';
require_once 'includes/main/WebUI.php';
require_once 'include/utils/utils.php';
require_once 'vtlib/Vtiger/Module.php';
echo 'after Module<br>';
$current_user = \Users::getActiveAdminUser();
$user = \Users_Record_Model::getCurrentUserModel();
echo 'after getCurrentUserModel<br>';


class CRM {
	public function createPayment($data) {
		try {
			global $adb;
			$payments = \Vtiger_Record_Model::getCleanInstance("SPPayments");
			$payments->set('type_payment', 'Cashless Transfer');
			$payments->set('pay_type', 'Receipt');
			$payments->set('spstatus', 'Executed');
			$payments->set('assigned_user_id', '1');
			foreach ($data as $key => $value) {
				$payments->set($key, $value);
			}

			$payments->set('mode', 'create');
			$payments->save();

			$dataId = $payments->getId();

			$sql = $adb->run_query_allrecords("SELECT * FROM sp_payments sp 
                INNER JOIN sp_paymentscf spcf ON sp.payid = spcf.payid 
                WHERE spcf.payid = $dataId");
			$result = $sql[0];
			$keys_to_extract = ['cf_txnid', 'amount'];
			$new_array = array_intersect_key($result, array_flip($keys_to_extract));
			$new_array['command'] = "Платеж успешно сохранен в системе.";
			return $new_array;

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}
}
?>