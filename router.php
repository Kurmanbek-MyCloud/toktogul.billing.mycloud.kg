<?php
// Router для PHP встроенного сервера
// Обрабатывает статические файлы напрямую, остальное через index.php

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Убираем начальный слэш
$filePath = ltrim($requestPath, '/');

// Если файл существует и это статический файл - отдаем напрямую
if ($filePath && file_exists(__DIR__ . '/' . $filePath)) {
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $staticExtensions = ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'pdf', 'zip', 'map'];

    if (in_array(strtolower($ext), $staticExtensions)) {
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'map' => 'application/json'
        ];

        $mimeType = $mimeTypes[strtolower($ext)] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        readfile(__DIR__ . '/' . $filePath);
        return true;
    }

    // PHP файлы - выполняем
    if (strtolower($ext) === 'php') {
        return false;
    }
}

// Все остальное через index.php
require __DIR__ . '/index.php';
