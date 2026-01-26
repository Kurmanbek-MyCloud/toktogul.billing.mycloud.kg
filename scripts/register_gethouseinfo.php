<?php
$Vtiger_Utils_Log = true;

chdir('../');

include_once 'include/database/PearDatabase.php';
include_once 'include/Webservices/Utils.php';

global $current_user, $adb;

$db = PearDatabase::getInstance();

$operationName = 'gethouseinfo';
$handler_path = 'include/Webservices/GetHouseInfo.php';
$handler_method = 'vtws_gethouseinfo';
$operation_type = 'GET'; // Используйте GET, если вы хотите, чтобы запрос выполнялся с помощью HTTP GET

$result = $db->pquery("SELECT 1 FROM vtiger_ws_operation WHERE name = ?", array($operationName));
if (!$db->num_rows($result)) {
    $operationId = vtws_addWebserviceOperation($operationName, $handler_path, $handler_method, $operation_type);
    vtws_addWebserviceOperationParam($operationId, 'accountNumber', 'string', 0);
    vtws_addWebserviceOperationParam($operationId, 'action', 'string', 0); 
}

echo 'DONE!';
?>
