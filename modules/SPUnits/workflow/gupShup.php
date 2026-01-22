<?php 

function WhatsAppMessageSender($crm_entity){
    vimport('includes.http.Request');
    vimport('includes.runtime.Globals');
    vimport('includes.runtime.BaseModel');
    vimport('includes.runtime.Controller');
    vimport('includes.runtime.LanguageHandler');
    global $current_user;
    $current_user = Users::getActiveAdminUser();
    
    $bill = Vtiger_Record_Model::getCleanInstance("Contacts");
    $bill->set('lastname', 'test');
    $bill->set('assigned_user_id', $current_user->id);
    $bill->set('mode', 'create');
    $bill->save();

}