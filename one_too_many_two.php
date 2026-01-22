<?php
/*
create_relation.php
Create this file into your root directory of vtiger i.e. vtigercrm/
and then run this file directly using your browser 
for example localhost/vtigercrm/create_relation.php
*/
include_once('vtlib/Vtiger/Module.php');
$moduleInstance = Vtiger_Module::getInstance('MetersData');
$accountsModule = Vtiger_Module::getInstance('Contacts');
$relationLabel  = 'contact_to_meters_data';
$accountsModule->setRelatedList(
      $moduleInstance, $relationLabel, Array('ADD') //you can do select also Array('ADD','SELECT')
);

echo "done";