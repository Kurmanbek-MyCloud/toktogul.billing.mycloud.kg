{strip}
	{assign var=TITLE value={vtranslate('NOTIFICATIONS_TITLE', $MODULE)}}
	<div class="modal-dialog modal-lg">
		<div class="modal-content" style='width: 525px; left:23%;'>
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
			<div id="modal-content-wrp">
				{if count($NOTIFICATIONS_LIST) neq 0}
					<ul id="modal-notifs-menu">
					{foreach item=VAL key=ID from=$NOTIFICATIONS_LIST}
						{assign var=TMODULE value=$VAL['module']}
						{if $TMODULE eq "Leads"}
							{assign var=MENTIONED value="лидах"}
							{assign var=ICON value="vicon-leads"}
						{else if $TMODULE eq "Potentials"}
							{assign var=MENTIONED value="сделках"}
							{assign var=ICON value="vicon-potentials"}
						{else if $TMODULE eq "Events"}
							{assign var=MENTIONED value="активностях"}
							{assign var=ICON value="fa fa-calendar"}
						{else if $TMODULE eq "SalesOrder"}
							{assign var=MENTIONED value="заказах"}
							{assign var=ICON value="vicon-salesorder"}
						{/if}
						<li class="notif_row{if $VAL['seen'] neq 0} hd_seen{/if}" data-id="{$ID}">
							<span class="module-icon"><i class="{$ICON}"></i></span>
							<a href="{$VAL['link']}" class="notifs_row_wrp">
								<h3 class="notifs_text">{$VAL['text']}</h3>
								{if !empty($VAL['description'])} <h3 class="notifs_desc">{$VAL['description']}</h3>{/if}
								<div class="notifs_auth_wrp">
									<span class="notifs_auth_lbl">Назначил(а):</span>
									<span class="notifs_auth_val">{$VAL['added_by']}</span>
								</div>
								<h3 class="notifs_date">{$VAL['added_time']}</h3>
							</a>
							<span onclick="return Vtiger_Index_Js.removeNotif($(this).closest('li'))" class="notif_remove" title="Удалить"></span>
						</li>
					{/foreach}
					</ul>
				{else}
					<h3 id="notifs_empty">Уведомлений нет</h3>
				{/if}
			</div>
		</div>
	</div>
{/strip}
<style type="text/css">
#modal-content-wrp {
	margin: 15px;
}
#notifs_empty {
	text-align: center;
	font-size: 15px;
	font-weight: bold;
}
#modal-notifs-menu {
	margin: 0 !important;
}
.hd_seen {
	color: #666;
	position: relative;
}
.notifs_row_wrp:hover .notifs_text {
	color: #369;
}
.notifs_row_wrp:hover .notif_remove:before,
.notifs_row_wrp:hover .notifs_desc,
.notifs_row_wrp:hover .notifs_auth_wrp > *{
	color: #666 !important;
}
.notif_remove:before {
	content: "\f00d";
	font-size: 14px;
	color: #666;
	font-weight: 900;
	font-family: "Font Awesome 5 Free";
}
.notif_remove {
	position: absolute;
	left: -20px;
	top: 0;
	cursor: pointer;
}
.notif_remove:hover:before {
	color: #999 !important;
}
.notif_row:not(:last-child) {
	padding-bottom: 10px;
	border-bottom: 1px solid #ccc;
}
.notifs_auth_lbl {
	margin-right: 10px;
}
.notifs_auth_val {
	font-weight: bold;
}
.notif_row {
	margin: 15px 0;
	display: flex;
	position: relative;
	flex-direction: row;
	list-style-type: none;
}
.notifs_row_wrp > *:not(:first-child) {
	margin-top: 5px !important;
}
.notifs_auth_wrp {
	display: flex;
	flex-direction: row;
}
.notifs_auth_wrp > * {
	font-size: 11px;
}
.notif_row h3 {
	margin: 0;
}
.notifs_text {
	font-weight: bold;
	font-size: 14px !important;
}
.notifs_row_wrp {
	margin-left: 15px;
	overflow: hidden;
	display: block;
}
.notifs_desc {
	font-size: 11px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
.notifs_date {
	color: #252525;
	font-size: 11px !important;
}
</style>