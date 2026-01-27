<?php
/*+**********************************************************************************
 * Действие для получения показаний счётчиков
 * Автор: Курманбек (26.01.2026)
 *
 * Используется для заполнения выпадающих списков показаний в EditView счёта
 ************************************************************************************/

class Invoice_GetMeterReadings_Action extends Vtiger_Action_Controller {

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

        $flatId = $request->get('flat_id');
        $meterId = $request->get('meter_id'); // Опционально - для фильтрации по конкретному счётчику
        $readingType = $request->get('reading_type'); // 'previous' или 'current'

        $result = array(
            'success' => true,
            'readings' => array(),
            'meters' => array()
        );

        if (empty($flatId)) {
            $result['success'] = false;
            $result['message'] = 'Не указан ID объекта (дома)';
            $this->sendResponse($result);
            return;
        }

        // Получаем список счётчиков для дома
        $metersQuery = "SELECT m.metersid, m.meter, mcf.cf_1462 as well_name
                        FROM vtiger_meters m
                        INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
                        INNER JOIN vtiger_crmentity crm ON crm.crmid = m.metersid
                        WHERE crm.deleted = 0 AND mcf.cf_1319 = ?
                        ORDER BY mcf.cf_1462";

        $metersResult = $adb->pquery($metersQuery, array($flatId));
        $meters = array();
        $meterIds = array();

        while ($meter = $adb->fetchByAssoc($metersResult)) {
            $meters[] = array(
                'id' => $meter['metersid'],
                'name' => $meter['meter'],
                'well' => $meter['well_name']
            );
            $meterIds[] = $meter['metersid'];
        }

        $result['meters'] = $meters;

        if (empty($meterIds)) {
            $result['readings'] = array();
            $this->sendResponse($result);
            return;
        }

        // Если указан конкретный счётчик, фильтруем по нему
        if (!empty($meterId)) {
            $meterIds = array($meterId);
        }

        // Получаем показания для счётчиков
        $placeholders = implode(',', array_fill(0, count($meterIds), '?'));
        $readingsQuery = "SELECT
                            md.metersdataid,
                            md.data as reading_value,
                            mdcf.cf_1317 as meter_id,
                            mdcf.cf_1325 as reading_date,
                            mdcf.cf_1521 as is_used,
                            m.meter as meter_name,
                            mcf.cf_1462 as well_name
                        FROM vtiger_metersdata md
                        INNER JOIN vtiger_metersdatacf mdcf ON mdcf.metersdataid = md.metersdataid
                        INNER JOIN vtiger_crmentity crm ON crm.crmid = md.metersdataid
                        INNER JOIN vtiger_meters m ON m.metersid = mdcf.cf_1317
                        INNER JOIN vtiger_meterscf mcf ON mcf.metersid = m.metersid
                        WHERE crm.deleted = 0
                        AND mdcf.cf_1317 IN ($placeholders)
                        ORDER BY mdcf.cf_1325 DESC, crm.createdtime DESC
                        LIMIT 100";

        $readingsResult = $adb->pquery($readingsQuery, $meterIds);
        $readings = array();

        while ($reading = $adb->fetchByAssoc($readingsResult)) {
            $readingDate = !empty($reading['reading_date']) ? date('d.m.Y', strtotime($reading['reading_date'])) : '';
            $wellName = $reading['well_name'] ? $reading['well_name'] : '';
            $isUsed = $reading['is_used'] ? true : false;

            $label = sprintf(
                "%s (%s) - %s%s",
                $reading['reading_value'],
                $readingDate,
                $wellName,
                $isUsed ? ' [использовано]' : ''
            );

            $readings[] = array(
                'id' => $reading['metersdataid'],
                'value' => $reading['reading_value'],
                'date' => $reading['reading_date'],
                'date_formatted' => $readingDate,
                'meter_id' => $reading['meter_id'],
                'meter_name' => $reading['meter_name'],
                'well_name' => $wellName,
                'is_used' => $isUsed,
                'label' => $label
            );
        }

        $result['readings'] = $readings;
        $this->sendResponse($result);
    }

    private function sendResponse($result) {
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
