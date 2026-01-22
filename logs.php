<?php
header('Content-Type: application/json');
$logFile = 'metersDataConnector.log';

if (file_exists($logFile)) {
    $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo json_encode(['logs' => $logs], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['logs' => [], 'error' => 'Log file not found.'], JSON_UNESCAPED_UNICODE);
}
