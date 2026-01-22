{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<footer class="app-footer">
        {* SalesPlatform.ru begin *}
        <div class="pull-right footer-icons">
            <!--<small>{vtranslate('LBL_CONNECT_WITH_US', $MODULE)}&nbsp;</small>-->
            <span style="margin-right:15px;"><b>Наши контакты:</b> 0559 986 650, 0312-98-66-50</span>
			<!-- SalesPlatform begin #5822 -->
            <!-- <a href="http://community.salesplatform.ru/"><img src="layouts/vlayout/skins/images/forum.png"></a>
            &nbsp;<a href="https://twitter.com/salesplatformru"><img src="layouts/vlayout/skins/images/twitter.png"></a> -->
            <!--<a href="http://community.salesplatform.ru/" target="_blank" title="{vtranslate('Community', $MODULE)}"><i class="fa fa-comments"></i></a>
            <a href="https://twitter.com/salesplatformru" target="_blank" title="Twitter"><i class="fa fa-twitter"></i></a>
            <a href="https://vk.com/salesplatform" target="_blank" title="Vk"><i class="fa fa-vk"></i></a>
            <a href="https://youtube.com/salesplatform" target="_blank" title="YouTube"><i class="fa fa-youtube-play"></i></a>-->
            
			<!--<a href="https://www.facebook.com/MyCloud.KG" target="_blank" title="Facebook"><i style="background:#3b5998" class="fa fa-facebook"></i></a>-->
            <a href="https://www.facebook.com/CRM.TechnologiesKG/" target="_blank" title="Facebook"><i style="background:#3b5998" class="fa fa-facebook"></i></a>
            
			<!--<a href="https://www.instagram.com/mycloud.kg/" target="_blank" title="Instagram"><i style="background:#3f729b" class="fa fa-instagram"></i></a>-->
			<a href="https://www.instagram.com/crm_technologieskg/" target="_blank" title="Instagram"><i style="background:#3f729b" class="fa fa-instagram"></i></a>
            
			<!--<a href="https://twitter.com/mycloud_kg" target="_blank" title="Twitter"><i style="background:#0088cc" class="fa fa-twitter-square"></i></a>-->
			
			<a href="https://t.me/crmtech" target="_blank" title="Telegram"><i style="background:#0088cc" class="fab fa-telegram"></i></a>
            <!-- SalesPlatform end -->
        </div>
        {* SalesPlatform.ru end *}
	<p>Oimo Billing - © vtiger.com | © 2014 - {date('Y')} <a style="font-weight:700;" href="https://crm.kg/our-products/oimo-billing-zhkh" target="_blank">CRM Technologies Oimo</a></p>
	<!--<p>Oimo cloud CRM 1.0.1-04.{date('Y')} © vtiger.com | © 2016 - {date('Y')} <a style="font-weight:700;" href="https://crm.kg/" target="_blank">MyCloud.kg</a></p>-->
</footer>
</div>
<div id='overlayPage'>
	<!-- arrow is added to point arrow to the clicked element (Ex:- TaskManagement),
	any one can use this by adding "show" class to it -->
	<div class='arrow'></div>
	<div class='data'>
	</div>
</div>
<div id='helpPageOverlay'></div>
<div id="js_strings" class="hide noprint">{Zend_Json::encode($LANGUAGE_STRINGS)}</div>
<div class="modal myModal fade"></div>
{include file='JSResources.tpl'|@vtemplate_path}
<![if IE]>
<script src='https://livechat.chat2desk.com/packs/ie11-supporting-7c7048f2020b6d05293e.js'></script>
<![endif]>
</body>
</html>