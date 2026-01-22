<?php
require_once 'QRApiRSK.php';

$account = 120610;
$amount = 1;
$service = 1; // ID услуги

$qrApi = new QRApiRSK();
try {
    $result = $qrApi->generateQR($account, $amount, $service);

    echo "Лицевой счёт: $account<br>";
    echo "UUID: " . $result['id'] . "<br>";
    echo "Срок действия: " . ($result['response']['expiresAt'] ?? 'Бессрочный') . "<br><br>";
    echo '<img src="' . $result['response']['qrImage'] . '" alt="QR Code"><br>';
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "<br>";
}

// Проверка статуса
try {
    echo "<br><br>Проверка статуса:<br>";
    $status = $qrApi->getStatus('584a9c09-dcd5-42b8-bb79-1673636cbff3');
    echo "<pre>";
    print_r($status);
    echo "</pre>";
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "<br>";
}