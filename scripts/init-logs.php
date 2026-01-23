<?php
/**
 * Скрипт инициализации лог файлов
 * Создаёт все необходимые лог файлы и устанавливает права доступа
 *
 * Использование:
 *   php scripts/init-logs.php
 *
 * Или в Docker:
 *   docker-compose exec web php scripts/init-logs.php
 */

$rootDir = dirname(__DIR__);

// Проверка на путь
var_dump($rootDir);
// exit;

// Список лог файлов которые нужно создать
$logFiles = [
    // Основная папка logs/
    'logs/vtigercrm.log',
    'logs/security.log',
    'logs/installation.log',
    'logs/migration.log',
    'logs/soap.log',
    'logs/platform.log',
    'logs/sqltime.log',
    'logs/application.log',
    'logs/viewer-debug.log',
    'logs/voip_integration.log',
    'logs/googleErrorLog.txt',
    'logs/InformgradDirect2.log',

    // Корневая папка
    'bot_ticket_handler.log',
    'createInvoice_new.log',
    'createInvoice.log',
    'whatsAppViaGupshupAll.log',
    'getDifferentMeters.log',
    'invoice.log',
    'vtigermodule.log',

    // Модули и сервисы
    'connector_quant/connector_quant.log',
    'payment_services/oimo/payments.log',
    'payment_services/oimo_v2/payments.log',
    'api_elehant/elehant_api_check.log',
    'modules/Documents/parce_and_create_payments.log',
];

// Директории которые нужно создать
$directories = [
    'logs',
    'cache',
    'cache/images',
    'cache/import',
    'cache/upload',
    'storage',
    'user_privileges',
];

echo "=== Инициализация лог файлов ===\n\n";

// Создаём директории
echo "Создание директорий...\n";
foreach ($directories as $dir) {
    $fullPath = $rootDir . '/' . $dir;
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0775, true)) {
            echo "  [OK] Создана: $dir\n";
        } else {
            echo "  [ОШИБКА] Не удалось создать: $dir\n";
        }
    } else {
        echo "  [--] Уже существует: $dir\n";
    }
}

echo "\nСоздание лог файлов...\n";

// Создаём лог файлы
foreach ($logFiles as $logFile) {
    $fullPath = $rootDir . '/' . $logFile;
    $dir = dirname($fullPath);

    // Создаём директорию если не существует
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    // Создаём файл если не существует
    if (!file_exists($fullPath)) {
        if (touch($fullPath)) {
            chmod($fullPath, 0666);
            echo "  [OK] Создан: $logFile\n";
        } else {
            echo "  [ОШИБКА] Не удалось создать: $logFile\n";
        }
    } else {
        // Проверяем права
        $perms = fileperms($fullPath) & 0777;
        if ($perms < 0666) {
            chmod($fullPath, 0666);
            echo "  [FIX] Исправлены права: $logFile\n";
        } else {
            echo "  [--] Уже существует: $logFile\n";
        }
    }
}

// Устанавливаем права на директории
echo "\nУстановка прав на директории...\n";
$writableDirs = ['logs', 'cache', 'storage', 'user_privileges'];
foreach ($writableDirs as $dir) {
    $fullPath = $rootDir . '/' . $dir;
    if (is_dir($fullPath)) {
        chmod($fullPath, 0775);
        echo "  [OK] chmod 775: $dir\n";
    }
}

echo "\n=== Готово! ===\n";
echo "\nЕсли на сервере с Apache, также выполните:\n";
echo "  sudo chown -R www-data:www-data logs/ cache/ storage/ user_privileges/\n";