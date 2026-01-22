<?php
function notify($ws_entity) {
	global $adb;
	$em = new VTEventsManager($adb);
	$em->triggerEvent("vtiger.create.notification", array(
		"module" => $ws_entity->getModuleName(),
		"id" => $ws_entity->getId(),
		"owner" => $ws_entity->get('assigned_user_id')
	));
}