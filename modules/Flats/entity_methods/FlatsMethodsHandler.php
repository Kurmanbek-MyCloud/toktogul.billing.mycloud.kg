<?php

function addService($ws_entity) {

  global $adb;
  $module = $ws_entity->getModuleName();
  $flat_id = explode('x', $ws_entity->getId())[1];
  $contactid = explode('x', $ws_entity->get('cf_1235'))[1];

  $contact_data = $adb->run_query_allrecords("SELECT cf.cf_1468 as abonent_type from vtiger_contactscf cf 
                        inner join vtiger_crmentity crm on crm.crmid = cf.contactid 
                        where crm.deleted =0
                        and cf.contactid = $contactid");
  $contact_type = $contact_data[0]['abonent_type'];

  // Вытаскиваем тип водопровода, и в зависимости какой он, добавляем услугу автоматически
  $sql_vod =
    "SELECT f.cf_type_vod, mcf.metersid 
      FROM vtiger_flats f
      INNER JOIN vtiger_crmentity vc ON f.flatsid = vc.crmid
      LEFT JOIN (
          SELECT mcf.metersid, mcf.cf_1319
          FROM vtiger_meterscf mcf
          INNER JOIN vtiger_crmentity vc2 ON mcf.metersid = vc2.crmid
          WHERE vc2.deleted = 0
      ) mcf ON mcf.cf_1319 = f.flatsid
      WHERE vc.deleted = 0 
      AND f.flatsid = ?";

  $result = $adb->pquery($sql_vod, array($flat_id));

  $row = $adb->fetch_row($result);
  $cf_type_vod_value = $row['cf_type_vod'];
  $meters = $row['metersid'];
  // var_dump($contact_type);
  // var_dump($cf_type_vod_value);
  // exit();
  if ($contact_type == 'Физ. лицо') {
    $adb->pquery("DELETE FROM vtiger_crmentityrel WHERE  `crmid`= $flat_id AND `module`='Flats' AND `relmodule`='Services'", array());
    if ($meters != null) {
      if ($cf_type_vod_value == 'Благоустроенный') {
        $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '30083', 'Services')", array()); // Муздак суу колдонуу 
        $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '30084', 'Services')", array()); // Канализация (счетчик) 
      } else {
        $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '30083', 'Services')", array()); // Муздак суу колдонуу 
      }
    } else {
      if ($cf_type_vod_value == 'Дворовой') {
        $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '77958', 'Services')", array()); // Вода - Дворовый 
      }

      if ($cf_type_vod_value == 'Благоустроенный') {
        $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '77962', 'Services')", array()); // Вода - Благоустроенный 
        $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '77963', 'Services')", array()); // Канализация - Благоустроенный 
      }

      if ($cf_type_vod_value == 'Уличный') {
        $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES ('$flat_id', 'Flats', '77961', 'Services')", array()); // Вода - Уличный 
      }
    }

  } elseif ($contact_type == 'Юр. лицо') {

  }
}

function generateLs($ws_entity) {
  global $adb;
  $flat_id = explode('x', $ws_entity->getId())[1];
  $adb->pquery("UPDATE vtiger_flatscf f
                  SET f.cf_1420 = CONCAT('49010',f.flatsid)
                  WHERE f.flatsid = ?", array($flat_id));
}
