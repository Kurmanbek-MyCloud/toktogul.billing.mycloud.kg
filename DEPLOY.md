# Toktogul Billing CRM

CRM система для биллинга на базе Vtiger CRM.

## Локальное развертывание с Docker

### Требования

- Docker Desktop (Mac/Windows) или Docker + Docker Compose (Linux)
- Git
- Дамп базы данных (получить у администратора)

### Пошаговая инструкция

#### Шаг 1. Клонирование репозитория

```bash
git clone git@github.com:Kurmanbek-MyCloud/toktogul.billing.mycloud.kg.git
cd toktogul.billing.mycloud.kg
```

#### Шаг 2. Создание конфигурационных файлов

Выполните команды:

```bash
# Копируем шаблон основного конфига
cp config.inc.php.example config.inc.php
```

#### Шаг 3. Создание config_override.php

Создайте файл `config_override.php` с настройками для Docker:

```bash
cat > config_override.php << 'EOF'
<?php
$max_mailboxes = 3;
$site_URL = 'http://localhost:8000/';
$PORTAL_URL = $site_URL . 'customerportal';
$root_directory = '/var/www/html/';

$dbconfig['db_server'] = 'db';
$dbconfig['db_port'] = ':3306';
$dbconfig['db_username'] = 'root';
$dbconfig['db_password'] = 'root';
$dbconfig['db_name'] = 'ИМЯ_ВАШЕЙ_БД';
$dbconfig['db_type'] = 'mysqli';
$dbconfig['db_status'] = 'true';
$dbconfig['db_hostname'] = $dbconfig['db_server'] . $dbconfig['db_port'];
EOF
```

> **Важно:** Замените `ИМЯ_ВАШЕЙ_БД` на имя базы данных из docker-compose.yml (параметр `MYSQL_DATABASE`)

#### Шаг 4. Создание CSRF секрета

```bash
echo '<?php $secret = "'$(openssl rand -hex 20)'";' > config.csrf-secret.php
```

#### Шаг 5. Создание необходимых директорий

```bash
mkdir -p cache/images cache/import cache/upload
mkdir -p storage logs user_privileges db_dump
```

#### Шаг 6. Импорт базы данных

Положите дамп базы данных в папку `db_dump/`:

```bash
# Скопируйте дамп в папку db_dump
cp /path/to/your/dump.sql db_dump/dump.sql
```

Затем раскомментируйте строку в `docker-compose.yml`:

```yaml
volumes:
  - mysql_data:/var/lib/mysql
  - ./db_dump:/docker-entrypoint-initdb.d  # раскомментировать эту строку
```

#### Шаг 7. Запуск Docker

```bash
docker-compose up -d
```

Дождитесь запуска (первый раз может занять время для импорта БД):

```bash
# Проверить логи
docker-compose logs -f

# Проверить статус
docker-compose ps
```

#### Шаг 8. Инициализация привилегий пользователей (ВАЖНО!)

После импорта БД необходимо создать файлы привилегий:

```bash
docker-compose exec web php scripts/init-privileges.php
```

Этот скрипт создаст файлы `user_privileges_X.php` и `sharing_privileges_X.php` для всех пользователей.

#### Шаг 9. Проверка

Откройте в браузере: **http://localhost:8000**

Логин по умолчанию: `admin` / `admin`

---

## Быстрый старт (одной командой)

Если у вас уже есть дамп БД, выполните:

```bash
# Клонирование
git clone git@github.com:Kurmanbek-MyCloud/toktogul.billing.mycloud.kg.git
cd toktogul.billing.mycloud.kg

# Настройка
cp config.inc.php.example config.inc.php

cat > config_override.php << 'EOF'
<?php
$max_mailboxes = 3;
$site_URL = 'http://localhost:8000/';
$PORTAL_URL = $site_URL . 'customerportal';
$root_directory = '/var/www/html/';
$dbconfig['db_server'] = 'db';
$dbconfig['db_port'] = ':3306';
$dbconfig['db_username'] = 'root';
$dbconfig['db_password'] = 'root';
$dbconfig['db_name'] = 'ИМЯ_ВАШЕЙ_БД';
$dbconfig['db_type'] = 'mysqli';
$dbconfig['db_status'] = 'true';
$dbconfig['db_hostname'] = $dbconfig['db_server'] . $dbconfig['db_port'];
EOF

echo '<?php $secret = "'$(openssl rand -hex 20)'";' > config.csrf-secret.php

mkdir -p cache/images cache/import cache/upload storage logs user_privileges db_dump

# Скопируйте дамп в db_dump/ и раскомментируйте строку в docker-compose.yml
# cp /path/to/dump.sql db_dump/dump.sql

# Запуск
docker-compose up -d

# ВАЖНО: После запуска инициализируйте привилегии
docker-compose exec web php scripts/init-privileges.php
```

---

## Полезные команды Docker

```bash
# Запустить контейнеры
docker-compose up -d

# Остановить контейнеры
docker-compose down

# Перезапустить
docker-compose restart

# Посмотреть логи
docker-compose logs -f web    # логи PHP/Apache
docker-compose logs -f db     # логи MariaDB

# Зайти в контейнер PHP
docker-compose exec web bash

# Зайти в MySQL
docker-compose exec db mysql -u root -proot ИМЯ_БД

# Импортировать дамп вручную (если БД уже запущена)
docker-compose exec -T db mysql -u root -proot ИМЯ_БД < db_dump/dump.sql

# Инициализация привилегий после импорта БД
docker-compose exec web php scripts/init-privileges.php

# Пересоздать контейнеры (если изменили Dockerfile)
docker-compose up -d --build

# Удалить все данные и начать заново
docker-compose down -v
```

---

## Порты

| Сервис | Порт |
|--------|------|
| Web (PHP/Apache) | http://localhost:8000 |
| MySQL | localhost:3307 |

Для подключения к БД через клиент (DBeaver, TablePlus и т.д.):
- Host: `localhost`
- Port: `3307`
- User: `root`
- Password: `root`
- Database: `ИМЯ_БД` (из docker-compose.yml)

---

## Решение проблем

### Ошибка 500 после логина

**Причина:** Отсутствуют файлы привилегий пользователей.

**Решение:**
```bash
docker-compose exec web php scripts/init-privileges.php
```

### Ошибка "Class 'Vtiger_Cache_Connector' not found"

**Причина:** Отсутствует файл `includes/runtime/cache/Connector.php`.

**Решение:** Файл уже включен в репозиторий. Если ошибка появилась, убедитесь что git pull прошел успешно.

### Ошибка "Access denied for user"

Проверьте что `config_override.php` создан корректно и содержит правильные настройки БД.

### Пустая страница или 500 ошибка

```bash
# Проверьте логи Apache
docker-compose logs web

# Проверьте права на директории
docker-compose exec web chown -R www-data:www-data /var/www/html/cache
docker-compose exec web chown -R www-data:www-data /var/www/html/storage
docker-compose exec web chown -R www-data:www-data /var/www/html/logs
docker-compose exec web chown -R www-data:www-data /var/www/html/user_privileges
```

### БД не импортируется автоматически

```bash
# Импортируйте вручную
docker-compose exec -T db mysql -u root -proot ИМЯ_БД < db_dump/dump.sql

# Затем инициализируйте привилегии
docker-compose exec web php scripts/init-privileges.php
```

### Контейнер db не запускается (Mac M1/M2)

В `docker-compose.yml` уже указана платформа `linux/arm64`. Если проблемы остаются:

```bash
docker-compose down -v
docker-compose up -d
```

### Сброс пароля администратора

```bash
# Сбросить пароль admin на 'admin'
docker-compose exec web php -r "echo crypt('admin', '\$1\$ad000000\$');" | xargs -I {} docker-compose exec db mysql -u root -proot ИМЯ_БД -e "UPDATE vtiger_users SET user_password='{}' WHERE user_name='admin';"
```

---

## Структура проекта

```
├── config.inc.php.example  # Шаблон конфигурации (в git)
├── config.inc.php          # Основной конфиг (НЕ в git)
├── config_override.php     # Локальные настройки (НЕ в git)
├── config.csrf-secret.php  # CSRF секрет (НЕ в git)
├── docker-compose.yml      # Docker конфигурация
├── Dockerfile              # PHP + Apache образ
├── scripts/
│   └── init-privileges.php # Скрипт инициализации привилегий
├── includes/
│   └── runtime/
│       └── cache/
│           └── Connector.php  # Системный файл кэша (в git)
├── db_dump/                # Папка для дампа БД (НЕ в git)
├── user_privileges/        # Привилегии пользователей (НЕ в git, генерируются)
├── modules/                # Модули CRM
├── layouts/                # Темы и шаблоны
├── languages/              # Переводы
├── include/                # Системные файлы
└── libraries/              # Библиотеки
```

---

## Чеклист после клонирования

- [ ] Скопировать `config.inc.php.example` → `config.inc.php`
- [ ] Создать `config_override.php` с настройками БД
- [ ] Создать `config.csrf-secret.php`
- [ ] Создать директории: `cache/`, `storage/`, `logs/`, `user_privileges/`, `db_dump/`
- [ ] Положить дамп БД в `db_dump/`
- [ ] Раскомментировать volume для `db_dump` в `docker-compose.yml`
- [ ] Запустить `docker-compose up -d`
- [ ] **Запустить `docker-compose exec web php scripts/init-privileges.php`**
- [ ] Открыть http://localhost:8000

---

## Развертывание на production сервере

См. раздел ниже для ручной установки на сервер без Docker.

### 1. Установка зависимостей

```bash
sudo apt update
sudo apt install apache2 php7.4 php7.4-mysqli php7.4-gd php7.4-zip php7.4-intl php7.4-xml php7.4-mbstring php7.4-curl mariadb-server
```

### 2. Настройка Apache

```bash
sudo a2enmod rewrite
sudo nano /etc/apache2/sites-available/billing.conf
```

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

### 3. Настройка БД

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE toktogul_bill2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'billing_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON toktogul_bill2.* TO 'billing_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
mysql -u billing_user -p toktogul_bill2 < dump.sql
```

### 4. Права доступа

```bash
sudo chown -R www-data:www-data /var/www/toktogul.billing.mycloud.kg
sudo chmod -R 755 /var/www/toktogul.billing.mycloud.kg
sudo chmod -R 775 cache/ storage/ logs/ user_privileges/
```

### 5. Инициализация привилегий

```bash
cd /var/www/toktogul.billing.mycloud.kg
php scripts/init-privileges.php
```

---

## Поддержка

По вопросам обращайтесь: almaz.mamadaliev@crm.kg
