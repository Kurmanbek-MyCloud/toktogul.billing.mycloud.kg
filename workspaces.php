<?php

include_once 'vtlib/Vtiger/Module.php';
include_once 'vtlib/Vtiger/Package.php';
include_once 'includes/main/WebUI.php';

include_once 'include/Webservices/Utils.php';

$Vtiger_Utils_Log = true;

$MODULENAME = 'Workspace';

$moduleInstance = new Vtiger_Module();
$moduleInstance->name = $MODULENAME;
$moduleInstance->parent = "MARKETING";
$moduleInstance->save();

// Schema Setup
$moduleInstance->initTables();

// Webservice Setup
$moduleInstance->initWebservice();

// Field Setup
$block1 = new Vtiger_Block();
$block1->label = 'Информация ' . strtoupper($moduleInstance->name);
$moduleInstance->addBlock($block1);


// Filter Setup
$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$moduleInstance->addFilter($filter1);

// Add field here using normal defination

$field = new Vtiger_Field();
$field->name = 'floor_number';
$field->label = 'Номер этажа';
$field->uitype = 10;
$field->summaryfield = 1;
$field->column = $field->name;
$field->columntype = 'INT(11)';
$field->typeofdata = 'I~M';
$block1->addField($field);
$moduleInstance->setEntityIdentifier($field);
$field->setRelatedModules(Array('FloorScheme'));

$field = new Vtiger_Field();
$field->name = 'area';
$field->label = 'Площадь';
$field->uitype = 1;
$field->column = $field->name;
$field->columntype = 'VARCHAR(255)';
$field->typeofdata = 'V~M';
$block1->addField($field);
$moduleInstance->setEntityIdentifier($field);

$field = new Vtiger_Field();
$field->name = 'space_status';
$field->label = 'Статус';
$field->uitype = 16;
$field->summaryfield = 1;
$field->column = $field->name;
$field->columntype = 'VARCHAR(255)';
$field->typeofdata = 'V~M';
$block1->addField($field);
$field->setPicklistValues(Array('Активно', 'Неактивно'));

$field = new Vtiger_Field();
$field->name = 'organization_name';
$field->label = 'Название организации';
$field->uitype = 1;
$field->column = $field->name;
$field->columntype = 'VARCHAR(255)';
$field->typeofdata = 'V~O';
$block1->addField($field);
$moduleInstance->setEntityIdentifier($field);

$img_field = new Vtiger_Field();
$img_field->name = 'organization_logo';
$img_field->label = 'Логотип организации';
$img_field->uitype = 69;
$img_field->column = $img_field->name;
$block1->addField($img_field);
$moduleInstance->setEntityIdentifier($img_field);

$field = new Vtiger_Field();
$field->name = 'autocenter_logo';
$field->label = 'Автоматически ценрировать логотип';
$field->uitype = 56;
$field->column = $field->name;
$block1->addField($field);
$moduleInstance->setEntityIdentifier($field);

$field = new Vtiger_Field();
$field->name = 'space_coords';
$field->label = 'Координаты';
$field->uitype = 1;
$field->column = $field->name;
$field->columntype = 'VARCHAR(255)';
$field->typeofdata = 'V~M';
$block1->addField($field);
$moduleInstance->setEntityIdentifier($field);

$responsible_field = new Vtiger_Field();
$responsible_field->name = 'responsible';
$responsible_field->label = 'Ответственный';
$responsible_field->table = 'vtiger_crmentity';
$responsible_field->column = 'smownerid';
$responsible_field->uitype = 53;
$responsible_field->typeofdata = 'V~M';
$block1->addField($responsible_field);

// Sharing Access Setup
$moduleInstance->setDefaultSharing('Public');

$targetpath = 'modules/' . $moduleInstance->name;

if (! is_file($targetpath)) {
    mkdir($targetpath);

    $templatepath = 'vtlib/ModuleDir/6.0.0';

    $moduleFileContents = file_get_contents($templatepath . '/ModuleName.php');
    $replacevars = array(
        'ModuleName' => $moduleInstance->name,
        '<modulename>' => strtolower($moduleInstance->name),
        '<entityfieldlabel>' => $field1->label,
        '<entitycolumn>' => $field1->column,
        '<entityfieldname>' => $field1->name
    );

    foreach ($replacevars as $key => $value) {
        $moduleFileContents = str_replace($key, $value, $moduleFileContents);
    }
    file_put_contents($targetpath . '/' . $moduleInstance->name . '.php', $moduleFileContents);
}

if (! file_exists('languages/en_us/ModuleName.php')) {
    $ModuleLanguageContents = file_get_contents($templatepath . '/languages/en_us/ModuleName.php');

    $replaceparams = array(
        'Module Name' => $moduleInstance->name,
        'Custom' => $moduleInstance->name,
        'ModuleBlock' => $moduleInstance->name,
        'ModuleFieldLabel Text' => $field1->label
    );

    foreach ($replaceparams as $key => $value) {
        $ModuleLanguageContents = str_replace($key, $value, $ModuleLanguageContents);
    }

    $languagePath = 'languages/en_us';
    file_put_contents($languagePath . '/' . $moduleInstance->name . '.php', $ModuleLanguageContents);
}

Settings_MenuEditor_Module_Model::addModuleToApp($moduleInstance->name, $moduleInstance->parent);

echo $moduleInstance->name." is Created";

?>