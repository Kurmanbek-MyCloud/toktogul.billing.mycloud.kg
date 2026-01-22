# Toktogul Billing CRM

CRM система для биллинга на базе Vtiger CRM.

## Требования

- PHP 7.4+
- MySQL/MariaDB 10.6+
- Apache с mod_rewrite
- Docker и Docker Compose (для локальной разработки)

## Быстрый старт с Docker

### 1. Клонирование репозитория

```bash
git clone https://github.com/YOUR_USERNAME/toktogul-billing.git
cd toktogul-billing
```

### 2. Настройка конфигурации

```bash
# Копируем шаблон конфига
cp config.inc.php.example config.inc.php

# Редактируем настройки (для Docker используем эти значения):
# $dbconfig['db_server'] = 'db';
# $dbconfig['db_username'] = 'root';
# $dbconfig['db_password'] = 'root';
# $dbconfig['db_name'] = 'toktogul_bill2';
# $site_URL = 'http://localhost:8000/';
# $root_directory = '/var/www/html/';
```

### 3. Создание config_override.php для Docker

Создайте файл `config_override.php`:

```php
<?php
$max_mailboxes = 3;
$site_URL = 'http://localhost:8000/';
$PORTAL_URL = $site_URL . 'customerportal';
$root_directory = '/var/www/html/';
$dbconfig['db_server'] = 'db';
$dbconfig['db_port'] = ':3306';
$dbconfig['db_username'] = 'root';
$dbconfig['db_password'] = 'root';
$dbconfig['db_name'] = 'toktogul_bill2';
$dbconfig['db_type'] = 'mysqli';
$dbconfig['db_status'] = 'true';
$dbconfig['db_hostname'] = $dbconfig['db_server'] . $dbconfig['db_port'];
```

### 4. Создание CSRF секрета

```bash
echo '<?php $secret = "'$(openssl rand -hex 20)'";' > config.csrf-secret.php
```

### 5. Импорт базы данных

Положите дамп базы данных в корень проекта:

```bash
# Файл должен называться toktogul_bill2_backup.sql
# или измените путь в docker-compose.yml
```

### 6. Запуск

```bash
docker-compose up -d
```

Приложение доступно: http://localhost:8000

## Развертывание на сервере

### 1. Установка зависимостей

```bash
sudo apt update
sudo apt install apache2 php7.4 php7.4-mysqli php7.4-gd php7.4-zip php7.4-intl php7.4-xml php7.4-mbstring php7.4-curl mariadb-server
```

### 2. Настройка Apache

```bash
sudo a2enmod rewrite

# Создайте виртуальный хост
sudo nano /etc/apache2/sites-available/billing.conf
```

Пример конфигурации:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/toktogul.billing.mycloud.kg

    <Directory /var/www/toktogul.billing.mycloud.kg>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/billing_error.log
    CustomLog ${APACHE_LOG_DIR}/billing_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite billing.conf
sudo systemctl restart apache2
```

### 3. Настройка базы данных

```bash
sudo mysql -u root -p

CREATE DATABASE toktogul_bill2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'billing_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON toktogul_bill2.* TO 'billing_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Импорт дампа
mysql -u billing_user -p toktogul_bill2 < dump.sql
```

### 4. Настройка конфигов

```bash
cp config.inc.php.example config.inc.php
# Заполните реальные данные БД и домен
nano config.inc.php
```

### 5. Права доступа

```bash
sudo chown -R www-data:www-data /var/www/toktogul.billing.mycloud.kg
sudo chmod -R 755 /var/www/toktogul.billing.mycloud.kg
sudo chmod -R 775 cache/ storage/ logs/
```

### 6. Создание необходимых директорий

```bash
mkdir -p cache/images cache/import cache/upload
mkdir -p storage logs user_privileges
chmod -R 775 cache storage logs user_privileges
```

## Структура проекта

```
├── config.inc.php.example  # Шаблон конфигурации
├── config_override.php     # Локальные переопределения (не в git)
├── docker-compose.yml      # Docker конфигурация
├── Dockerfile              # PHP + Apache образ
├── modules/                # Модули CRM
├── layouts/                # Темы и шаблоны
├── languages/              # Переводы
├── include/                # Системные файлы
└── libraries/              # Библиотеки
```

## Основные модули

- **Flats** - Квартиры/абоненты
- **Invoices** - Счета
- **Payments** - Платежи
- **Meters** - Счетчики

## Cron задачи

Добавьте в crontab:

```bash
# Каждые 15 минут - обработка задач
*/15 * * * * cd /var/www/toktogul.billing.mycloud.kg && php vtigercron.php > /dev/null 2>&1
```

## Поддержка

По вопросам обращайтесь: almaz.mamadaliev@crm.kg
