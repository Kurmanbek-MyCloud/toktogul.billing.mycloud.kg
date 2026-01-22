<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'config.php';
require_once 'vendor/autoload.php';
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

require_once('vtlib/Vtiger/Package.php');
require_once('vtlib/Vtiger/Module.php');
require_once 'include/utils/utils.php';
require_once 'Logger.php';
require_once 'includes/runtime/BaseModel.php';
require_once 'includes/runtime/Globals.php';
include_once 'includes/runtime/Controller.php';
include_once 'includes/http/Request.php';
// exit('test');
$package = new Vtiger_Package();
$package->export(
    Vtiger_Module::getInstance('Penalty'),
    'test/vtlib',
    'Penalty.zip'
);



