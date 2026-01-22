{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
   <div class="col-lg-6 col-md-6 col-sm-6">

	   <div class="flex-container">

		   <div class="record-header clearfix">
               {* SalesPlatform.ru begin *}
               {* <div class="hidden-sm hidden-xs recordImage bgcontacts app-{$SELECTED_MENU_CATEGORY}">*}
			   <div class="hidden-sm hidden-xs recordImage">
                   {* SalesPlatform.ru end *}
                   {assign var=IMAGE_DETAILS value=$RECORD->getImageDetails()}
                   {foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
                       {if !empty($IMAGE_INFO.path)}
						   <img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" width="100%" height="100%" align="left"><br>
                       {else}
						   <img src="{vimage_path('summary_Contact.png')}" class="summaryImg"/>
                       {/if}
                   {/foreach}
                   {if empty($IMAGE_DETAILS)}
					   <div class="name"><span><strong>{$MODULE_MODEL->getModuleIcon()}</strong></span></div>
                   {/if}
			   </div>
			   <div class="recordBasicInfo">
				   <div class="info-row">
					   <h4>
						   <span class="recordLabel  pushDown" title="{$RECORD->getDisplayValue('salutationtype')}&nbsp;{$RECORD->getName()}">
					 			{assign var=COUNTER value=0}
							  {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
								  {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
								  {if $FIELD_MODEL->getPermissions()}
									  <span class="{$NAME_FIELD}">
									   {if $RECORD->getDisplayValue('salutationtype') && $FIELD_MODEL->getName() eq 'firstname'}
										   {$RECORD->getDisplayValue('salutationtype')}&nbsp;
									   {/if}
										  {trim($RECORD->get($NAME_FIELD))}
								   </span>
									  {if $COUNTER eq 0 && ($RECORD->get($NAME_FIELD))}&nbsp;{assign var=COUNTER value=$COUNTER+1}{/if}
								  {/if}
							  {/foreach}
				  			</span>
					   </h4>
				   </div>
                   {*{include file="DetailViewHeaderFieldsView.tpl"|vtemplate_path:$MODULE}*}
				   <div class="info-row">
{* 
					   <span class="account-label">Лицевой счет: </span>
					   <span class="account-value">{$RECORD->get('cf_1225')}</span>
					   <br> *}
					{* {if Users_Record_Model::getCurrentUserModel()->getId() == '1'} *}
					   <span class="balance-label">Переплата: </span>
					   <span class="balance-value">{if $RECORD->getTotalDebt() < 0} {$RECORD->getTotalDebt()} {else}0{/if}⊆</span>
					   <br>
					   <span class="debt-label">Общая задолженность: </span>
					   <span class="debt-value">{if $RECORD->getTotalDebt() > 0} {$RECORD->getTotalDebt()} {else}0{/if} ⊆</span>
							{* <pre>
							{var_dump($RECORD->getTotalDebt())}
							{var_dump($key+1)}
							</pre> *}
					{* {/if} *}
							<br>
							{if $RECORD->getHousesInfo()|@count > 1}
								<span class="debt-label">Лицевые счета: </span>
							{else}
								<span class="debt-label">Лицевой счёт: </span>
							{/if}
						{foreach from=$RECORD->getHousesInfo() item=RowData key=key name=name}
							{assign var="link" value="index.php?module=Flats&view=Detail&record="|cat:$RowData['flatsid']|cat:"&app=MARKETING"}
							<span class="debt-value"><a href = {$link} >{$RowData['cf_1420']}</a></span>
							{if $RECORD->getHousesInfo()|@count > 0 && $key+1 != $RECORD->getHousesInfo()|@count}
								, 
							{/if}
						{/foreach}
							
								
				   </div>
			   </div>
		   </div>

	   </div>

   </div>
{/strip}