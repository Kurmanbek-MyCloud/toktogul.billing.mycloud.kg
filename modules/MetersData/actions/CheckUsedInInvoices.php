<?php
/*+**********************************************************************************
 * Проверка использования показания в счетах
 * Возвращает список счетов где используется данное показание
 ************************************************************************************/

class MetersData_CheckUsedInInvoices_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function process(Vtiger_Request $request) {
        global $adb;

        $recordId = $request->get('record');

        $result = array(
            'success' => true,
            'invoices' => array()
        );

        if (empty($recordId)) {
            $this->sendResponse($result);
            return;
        }

        // Ищем счета где это показание используется как previous_reading_id или current_reading_id
        $query = "SELECT DISTINCT i.invoiceid, i.subject
                  FROM vtiger_invoice i
                  INNER JOIN vtiger_inventoryproductrel ipr ON ipr.id = i.invoiceid
                  INNER JOIN vtiger_crmentity crm ON crm.crmid = i.invoiceid
                  WHERE crm.deleted = 0
                  AND (ipr.previous_reading_id = ? OR ipr.current_reading_id = ?)
                  ORDER BY i.invoiceid DESC
                  LIMIT 10";

        $queryResult = $adb->pquery($query, array($recordId, $recordId));

        while ($row = $adb->fetchByAssoc($queryResult)) {
            $result['invoices'][] = array(
                'id' => $row['invoiceid'],
                'subject' => $row['subject']
            );
        }

        $this->sendResponse($result);
    }

    private function sendResponse($result) {
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
