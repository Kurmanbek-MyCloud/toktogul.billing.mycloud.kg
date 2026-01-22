<?php
chdir('../');


include_once 'includes/Loader.php';
include_once 'include/utils/utils.php';
include_once 'include/utils/InventoryUtils.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

global $adb;
global $current_user;
$current_user = Users::getActiveAdminUser();

$path = "phpexcel/elehantMeters23_05_2025.xlsx";
if (!file_exists($path)) {
    die("Файл не найден: $path");
}

try {
    require_once 'phpexcel/Classes/PHPExcel.php';

    $reader = PHPExcel_IOFactory::createReaderForFile($path);
    $excel_Obj = $reader->load($path);
    echo "Файл успешно прочитан" . PHP_EOL;
} catch (Exception $e) {
    die("Ошибка чтения Excel: " . $e->getMessage());
}

require_once 'Logger.php';
$logger = new CustomLogger('excelreader.log');


$worksheet = $excel_Obj->getSheet(0);


$numbers = [];
$names = [];
for ($row = 3; $row <= 5; $row++) {
    $meterNumber = trim($worksheet->getCell('B' . $row)->getValue());
    $meterValue = trim($worksheet->getCell('C' . $row)->getValue());
    $lastUpdate = trim($worksheet->getCell('D' . $row)->getValue());
    echo $meterNumber . PHP_EOL;
}