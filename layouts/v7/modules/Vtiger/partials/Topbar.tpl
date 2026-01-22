{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
  {include file="modules/Vtiger/Header.tpl"}

  {assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
  <nav class="navbar navbar-default navbar-fixed-top app-fixed-navbar">
    <div class="container-fluid global-nav">
      <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 app-navigator-container">
          <div class="row">
            <div id="appnavigator" class="col-sm-2 col-xs-2 cursorPointer app-switcher-container"
              data-app-class="{if $MODULE eq 'Home' || !$MODULE}fa-dashboard{else}{$APP_IMAGE_MAP[$SELECTED_MENU_CATEGORY]}{/if}">
              <div class="row app-navigator">
                <span class="app-icon fa fa-bars"></span>
              </div>
            </div>
            <div class="logo-container col-lg-9 col-md-9 col-sm-9 col-xs-9">
              <div class="row">
                <a href="index.php" class="company-logo">
                  <img src="{$COMPANY_LOGO->get('imagepath')}" alt="{$COMPANY_LOGO->get('alt')}" />
                </a>
                {* {$COMPANY_LOGO->get('imagepath')} *}
              </div>
            </div>
          </div>
        </div>
        <div class="search-links-container col-md-3 col-lg-3 hidden-sm" style="margin-left: -73px;">
          <div class="search-link hidden-xs">
            <span class="fa fa-search" aria-hidden="true"></span>
            {*Salesplatform.ru begin Vtiger7 global search support*}
            {* <input id="globalSearchValue" class="keyword-input" type="text" onfocusout="setTimeout(()=>$('.searchContact').remove(),300)" placeholder="{vtranslate('LBL_TYPE_SEARCH')}"
              value="{$GLOBAL_SEARCH_VALUE}"> *}
            {* <input class="keyword-input" type="text" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" value="{$GLOBAL_SEARCH_VALUE}">*}
            {*Salesplatform.ru end Vtiger7 global search support *}
            {*SearchContact START*}
            <style type="text/css">
              .searchContact{
                border: 1px solid lightgrey;
                position: absolute;
                width: calc(100% - 30px);
                margin-top: 37px;
                z-index: 1;
                background-color: white;
              }
              .searchContact .resultRow{
                padding: 4px 2px;
              }
              .searchContact .resultRow:hover{
                background-color: lightgrey;
                color: black;
              }
            </style>
            <input id="globalSearchValue" class="keyword-input" type="text" onfocusout="setTimeout(()=>$('.searchContact').remove(),300)" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" value="{$GLOBAL_SEARCH_VALUE}">
            {literal}
            <script type="text/javascript">
              jQuery('.keyword-input').keyup(function(e) {
                if(e.keyCode>=65 && e.keyCode<=90 || e.keyCode == 8 || e.keyCode == 46){
                  var inputText = $('.keyword-input').val().trim()
                  if(inputText.length>=3){
                    $('.searchContact').remove()
                    var params = {
                        'module': 'Vtiger',
                        'action': 'SearchContact',
                        'input' :inputText
                    };
                    app.request.post({data: params}).then(function (err, result) {
                        var result = JSON.parse(result)
                        if (result.length>0){
                          var html = `<div class="searchContact">`
                          for(var row of result){
                              html+=`<a href="index.php?module=Contacts&view=Detail&record=${row[0]}" target="_blank"><div class="resultRow">${row[1]}</div></a>`  
                          }
                          html+=`</div>`
                          $('.search-links-container').append(html)
                        }
                        
                    })
                  }
                }
              })
            </script>
            {/literal}
            {*SearchContact END*}
            <span id="adv-search" class="adv-search fa fa-chevron-circle-down pull-right cursorPointer"
              aria-hidden="true"></span>
          </div>
        </div>
        <div id="navbar" class="col-sm-6 col-md-3 col-lg-3 collapse navbar-collapse navbar-right global-actions">
          <ul class="nav navbar-nav">
            {* crmkg start *}
            {* <li>
            <div class="quick-link" style="margin-top: 10px">
              <a href="index.php?module=Houses&view=List&app=MARKETING" title="Улицы">
                <span class="module-icon"><i class="fas fa-road" style="font-size: 25px" title="Улицы"></i></span>
              </a>
            </div>
          </li> *}
          <li>
          <div class="quick-link" style="margin-top: 10px">
            <a href="index.php?module=Flats&view=List&app=MARKETING" title="Дома">
              <span class="module-icon"><i class="fas fa-home" style="font-size: 25px" title="Дома"></i></span>
            </a>
          </div>
        </li>
        <li>
        <div class="quick-link" style="margin-top: 10px">
          <a href="index.php?module=Meters&view=List&app=MARKETING" title="Счетчики">
            <span class="module-icon"><i class="fas fa-tachometer-alt" style="font-size: 25px"></i></span>
          </a>
        </div>
        </li>
        <li>
          <div class="quick-link" style="margin-top: 10px">
            <a href="index.php?module=MetersData&view=List&app=MARKETING" title="Показания">
              <span class="module-icon">
              <img style='width:25px;' src ="counter.png"/>
              {* <i class="fas fa-tachometer-alt" style="font-size: 25px"></i> *}
              </span>
            </a>
          </div>
        </li>
        
          {* <li>
          <div class="quick-link" style="margin-top: 10px">
            <a href="index.php?module=Meters&view=List&app=MARKETING" title="Счетчики">
              <span class="module-icon"><i class="fas fa-tachometer-alt" style="font-size: 25px"></i></span>
            </a>
          </div>
        </li> *}
            <li>  
              <div class="quick-link" style="margin-top: 10px">
                <a href="index.php?module=SPPayments&view=List&app=INVENTORY" title="Платежи">
                  <span class="module-icon"><i class="vicon-sppayments"></i></span>
                </a>
              </div>
            </li>
            <li>
              <div class="quick-link" style="margin-top: 10px">
                <a href="index.php?module=Invoice&view=List" title="Счета">
                  <span class="module-icon"><i class="vicon-invoice" title="Счета"></i></span>
                </a>
              </div>
            </li>
           
            <li>
              <div class="quick-link" style="margin-top: 10px">
                <a href="index.php?module=HelpDesk&view=List" title="Обращения">
                  <span class="module-icon"><i class="vicon-helpdesk" title="Обращения"></i></span>
                </a>
              </div>
            </li>
            <li>
              <div class="quick-link" style="margin-top: 10px">
                <a href="index.php?module=Contacts&view=List" title="Абоненты">
                  <span class="module-icon"><i class="vicon-contacts" title="Абоненты"></i></span>
                </a>
              </div>
            </li>
            {* <li>
              <div class="quick-link" style="margin-top: 10px">
                <a href="index.php?module=Accounts&view=List" title="Контрагенты">
                  <span class="module-icon"><i class="vicon-accounts" title="Контрагенты"></i></span>
                </a>
              </div>
            </li> *}
            <li>
              <div class="quick-link">
                <a id="notif_lnk" title="Уведомления">
                  <span class="module-icon"><i onclick="return Vtiger_Index_Js.showNotifs()" id="hd_notifs"
                      title="Уведомления"></i></span>
                </a>
              </div>
            </li>
            {* crmkg end *}
            {if $TEST}
            <li>
              <div class="dropdown">
                <div class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                  <a href="#" id="menubar_quickCreate" class="qc-button fa fa-plus-circle"
                    title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" aria-hidden="true"></a>
                </div>
                <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1" style="width:500px;">
                  <li class="title" style="padding: 5px 0 0 15px;">
                    <strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong>
                  </li>
                  <hr />
                  <li id="quickCreateModules" style="padding: 0 5px;">
                    <div class="col-lg-12" style="padding-bottom:15px;">
                      {foreach key=moduleName item=moduleModel from=$QUICK_CREATE_MODULES}
                        {if $moduleModel->isPermitted('CreateView') || $moduleModel->isPermitted('EditView')}
                          {assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
                          {assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
                          {assign var=hideDiv value={!$moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('EditView')}}
                          {if $quickCreateModule == '1'}
                            {if $count % 3 == 0}
                              <div class="row">
                              {/if}
                              {* Adding two links,Event and Task if module is Calendar *}
                              {if $singularLabel == 'SINGLE_Calendar'}
                                {assign var='singularLabel' value='LBL_TASK'}
                                <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
                                  <a id="menubar_quickCreate_Events" class="quickCreateModule" data-name="Events"
                                    data-url="index.php?module=Events&view=QuickCreateAjax"
                                    href="javascript:void(0)">{$moduleModel->getModuleIcon('Event')}<span
                                      class="quick-create-module">{vtranslate('LBL_EVENT',$moduleName)}</span></a>
                                </div>
                                {if $count % 3 == 2}
                                </div>
                                <br>
                                <div class="row">
                                {/if}
                                <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
                                  <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule"
                                    data-name="{$moduleModel->getName()}" data-url="{$moduleModel->getQuickCreateUrl()}"
                                    href="javascript:void(0)">{$moduleModel->getModuleIcon('Task')}<span
                                      class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span></a>
                                </div>
                                {if !$hideDiv}
                                  {assign var='count' value=$count+1}
                                {/if}
                              {else if $singularLabel == 'SINGLE_Documents'}
                                <div
                                  class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if} dropdown">
                                  <a id="menubar_quickCreate_{$moduleModel->getName()}"
                                    class="quickCreateModuleSubmenu dropdown-toggle" data-name="{$moduleModel->getName()}"
                                    data-toggle="dropdown" data-url="{$moduleModel->getQuickCreateUrl()}"
                                    href="javascript:void(0)">
                                    {$moduleModel->getModuleIcon()}
                                    <span class="quick-create-module">
                                      {vtranslate($singularLabel,$moduleName)}
                                      <i class="fa fa-caret-down quickcreateMoreDropdownAction"></i>
                                    </span>
                                  </a>
                                  <ul class="dropdown-menu quickcreateMoreDropdown"
                                    aria-labelledby="menubar_quickCreate_{$moduleModel->getName()}">
                                    <li class="dropdown-header"><i class="fa fa-upload"></i>
                                      {vtranslate('LBL_FILE_UPLOAD', $moduleName)}</li>
                                    <li id="VtigerAction">
                                      <a href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
                                        <img style="  margin-top: -3px;margin-right: 4%;" title="Vtiger" alt="Vtiger"
                                          src="layouts/v7/skins//images/Vtiger.png">
                                        {vtranslate('LBL_TO_SERVICE', $moduleName, {vtranslate('LBL_VTIGER', $moduleName)})}
                                      </a>
                                    </li>
                                    <li class="dropdown-header"><i class="fa fa-link"></i>
                                      {vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $moduleName)}</li>
                                    <li id="shareDocument"><a href="javascript:Documents_Index_Js.createDocument('E')">&nbsp;<i
                                          class="fa fa-external-link"></i>&nbsp;&nbsp;
                                        {vtranslate('LBL_FROM_SERVICE', $moduleName, {vtranslate('LBL_FILE_URL', $moduleName)})}</a>
                                    </li>
                                    <li role="separator" class="divider"></li>
                                    <li id="createDocument"><a href="javascript:Documents_Index_Js.createDocument('W')"><i
                                          class="fa fa-file-text"></i>
                                        {vtranslate('LBL_CREATE_NEW', $moduleName, {vtranslate('SINGLE_Documents', $moduleName)})}</a>
                                    </li>
                                  </ul>
                                </div>
                              {else}
                                <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
                                  <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule"
                                    data-name="{$moduleModel->getName()}" data-url="{$moduleModel->getQuickCreateUrl()}"
                                    href="javascript:void(0)">
                                    {$moduleModel->getModuleIcon()}
                                    <span class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span>
                                  </a>
                                </div>
                              {/if}
                              {if $count % 3 == 2}
                              </div>
                              <br>
                            {/if}
                            {if !$hideDiv}
                              {assign var='count' value=$count+1}
                            {/if}
                          {/if}
                        {/if}
                      {/foreach}
                    </div>
                  </li>
                </ul>
              </div>
            </li>
            {/if}
            <li>
              <div class="dropdown">
                <div class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                  <a href="#" id="menubar_quickCreate" class="qc-button fa fa-plus-circle" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" aria-hidden="true"></a>
                </div>
                <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1" style="width:500px;">
                  <li class="title" style="padding: 5px 0 0 15px;">
                    <strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong>
                  </li>
                  <hr/>
                  <li id="quickCreateModules" style="padding: 0 5px;">
                    <div class="col-lg-12" style="padding-bottom:15px;">
                      {foreach key=moduleName item=moduleModel from=$QUICK_CREATE_MODULES}
                        {if $moduleModel->isPermitted('CreateView') || $moduleModel->isPermitted('EditView')}
                          {assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
                          {assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
                          {assign var=hideDiv value={!$moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('EditView')}}
                          {if $quickCreateModule == '1'}
                            {if $count % 3 == 0}
                              <div class="row">
                              {/if}
                              {* Adding two links,Event and Task if module is Calendar *}
                              {if $singularLabel == 'SINGLE_Calendar'}
                                {assign var='singularLabel' value='LBL_TASK'}
                                <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
                                  <a id="menubar_quickCreate_Events" class="quickCreateModule" data-name="Events"
                                     data-url="index.php?module=Events&view=QuickCreateAjax" href="javascript:void(0)">{$moduleModel->getModuleIcon('Event')}<span class="quick-create-module">{vtranslate('LBL_EVENT',$moduleName)}</span></a>
                                </div>
                                {if $count % 3 == 2}
                                  </div>
                                  <br>
                                  <div class="row">
                                {/if}
                                {* crmkg start *}
                                {*<div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
                                  <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
                                     data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">{$moduleModel->getModuleIcon('Task')}<span class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span></a>
                                </div>*}
                                {* crmkg end *}
                                {if !$hideDiv}
                                  {assign var='count' value=$count+1}
                                {/if}
                              {else if $singularLabel == 'SINGLE_Documents'}
                                <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if} dropdown">
                                  <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModuleSubmenu dropdown-toggle" data-name="{$moduleModel->getName()}" data-toggle="dropdown" 
                                     data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                    {$moduleModel->getModuleIcon()}
                                    <span class="quick-create-module">
                                      {vtranslate($singularLabel,$moduleName)}
                                      <i class="fa fa-caret-down quickcreateMoreDropdownAction"></i>
                                    </span>
                                  </a>
                                  <ul class="dropdown-menu quickcreateMoreDropdown" aria-labelledby="menubar_quickCreate_{$moduleModel->getName()}">
                                    <li class="dropdown-header"><i class="fa fa-upload"></i> {vtranslate('LBL_FILE_UPLOAD', $moduleName)}</li>
                                    <li id="VtigerAction">
                                      <a href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
                                        <img style="  margin-top: -3px;margin-right: 4%;" title="Vtiger" alt="Vtiger" src="layouts/v7/skins//images/Vtiger.png">
                                        {vtranslate('LBL_TO_SERVICE', $moduleName, {vtranslate('LBL_VTIGER', $moduleName)})}
                                      </a>
                                    </li>
                                    <li class="dropdown-header"><i class="fa fa-link"></i> {vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $moduleName)}</li>
                                    <li id="shareDocument"><a href="javascript:Documents_Index_Js.createDocument('E')">&nbsp;<i class="fa fa-external-link"></i>&nbsp;&nbsp; {vtranslate('LBL_FROM_SERVICE', $moduleName, {vtranslate('LBL_FILE_URL', $moduleName)})}</a></li>
                                    <li role="separator" class="divider"></li>
                                    <li id="createDocument"><a href="javascript:Documents_Index_Js.createDocument('W')"><i class="fa fa-file-text"></i> {vtranslate('LBL_CREATE_NEW', $moduleName, {vtranslate('SINGLE_Documents', $moduleName)})}</a></li>
                                  </ul>
                                </div>
                              {else}
                                <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
                                  <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
                                     data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                    {$moduleModel->getModuleIcon()}
                                    <span class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span>
                                  </a>
                                </div>
                              {/if}
                              {if $count % 3 == 2}
                                </div>
                                <br>
                              {/if}
                            {if !$hideDiv}
                              {assign var='count' value=$count+1}
                            {/if}
                          {/if}
                        {/if}
                      {/foreach}
                    </div>
                  </li>
                </ul>
              </div>
            </li>
            {assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
            {assign var=CALENDAR_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Calendar')}
            {if $USER_PRIVILEGES_MODEL->hasModulePermission($CALENDAR_MODULE_MODEL->getId())}
              <li>
                <div><a href="index.php?module=Calendar&view={$CALENDAR_MODULE_MODEL->getDefaultViewName()}"
                    class="fa fa-calendar" title="{vtranslate('Calendar','Calendar')}" aria-hidden="true"></a></div>
              </li>
            {/if}
            {assign var=REPORTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Reports')}
            {if $USER_PRIVILEGES_MODEL->hasModulePermission($REPORTS_MODULE_MODEL->getId())}
              <li>
                <div><a href="index.php?module=Reports&view=List" class="fa fa-bar-chart"
                    title="{vtranslate('Reports','Reports')}" aria-hidden="true"></a></div>
              </li>
            {/if}
            <li>
              <div style="margin-top: 13px;">
                <a href="https://meet.google.com/vkj-jrws-gvm" target="_blank" title="Google Meet">
                  <img style='width:15px;' src ="Googlemeet.png"/>
                </a>
              </div>
            </li>
            {* SalesPlatform.ru begin *}
      
            {* SalesPlatform.ru end *}
            <li class="dropdown">
              <div style="margin-top: 15px;">
                <a href="#" class="userName dropdown-toggle" data-toggle="dropdown" role="button">
                  <span class="fa fa-user" aria-hidden="true" title="{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}
										  ({$USER_MODEL->get('user_name')})"></span>
                  <span class="link-text-xs-only hidden-lg hidden-md hidden-sm">{$USER_MODEL->getName()}</span>
                </a>
                <div class="dropdown-menu logout-content" role="menu">
                  <div class="row">
                    <div class="col-lg-4 col-sm-4">
                      {* SalesPlatform.ru begin *}
                      {*<div class="profile-img-container">*}
                      <div class="profile-img-container" style="background: white !important;">
                        {* SalesPlatform.ru end *}
                        {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                        {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
                          <i class='vicon-vtigeruser' style="font-size:90px"></i>
                        {else}
                          {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                            {if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
                              <img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" width="100px" height="100px">
                            {/if}
                          {/foreach}
                        {/if}
                      </div>
                    </div>
                    <div class="col-lg-8 col-sm-8">
                      <div class="profile-container">
                        <h4>{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}</h4>
                        <h5 class="textOverflowEllipsis" title='{$USER_MODEL->get('user_name')}'>
                          {$USER_MODEL->get('user_name')}</h5>
                        <p>{$USER_MODEL->getUserRoleName()}</p>
                      </div>
                    </div>
                  </div>
                  <div class="logout-footer clearfix">
                    <hr style="margin: 10px 0 !important">
                    <div class="">
                      <span class="pull-left">
                        <span class="fa fa-cogs"></span>
                        <a id="menubar_item_right_LBL_MY_PREFERENCES"
                          href="{$USER_MODEL->getPreferenceDetailViewUrl()}">{vtranslate('LBL_MY_PREFERENCES')}</a>
                      </span>
                      <span class="pull-right">
                        <span class="fa fa-power-off"></span>
                        <a id="menubar_item_right_LBL_SIGN_OUT"
                          href="index.php?module=Users&action=Logout">{vtranslate('LBL_SIGN_OUT')}</a>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  {/strip}
  <style type="text/css">
    #hd_notifs:before {
      font-family: "Font Awesome 5 Free";
      font-size: 16px;
      font-weight: 900;
      color: #252525;
      content: "\f0f3";
    }

    .hd_notifs_has:before {
      color: #bb0000 !important;
    }

    #hd_notifs {
      font-style: normal;
    }

    #notif_lnk {
      margin-left: 5px;
      padding: 0;
      height: 44px;
      display: flex;
      margin-right: 10px;
      align-items: center;
    }
    
    .searchContact{
      border: 1px solid lightgrey;
      position: absolute;
      width: calc(100% - 30px);
      margin-top: 37px;
      z-index: 1;
      background-color: white;
    }
    .searchContact .resultRow{
      padding: 4px 2px;
    }
    .searchContact .resultRow:hover{
      background-color: lightgrey;
      /*cursor: pointer;*/
      color: black;
    }
</style>