<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
global $current_user;
$current_user = Users::getActiveAdminUser();
class MetersData_ImportExcelHandler_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {
        // Проверка доступа (по необходимости можешь доработать)
        return true;
    }

    public function process(Vtiger_Request $request) {
        global $log;

        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            echo 'Ошибка загрузки файла.';
            return;
        }

        $uploadDir = 'phpexcel/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = 'uploaded_' . date('Ymd_His') . '.xlsx';
        $fullPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $fullPath)) {
            // Вызываем скрипт, который ты уже написал (с парсингом Excel)
            include_once 'modules/MetersData/scripts/parseUploadedExcel.php';
            processUploadedExcel($fullPath); // передаём путь к файлу

            echo "<div style='padding:20px;'>
                <h4>Импорт завершён</h4>
                <p>Файл: <strong>{$filename}</strong> успешно обработан.</p>
                <a href='index.php?module=MetersData&view=List' class='btn btn-primary'>Назад</a>
            </div>";
        } else {
            echo 'Ошибка при сохранении файла.';
        }
    }
}
