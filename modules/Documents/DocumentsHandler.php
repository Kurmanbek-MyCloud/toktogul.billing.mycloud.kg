<?php
    
    function parce_and_create_payments($entityData){
      require_once 'Classes/PHPExcel.php';
      require 'config.inc.php';
      require 'Logger.php';
      global $adb;
      
      $logger = new CustomLogger("modules/Documents/parce_and_create_payments.log");

      $doc_id = explode('x',$entityData->getId() )[1];
      $file_name = strval($doc_id+1).'_'.$entityData->get('filename');
      $year =date('Y');
      $month = date('F');
      // $week = "week".date('w',strtotime('2021-12-05'));  #!!!!!!!!!!!!!!!!!!!Раскоментить 
      // $week = "week1";
      echo $doc_id."<br>";
      echo $file_name."<br>";
      echo $year."<br>";
      // echo date('Y-m-d');
      // echo weekOfMonth(strtotime('2021-s
      // $directory = new DirectoryIterator(dirname(__FILE__));
      $directory = $root_directory.'storage/'.$year.'/'.$month;
      
      // echo $directory."<br>";
      foreach (scandir($directory) as $week) {
          if(!in_array($week, array('.','..'))){
              foreach (scandir($directory.'/'.$week) as $file) {
                  if(in_array($file, array($file_name))){
                      // echo $week."<br>";
                      // echo "<pre>";
                      // var_dump($file);
                      // echo "</pre>";
                      // echo realpath($file);
                      $path_to_excel_file = $directory.'/'.$week.'/'.$file_name;
                      
                  }
              }
          }
      }
      echo $path_to_excel_file."<br>";
      
      

      $excelReader = PHPExcel_IOFactory::createReaderForFile($path_to_excel_file);
      
      // echo $directory,'         ';
      // echo $path,"\r\n";
      $excel_Obj = $excelReader->load($path_to_excel_file);
      
      $worksheet = $excel_Obj->getActiveSheet();
      $lastRow = $worksheet->getHighestRow();
      // echo $worksheet->getCell('D2')->getValue(),"<br>";
      // echo $worksheet->getCell('D2'),"<br>";
      echo  date("Y-m-d",PHPExcel_Shared_Date::ExcelToPHP($worksheet->getCell('D2')->getValue())),"<br>";
      // exit();
      
      
      
      echo "<pre>";
      for ($i=2; $i <= $lastRow ; $i++) { 
        // echo $worksheet->getCell('A'.$i),"\r\n";
        // $date = $worksheet->getCell('A'.$i);
        // $cell_date='';
        $fio = trim($worksheet->getCell('A'.$i)->getValue());
        $street = trim($worksheet->getCell('B'.$i)->getValue());
        $house = trim($worksheet->getCell('C'.$i)->getValue());
        $date = $worksheet->getCell('D'.$i)->getValue() != null ? date("Y-m-d",PHPExcel_Shared_Date::ExcelToPHP(trim($worksheet->getCell('D'.$i)->getValue()))) : "";
        $amount = trim($worksheet->getCell('E'.$i)->getValue());

        $fio = $fio != null ? $fio : "";
        $street = $street != null ? $street : "";
        $house = $house != null ? $house : "";
        $amount = $amount != null ? $amount : "";
        // var_dump($worksheet->getCell('D'.$i)->getValue());
        // var_dump('$date');
        // var_dump($date);
        // var_dump($fio);
        // var_dump($street);
        // var_dump($house);
        // var_dump($amount);
        if ($fio == null && $street == null && $house == null && $amount == null && $date == null) {
          continue;
        }
        if ($fio == null || $street == null || $house == null || $amount == null || $date == null) {
          $logger->log("ERROR! Недостаточно данных! Файл: $file_name #$i ФИО: '$fio', Улица: '$street', Дом: '$house', Сумма: '$amount', Дата: '$date'");
          continue;
        }
        // var_dump($worksheet->getCell('D'.$i)->getValue());
        
        $result_contact = $adb->pquery("SELECT contactid, lastname FROM vtiger_contactdetails c inner join vtiger_crmentity crm on crm.crmid = c.contactid WHERE crm.deleted  = 0 and lastname = ?",array($fio));
        $contact_id = $adb->query_result($result_contact,0,'contactid');
        if ($contact_id == null) {
          $logger->log("ERROR! Ошибка получения ID Контакта! Файл: $file_name #$i ФИО: '$fio', Улица: '$street', Дом: '$house', Сумма: '$amount', Дата: '$date'");
          continue;
        }
        $result_street = $adb->pquery("SELECT housesid, house FROM vtiger_houses h inner join vtiger_crmentity crm on crm.crmid = h.housesid WHERE crm.deleted = 0 and house = ?",array($street));
        $street_id = $adb->query_result($result_street,0,'housesid');
        if ($street_id == null) {
          $logger->log("ERROR! Ошибка получения ID Улицы! Файл: $file_name #$i ФИО: '$fio', Улица: '$street', Дом: '$house', Сумма: '$amount', Дата: '$date'");
          continue;
        }
        
        $result_house = $adb->pquery("SELECT * FROM vtiger_flats f inner join vtiger_flatscf fcf on fcf.flatsid = f.flatsid inner join vtiger_crmentity crm on crm.crmid = f.flatsid WHERE crm.deleted = 0 and flat = ? and fcf.cf_1203 = ?",array($house,$street_id));
        $house_id = $adb->query_result($result_house,0,'flatsid');
        $house = $adb->query_result($result_house,0,'flat');
        if ($house_id == null) {
          $logger->log("ERROR! Ошибка получения ID Дома! Файл: $file_name #$i ФИО: '$fio', Улица: '$street', Дом: '$house', Сумма: '$amount', Дата: '$date'");
          continue;
        }
        
        var_dump('$contact_id - '.$contact_id);
        // var_dump();
        var_dump('$street_id - '.$street_id);
        var_dump('$house_id - '.$house_id);
        // var_dump($house_id);
        var_dump('$amount - '.$amount);
        var_dump("-----------------");
        // $adb->
        // $adb->
        $payment = Vtiger_Record_Model::getCleanInstance("SPPayments");
        $payment->set('pay_date', $date);
        $payment->set('pay_type', 'Receipt');
        $payment->set('assigned_user_id', 1);
        $payment->set('spstatus', 'Executed');
        $payment->set('payer', $contact_id);
        $payment->set('type_payment', 'Cash Payment');
        $payment->set('amount', $amount );
        // $payment->set('cf_1492', $ls);
        $payment->set('cf_1416', $house_id);
        // // $payment->set('related_to', $service_id);
        $payment->set('mode', 'create');
        
        $payment->save();
        $pay_id = $payment->getId();
        var_dump($pay_id);
        if ($pay_id != null) {
          $logger->log("Платеж успешно создан Id: $pay_id Файл: $file_name #$i ФИО: '$fio'");
        }
        else{
          $logger->log("ERROR! Платеж НЕ создан! Файл: $file_name #$i");
        }
        // if(PHPExcel_Shared_Date::isDateTime($cell_date)) {
            // $date = date($format="Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($cell_date->getValue()));
            // $date = date('Y-m-d',strtotime($worksheet->getCell('A'.$i)));
          // }
          // $service_name = trim($worksheet->getCell('E'.$i)->getValue());
          
          // if($summ!=null && $date!=null && $ls!=null && $service_name!=null && $source!=null){
            
            //     $result_service =$adb->pquery("SELECT serviceid FROM vtiger_service WHERE servicename = ?",array($service_name));
            //     $service_id = $adb->query_result($result_service,0,'serviceid');
            
            //     $result_contact =$adb->pquery("SELECT contactid FROM vtiger_contactscf WHERE cf_1225 = ?",array($ls));
            //     $contact_id = $adb->query_result($result_contact,0,'contactid');
            //     // echo $ls,"\r\n";
            //     
          //     // echo "<pre>";
          //     // var_dump($ls);
          //     // // var_dump($contact_id);
          //     // // echo$cell_date->getValue();
          //     // echo "</pre>";
              
          // }
          // else {
          //     throw new Exception("Есть пустые значения в таблице");
          // }
          // echo $date."\r\n";
        }
        echo "</pre>";
        exit();
      // exit();
      // $value = $exel->getActiveSheet()->getCell('A2');
      
      // echo "<pre>";
      // var_dump($payment);
  // // var_dump($file_name);
  // // var_dump($entityData->get('filename'));
      // echo "<pre>";
      // exit();
      // exit();


      // require_once "Classes/Pcontact$result_contact.php";
      
// exit();
    }
