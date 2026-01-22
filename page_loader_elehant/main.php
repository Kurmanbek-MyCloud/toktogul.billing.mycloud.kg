<?php
// Устанавливаем часовой пояс
date_default_timezone_set('Asia/Bishkek');

chdir('../');

include_once 'includes/Loader.php';
include_once 'include/utils/utils.php';
include_once 'include/utils/InventoryUtils.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

// Подключаем PHPExcel
require_once 'libraries/PHPExcel/PHPExcel.php';

global $adb;
global $current_user;
$current_user = Users::getActiveAdminUser();
// Создаем простой логгер
function writeLog($message) {
    $logFile = 'page_loader_elehant/main.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Функция для чтения XLSX файла
function readXlsxFile($filePath) {
    try {
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($filePath);
        
        $data = array();
        $worksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        // Получаем заголовки (вторая строка, так как первая содержит метаданные)
        $headers = array();
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cellValue = $worksheet->getCell($col . '2')->getValue();
            $headers[] = $cellValue ? $cellValue : 'Столбец ' . $col;
        }
        
        // Получаем данные (начиная с третьей строки, так как вторая - заголовки)
        for ($row = 3; $row <= $highestRow; $row++) {
            $rowData = array();
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $worksheet->getCell($col . $row)->getValue();
                $rowData[] = $cellValue ? $cellValue : '';
            }
            $data[] = $rowData;
        }
        
        // Создаем структурированные переменные
        $structuredData = createStructuredData($headers, $data);
        
        return array(
            'headers' => $headers,
            'data' => $data,
            'structuredData' => $structuredData,
            'totalRows' => $highestRow - 2, // Исключаем метаданные и заголовок
            'totalColumns' => count($headers)
        );
    } catch (Exception $e) {
        writeLog("Ошибка при чтении XLSX файла: " . $e->getMessage());
        return false;
    }
}

// Функция для создания структурированных данных
function createStructuredData($headers, $data) {
    $structured = array();
    
    // Создаем массив с именованными полями для каждой строки
    foreach ($data as $rowIndex => $row) {
        $rowData = array();
        foreach ($headers as $colIndex => $header) {
            $rowData[$header] = isset($row[$colIndex]) ? $row[$colIndex] : '';
        }
        $structured[] = $rowData;
    }
    
    return $structured;
}


// Обработка загрузки файлов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xlsxFile'])) {
    writeLog("Начало обработки загрузки файла");
    
    $uploadDir = '/var/www/toktogul.billing.mycloud.kg/page_loader_elehant/files/';
    
    // Создаем директорию если она не существует
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        writeLog("Создана директория для загрузки: " . $uploadDir);
    }
    
    // Проверяем и исправляем права доступа к директории
    if (!is_writable($uploadDir)) {
        // Пытаемся исправить права на директорию и все родительские директории
        $currentDir = $uploadDir;
        while ($currentDir !== '/' && $currentDir !== '') {
            if (is_dir($currentDir)) {
                chmod($currentDir, 0777);
                writeLog("Исправлены права доступа к директории: " . $currentDir);
            }
            $currentDir = dirname($currentDir);
        }
        
        // Дополнительно пытаемся изменить владельца директории
        if (function_exists('chown')) {
            chown($uploadDir, 'www-data');
            chgrp($uploadDir, 'www-data');
            writeLog("Изменен владелец директории на www-data: " . $uploadDir);
        }
        
        // Если все еще нет прав, выводим ошибку вместо переключения на локальную директорию
        if (!is_writable($uploadDir)) {
            writeLog("КРИТИЧЕСКАЯ ОШИБКА: Невозможно получить права на запись в " . $uploadDir);
            writeLog("Права директории: " . substr(sprintf('%o', fileperms($uploadDir)), -4));
            echo json_encode(['success' => false, 'message' => 'Ошибка прав доступа к директории загрузки']);
            exit;
        }
    }
    
    $file = $_FILES['xlsxFile'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    writeLog("Попытка загрузки файла: " . $fileName . ", размер: " . $fileSize . " байт");
    
    // Проверяем на ошибки загрузки
    if ($fileError !== UPLOAD_ERR_OK) {
        writeLog("Ошибка загрузки файла: код ошибки " . $fileError);
        echo json_encode(['success' => false, 'message' => 'Ошибка при загрузке файла']);
        exit;
    }
    
    // Проверяем расширение файла
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExtension !== 'xlsx') {
        writeLog("Отклонен файл с неподдерживаемым расширением: " . $fileExtension);
        echo json_encode(['success' => false, 'message' => 'Разрешены только файлы с расширением .xlsx']);
        exit;
    }
    
    // Проверяем размер файла (10 MB)
    if ($fileSize > 10 * 1024 * 1024) {
        writeLog("Отклонен файл превышающий размер: " . $fileSize . " байт");
        echo json_encode(['success' => false, 'message' => 'Размер файла не должен превышать 10 MB']);
        exit;
    }
    
    // Генерируем уникальное имя файла
    $uniqueFileName = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueFileName;
    
    writeLog("Сохранение файла как: " . $uniqueFileName);
    
    // Перемещаем загруженный файл
    writeLog("Попытка сохранения файла: " . $fileTmpName . " -> " . $uploadPath);
    writeLog("Права доступа к директории: " . (is_writable($uploadDir) ? 'да' : 'нет'));
    writeLog("Директория существует: " . (is_dir($uploadDir) ? 'да' : 'нет'));
    
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        writeLog("Файл успешно сохранен: " . $uniqueFileName);
        // ВАЖНО: XLSX файл остается в папке files/ и НЕ удаляется после обработки
        
        // Передаем данные в create_metersdata.php
        $fileData = readXlsxFile($uploadPath);
        if ($fileData !== false) {
            writeLog("Данные XLSX успешно прочитаны, всего строк: " . $fileData['totalRows']);
            
            // Сохраняем данные во временный файл для передачи
            $tempDataFile = 'temp_data_' . pathinfo($uniqueFileName, PATHINFO_FILENAME) . '.json';
            writeLog("Попытка сохранить во временный файл: " . $tempDataFile);
            writeLog("Текущая рабочая директория: " . getcwd());
            
            $result = file_put_contents($tempDataFile, json_encode($fileData));
            writeLog("Данные сохранены во временный файл: " . $tempDataFile . " (результат: " . ($result !== false ? 'успешно' : 'ошибка') . ")");
            
            if ($result !== false) {
                writeLog("Полный путь к временному файлу: " . realpath($tempDataFile));
                writeLog("Размер файла: " . filesize($tempDataFile) . " байт");
                writeLog("Файл существует: " . (file_exists($tempDataFile) ? 'да' : 'нет'));
            }
            
            // Вызываем обработку данных через HTTP запрос
            $url = 'https://toktogul.billing.mycloud.kg/page_loader_elehant/create_metersdata.php';
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ]);
            $result = @file_get_contents($url, false, $context);
            writeLog("Обработка данных вызвана по URL: " . $url . " - " . ($result !== false ? 'успешно' : 'ошибка'));
        }
        
        echo json_encode(['success' => true, 'message' => 'Файл успешно загружен', 'filename' => $uniqueFileName]);
    } else {
        writeLog("Ошибка при сохранении файла: " . $uniqueFileName);
        writeLog("Ошибка PHP: " . error_get_last()['message']);
        writeLog("Временный файл существует: " . (file_exists($fileTmpName) ? 'да' : 'нет'));
        writeLog("Целевой путь доступен для записи: " . (is_writable(dirname($uploadPath)) ? 'да' : 'нет'));
        echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении файла']);
    }
    exit;
}

// Обработка запроса на просмотр данных файла
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'view_file' && isset($_GET['filename'])) {
    $filename = $_GET['filename'];
    $uploadDir = '/var/www/toktogul.billing.mycloud.kg/page_loader_elehant/files/';
    $filePath = $uploadDir . $filename;
    
    // Проверяем, что файл существует и имеет правильное расширение
    if (file_exists($filePath) && pathinfo($filename, PATHINFO_EXTENSION) === 'xlsx') {
        $fileData = readXlsxFile($filePath);
        
        if ($fileData !== false) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'data' => $fileData
            ]);
        } else {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при чтении файла'
            ]);
        }
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Файл не найден'
        ]);
    }
    exit;
}


?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка XLSX файлов - Toktogul Billing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .upload-form {
            border: 2px dashed #ddd;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            background-color: #fafafa;
        }
        .file-input {
            margin: 20px 0;
        }
        input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            max-width: 400px;
        }
        .upload-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .upload-btn:hover {
            background-color: #0056b3;
        }
        .upload-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .status {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .file-info {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }
        .uploaded-files {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .file-list {
            list-style: none;
            padding: 0;
        }
        .file-item {
            padding: 10px;
            margin: 5px 0;
            background-color: white;
            border-radius: 3px;
            border-left: 4px solid #007bff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-info {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Загрузка XLSX файлов</h1>
        
        <form class="upload-form" id="uploadForm" enctype="multipart/form-data">
            <div class="file-input">
                <label for="xlsxFile">Выберите XLSX файл для загрузки:</label><br>
                <input type="file" id="xlsxFile" name="xlsxFile" accept=".xlsx" required>
            </div>
            
            <button type="submit" class="upload-btn" id="uploadBtn">
                Загрузить файл
            </button>
            
            <div class="file-info">
                <p>Поддерживаемые форматы: .xlsx</p>
                <p>Максимальный размер файла: 10 MB</p>
            </div>
        </form>
        
        <div id="status" class="status"></div>
        
        <div class="uploaded-files">
            <h3>Загруженные файлы:</h3>
            <ul class="file-list" id="fileList">
                <?php
                $uploadDir = '/var/www/toktogul.billing.mycloud.kg/page_loader_elehant/files/';
                if (is_dir($uploadDir)) {
                    $files = scandir($uploadDir);
                    $xlsxFiles = array_filter($files, function($file) {
                        return pathinfo($file, PATHINFO_EXTENSION) === 'xlsx';
                    });
                    
                    if (empty($xlsxFiles)) {
                        echo '<li>Нет загруженных файлов</li>';
                    } else {
                        foreach ($xlsxFiles as $file) {
                            $filePath = $uploadDir . $file;
                            $fileSize = filesize($filePath);
                            $fileDate = date('d.m.Y H:i:s', filemtime($filePath));
                            echo '<li class="file-item">';
                            echo '<div class="file-info">';
                            echo '<strong>' . htmlspecialchars($file) . '</strong><br>';
                            echo 'Размер: ' . round($fileSize / 1024, 2) . ' KB<br>';
                            echo 'Дата загрузки: ' . $fileDate;
                            echo '</div>';
                            echo '</li>';
                        }
                    }
                } else {
                    echo '<li>Директория для файлов не найдена</li>';
                }
                ?>
            </ul>
        </div>
        
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('xlsxFile');
            const uploadBtn = document.getElementById('uploadBtn');
            const status = document.getElementById('status');
            
            if (!fileInput.files[0]) {
                showStatus('Пожалуйста, выберите файл для загрузки', 'error');
                return;
            }
            
            const file = fileInput.files[0];
            
            // Проверка типа файла
            if (!file.name.toLowerCase().endsWith('.xlsx')) {
                showStatus('Пожалуйста, выберите файл с расширением .xlsx', 'error');
                return;
            }
            
            // Проверка размера файла (10 MB)
            if (file.size > 10 * 1024 * 1024) {
                showStatus('Размер файла не должен превышать 10 MB', 'error');
                return;
            }
            
            // Отправка файла
            const formData = new FormData();
            formData.append('xlsxFile', file);
            
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Загрузка...';
            status.style.display = 'none';
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus('Файл успешно загружен: ' + data.filename, 'success');
                    fileInput.value = '';
                    // Перезагружаем страницу для обновления списка файлов
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showStatus('Ошибка при загрузке: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showStatus('Ошибка при загрузке: ' + error.message, 'error');
            })
            .finally(() => {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Загрузить файл';
            });
        });
        
        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status ' + type;
            status.style.display = 'block';
        }
        
        
        // Функция для экранирования HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
    </script>
</body>
</html>
