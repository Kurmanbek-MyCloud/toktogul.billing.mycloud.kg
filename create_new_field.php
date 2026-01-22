<?php


include_once "vtlib/Vtiger/Module.php";

$block = 'LBL_PENALTY_INFORMATION'; # block name 

$modul_name = 'Penalty'; # module name

$module = Vtiger_Module::getInstance($modul_name); # module name 

$module1Class = Vtiger_Module::getClassInstance($modul_name);

$block1 = Vtiger_Block::getInstance($block, $module);


$fieldName = 'cf_penalty_description'; # name field

$field1 = new Vtiger_Field();

$field1->label = 'cf_penalty_description'; # label field

$field1->name = $fieldName;

$field1->table = $module1Class->table_name;

$field1->column = $fieldName;

$field1->generatedtype = 2;

$field1->columntype = "VARCHAR(255)";

$field1->uitype = 1;

$field1->typeofdata = "V~M";

$field1->quickcreate = 2;

// $field1->sequence = 7;

$block1->addField($field1);

// $field1->setPicklistValues(array('Оплачено', 'Неоплачено'));

// $field1->setRelatedModules(array("Invoice"));

$block1->save($module);

echo 'DONE';