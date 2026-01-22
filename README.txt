Установка функционала "уведомления"
1.Скопировать файл include/NotificationsHandler.php в соответствующую директорию проекта
2.Создать обработчики для Лиды/Заказы/Сделки/Активности и в качестве задачи выбрать "Запустить пользовательскую функцию"
3.Из списка название метода выбрать "Notify on creation"
4.В базе:
	1 - Создать(сделать дамп) таблицы vtiger_notifications
	2 - В таблице com_vtiger_workflowtasks_entitymethod добавить данные и указать
		function_path - путь include/NotificationsHandler.php
		function_name - notify
		method_name - Notify on creation
		module_name - Название модуля(Leads, SalesOrder, Events, Potentials)
	3 - В таблице com_vtiger_workflowtasks_entitymethod_seq задать id последним добавленым id из таблицы com_vtiger_workflowtasks_entitymethod
5.Скопировать файл modules/Vtiger/actions/GetNotificationsCount.php в соответствующую папку проекта
6.Скопировать файл modules/Vtiger/views/GetNotifications.php в соответствующую папку проекта
7.Скопировать файл modules/Vtiger/actions/UpdateNotifications.php в соответствующую папку проекта
8.Скопировать файл layouts/v7/resources/notify.js в соответствующую папку проекта
9.Скопировать файл modules/Vtiger/actions/RemoveNotification.php в соответствующую папку проекта
10.Подключаем js файл в проект в файле layouts/v7/modules/Vtiger/Header.php
11.В файле layouts/v7/modules/Vtiger/resources/Vtiger.js скопировать функции showNotifs и removeNotif в соответствующий файл проекта
12.Скопировать в файле layouts/v7/modules/Vtiger/partials/Topbar.tpl li с содержимым уведомления и поместить в соответствующий файл проекта
13.Скопировать файл layouts/v7/modules/Vtiger/NotificationsList.tpl в соответствующую папку проекта
14.Наслаждаемся функционалом =)

Установка функционала преобразования лида в заказ
1.Заходим в файл modules/Leads/models/Record.php и копируем функцию getConvertToSalesLeadFields в соответствующий файл
2.В файле указаном выше находим функцию getConvertLeadFields и добавляем в конец перед return:
	$soFields = $this->getConvertToSalesLeadFields();
	if(!empty($soFields)) $convertFields['SalesOrder'] = $soFields;
3.Заходим в файл modules/Leads/views/SaveConvertLead.php и находим комментарии относящиеся к преобразованию и следуем указаниям)
4.Заходим в файл include/WebServices/ConvertLead.php и повторяем шаг 3)
5.Заходим в файл modules/Leads/handlers/LeadHandler.php копируем содержимое. После чего на строке добавления в таблицу vtiger_inventoryproductrel вставляем нужные нам поля(зависит от требований)
6.Все готово)