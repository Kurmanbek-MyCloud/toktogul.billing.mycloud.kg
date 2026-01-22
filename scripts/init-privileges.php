<?php
/**
 * Скрипт инициализации привилегий пользователей
 * Запускать после импорта БД: php scripts/init-privileges.php
 *
 * Создает файлы user_privileges_X.php и sharing_privileges_X.php
 * для всех активных пользователей в системе
 */

// Определяем корневую директорию
$root_dir = dirname(__DIR__);
chdir($root_dir);

// Подключаем конфигурацию
if (!file_exists('config.inc.php')) {
    die("Error: config.inc.php not found. Copy from config.inc.php.example first.\n");
}

include_once 'config.inc.php';
if (file_exists('config_override.php')) {
    include_once 'config_override.php';
}

// Создаем директорию если не существует
$privileges_dir = $root_dir . '/user_privileges';
if (!is_dir($privileges_dir)) {
    mkdir($privileges_dir, 0755, true);
    echo "Created directory: user_privileges/\n";
}

// Подключаемся к БД
$db_host = $dbconfig['db_server'];
$db_port = str_replace(':', '', $dbconfig['db_port']);
$db_name = $dbconfig['db_name'];
$db_user = $dbconfig['db_username'];
$db_pass = $dbconfig['db_password'];

try {
    $pdo = new PDO(
        "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Получаем всех активных пользователей
$stmt = $pdo->query("
    SELECT u.id, u.user_name, u.is_admin, r.roleid, role.parentrole
    FROM vtiger_users u
    LEFT JOIN vtiger_user2role r ON u.id = r.userid
    LEFT JOIN vtiger_role role ON r.roleid = role.roleid
    WHERE u.status = 'Active'
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    die("No active users found in database.\n");
}

// Получаем все роли для subordinate_roles
$stmt = $pdo->query("SELECT roleid, parentrole FROM vtiger_role");
$all_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($users) . " active users.\n";

foreach ($users as $user) {
    $user_id = $user['id'];
    $user_name = $user['user_name'];
    $is_admin = ($user['is_admin'] === 'on');
    $roleid = $user['roleid'] ?: 'H2';
    $parentrole = $user['parentrole'] ?: 'H1::H2';

    // Определяем subordinate roles
    $subordinate_roles = [];
    $parent_roles = [];

    $parent_parts = explode('::', $parentrole);
    array_pop($parent_parts); // Убираем текущую роль
    $parent_roles = $parent_parts;

    // Находим подчиненные роли
    foreach ($all_roles as $role) {
        if (strpos($role['parentrole'], $parentrole . '::') === 0) {
            $subordinate_roles[] = $role['roleid'];
        }
    }

    // Создаем user_privileges_X.php
    $privileges_content = "<?php
\$is_admin = " . ($is_admin ? 'true' : 'false') . ";
\$user_info = array(
    'user_name' => '{$user_name}',
    'is_admin' => '" . ($is_admin ? 'on' : 'off') . "',
    'user_id' => '{$user_id}',
    'roleid' => '{$roleid}'
);
\$current_user_roles = '{$roleid}';
\$current_user_parent_role_seq = '{$parentrole}';
\$current_user_profiles = array(1);
\$profileGlobalPermission = array(1 => 0, 2 => 0);
\$profileTabsPermission = array();
\$profileActionPermission = array();
\$current_user_groups = array();
\$subordinate_roles = array(" . (!empty($subordinate_roles) ? "'" . implode("','", $subordinate_roles) . "'" : "") . ");
\$parent_roles = array(" . (!empty($parent_roles) ? "'" . implode("','", $parent_roles) . "'" : "") . ");
\$subordinate_roles_users = array();
";

    $file_path = $privileges_dir . "/user_privileges_{$user_id}.php";
    file_put_contents($file_path, $privileges_content);

    // Создаем sharing_privileges_X.php
    $sharing_content = "<?php
\$defaultOrgSharingPermission = array();
\$related_module_share = array();
";

    $sharing_file_path = $privileges_dir . "/sharing_privileges_{$user_id}.php";
    file_put_contents($sharing_file_path, $sharing_content);

    echo "Created privileges for user: {$user_name} (ID: {$user_id})" . ($is_admin ? ' [ADMIN]' : '') . "\n";
}

echo "\nDone! Created " . (count($users) * 2) . " privilege files.\n";
