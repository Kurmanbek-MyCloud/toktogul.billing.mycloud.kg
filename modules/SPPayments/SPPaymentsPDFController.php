<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: SalesPlatform Ltd
 * The Initial Developer of the Original Code is SalesPlatform Ltd.
 * All Rights Reserved.
 * If you have any questions or comments, please email: devel@salesplatform.ru
 ************************************************************************************/

include_once 'includes/SalesPlatform/PDF/SPPDFController.php';
require_once 'modules/Accounts/Accounts.php';
require_once 'modules/Contacts/Contacts.php';

class SalesPlatform_SPPaymentsPDFController extends SalesPlatform_PDF_SPPDFController {

	function buildDocumentModel() {
        global $app_strings;

        try {
            $model = parent::buildDocumentModel();

            $this->generateEntityModel($this->focus, 'SPPayments', 'payment_', $model);

            $entity = new Accounts();
            if($this->focusColumnValue('related_to')) {
                $entity->retrieve_entity_info($this->focusColumnValue('related_to'), 'Accounts');
            }
            $this->generateEntityModel($entity, 'Accounts', 'account_', $model);

            $entity = new Contacts();
            if($this->focusColumnValue('contact_id')) {
                $entity->retrieve_entity_info($this->focusColumnValue('contact_id'), 'Contacts');
            }
            $this->generateEntityModel($entity, 'Contacts', 'contact_', $model);

            $this->generateUi10Models($model);
            $this->generateRelatedListModels($model);

            $model->set('payment_owner', getUserFullName($this->focusColumnValue('assigned_user_id')));
            $model->set('payment_payer', getParentName($this->focusColumnValue('payer')));
            $contactid = $this->focusColumnValue('payer');

            $db = PearDatabase::getInstance();
            $kv = $db->pquery("SELECT a.flat FROM vtiger_flats as a INNER JOIN 
                                    vtiger_flatscf as c ON a.flatsid=c.flatsid WHERE c.cf_1235='$contactid'");
            $flat = $db->fetchByAssoc($kv);

            $model->set('flat', $flat['flat']);

            return $model;

        } catch (Exception $e) {
            echo '<meta charset="utf-8" />';
            if($e->getMessage() == $app_strings['LBL_RECORD_DELETE']) {
                echo $app_strings['LBL_RECORD_INCORRECT'];
                echo '<br><br>';
            } else {
                echo $e->getMessage();
                echo '<br><br>';
            }
            return null;
        }
    }

}
?>
