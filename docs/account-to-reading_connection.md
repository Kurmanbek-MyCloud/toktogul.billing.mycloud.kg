# Связь показаний счётчиков со счетами

**Дата:** 26.01.2026
**Автор:** Курманбек

## Цель

Реализовать выбор показаний счётчиков из выпадающего списка при создании/редактировании счёта и сделать показания кликабельными ссылками при просмотре.

---

## Изменения в базе данных

### SQL скрипт: `scripts/add_reading_ids_columns.sql`

```sql
ALTER TABLE vtiger_inventoryproductrel
ADD COLUMN previous_reading_id INT(19) DEFAULT NULL AFTER previous_reading;

ALTER TABLE vtiger_inventoryproductrel
ADD COLUMN current_reading_id INT(19) DEFAULT NULL AFTER current_reading;

CREATE INDEX idx_prev_reading_id ON vtiger_inventoryproductrel(previous_reading_id);
CREATE INDEX idx_curr_reading_id ON vtiger_inventoryproductrel(current_reading_id);
```

---

## Новые файлы

### 1. `modules/Invoice/actions/GetMeterReadings.php`

AJAX endpoint для получения показаний счётчиков.

**Параметры запроса:**
- `flat_id` — ID объекта (дома)
- `meter_id` — ID счётчика (опционально)

**Возвращает:**
```json
{
  "success": true,
  "meters": [{"id": 123, "name": "...", "well": "Колодец 1"}],
  "readings": [
    {
      "id": 456,
      "value": "182.130",
      "date": "2025-11-15",
      "date_formatted": "15.11.2025",
      "meter_id": 123,
      "well_name": "Колодец 1",
      "is_used": false,
      "label": "182.130 (15.11.2025) - Колодец 1"
    }
  ]
}
```

---

## Изменённые файлы

### 1. `include/utils/EditViewUtils.php`

**Строки ~376-395**

Добавлено получение ID показаний из БД:

```php
// ID предыдущего показания (для связи с MetersData)
$previousReadingId = $adb->query_result($result, $i - 1, 'previous_reading_id');
$product_Detail[$i]['previousReadingId' . $i] = $previousReadingId;

// ID текущего показания (для связи с MetersData)
$currentReadingId = $adb->query_result($result, $i - 1, 'current_reading_id');
$product_Detail[$i]['currentReadingId' . $i] = $currentReadingId;
```

---

### 2. `include/utils/InventoryUtils.php`

**Строки ~684-720**

Добавлено сохранение ID показаний при редактировании счёта через UI:

```php
// Получение из $_REQUEST
$previousReadingId = vtlib_purify($_REQUEST['previousReadingId'.$i]);
$currentReadingId = vtlib_purify($_REQUEST['currentReadingId'.$i]);
$previousReadingId = !empty($previousReadingId) ? $previousReadingId : null;
$currentReadingId = !empty($currentReadingId) ? $currentReadingId : null;

// INSERT запрос дополнен полями:
// previous_reading_id, current_reading_id
```

---

### 3. `createInvoice.php`

**Функция `add_meters_to_service`** (~строка 324):

Добавлено получение ID текущего показания:
```php
$current_metersdataid = $adb->query_result($current_meter_data, 0, 'metersdataid');
```

**Функция `add_service_to_invoice`** (~строка 400):

Добавлены параметры и сохранение:
```php
function add_service_to_invoice(..., $prev_reading_id = null, $current_reading_id = null)

$sql = "INSERT INTO vtiger_inventoryproductrel(..., previous_reading_id, current_reading_id) VALUES(...,?,?)";
```

---

### 4. `layouts/v7/modules/Invoice/partials/LineItemsContent.tpl`

**Строки ~23-27** — добавлены переменные:
```smarty
{assign var="previousReadingId" value="previousReadingId"|cat:$row_no}
{assign var="currentReadingId" value="currentReadingId"|cat:$row_no}
```

**Строки ~146-176** — заменены input на select:
```smarty
<select class="readingSelect previousReadingSelect" data-row="{$row_no}" data-type="previous">
    <option value="">-- Выберите --</option>
    {if $data.$previousReadingId}
        <option value="{$data.$previousReadingId}" selected>{$data.$previousReading}</option>
    {/if}
</select>
<input type="hidden" name="{$previousReading}" value="{$data.$previousReading}">
<input type="hidden" name="{$previousReadingId}" value="{$data.$previousReadingId}">
```

---

### 5. `layouts/v7/modules/Invoice/LineItemsDetail.tpl`

**Строки ~199-219** — показания стали ссылками:
```smarty
{if $LINE_ITEM_DETAIL["previousReadingId$INDEX"]}
    <a href="index.php?module=MetersData&view=Detail&record={$LINE_ITEM_DETAIL["previousReadingId$INDEX"]}"
       target="_blank">
        {$LINE_ITEM_DETAIL["previousReading$INDEX"]}
    </a>
{else}
    {$LINE_ITEM_DETAIL["previousReading$INDEX"]}
{/if}
```

---

### 6. `layouts/v7/modules/Invoice/resources/Edit.js`

**Добавлены методы:**

| Метод | Описание |
|-------|----------|
| `registerMeterReadingsEvents()` | Инициализация событий |
| `loadMeterReadings(flatId)` | AJAX загрузка показаний |
| `populateReadingSelects(readings)` | Заполнение select-ов |
| `clearReadingSelects()` | Очистка select-ов |
| `recalculateQuantity(row)` | Пересчёт количества |

**Логика работы:**
1. При загрузке страницы — загружаются показания по `cf_1265` (ID дома)
2. При изменении дома — перезагружаются показания
3. При выборе показания — обновляются hidden-поля и пересчитывается количество

---

## Как это работает

### При создании/редактировании счёта:
1. Пользователь выбирает объект (дом)
2. JavaScript загружает показания счётчиков этого дома через AJAX
3. Выпадающие списки заполняются показаниями
4. При выборе показания автоматически рассчитывается количество (разница)

### При просмотре счёта:
1. Если показание связано с записью MetersData — отображается как ссылка
2. Клик открывает запись MetersData в новой вкладке

### При автогенерации счетов (createInvoice.php):
1. ID показаний автоматически сохраняются вместе со значениями

---

## Инструкция по установке

1. Выполнить SQL скрипт: `scripts/add_reading_ids_columns.sql`
2. Очистить кэш: `rm -rf test/templates_c/*`
3. Проверить работу на тестовом счёте
