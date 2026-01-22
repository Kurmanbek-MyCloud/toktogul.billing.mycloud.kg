<?php

function vtws_gethouseinfo($accountNumber, $action)

{   
    
    

    // Создайте подключение к базе данных
    $db = PearDatabase::getInstance();

    // Используйте prepared statement для безопасного выполнения запроса с параметром
    $query = "SELECT c.lastname, cf_1289 AS debt
              FROM vtiger_flats f 
              INNER JOIN vtiger_flatscf fcf ON fcf.flatsid = f.flatsid
              INNER JOIN vtiger_crmentity crm ON crm.crmid = f.flatsid
              LEFT JOIN vtiger_contactdetails c ON c.contactid = fcf.cf_1235
              LEFT JOIN vtiger_contactscf cf ON cf.contactid = c.contactid
              WHERE fcf.cf_1420 = ?";

    // Выполните запрос с использованием лицевого счета в качестве параметра
    $result = $db->pquery($query, array($accountNumber));
    var_dump($accountNumber);
    var_dump('test');
    var_dump($action);
    var_dump($result);

    // if ($action === 'check') {
    //     // Проверка наличия абонента
    //     if ($db->num_rows($result) > 0) {
    //         // Абонент найден, соберите информацию и верните результат
    //         $row = $db->fetchByAssoc($result);
            
    //         $houseInfo = array(
    //             'house_number' => '123',
    //             'address' => 'ул. Примерная, 1',
    //             'owner' => $row['lastname']
    //         );
            
    //         return array(
    //             'success' => true,
    //             'result' => $houseInfo
    //         );
    //     } else {
    //         // Абонент не найден, верните сообщение об ошибке
    //         return array(
    //             'success' => false,
    //             'error' => 'Абонент не найден.'
    //         );
    //     }
    // } else {
    //     // Если значение параметра action не является 'check', верните сообщение об ошибке
    //     return array(
    //         'success' => false,
    //         'error' => 'Недопустимое действие.'
    //     );
    // }
}

?>