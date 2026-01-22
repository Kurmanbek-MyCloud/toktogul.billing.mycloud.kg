<?php /* Smarty version Smarty-3.1.19, created on 2019-10-22 07:15:37
         compiled from "/var/www/html/vtiger/customerportal/layouts/default/templates/Portal/partials/DetailContentBefore.tpl" */ ?>
<?php /*%%SmartyHeaderCode:403078595daeac99584675-35196455%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a83a45d06fd6f92768a9ab7c73977f2db2cce5ea' => 
    array (
      0 => '/var/www/html/vtiger/customerportal/layouts/default/templates/Portal/partials/DetailContentBefore.tpl',
      1 => 1549886706,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '403078595daeac99584675-35196455',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5daeac99635a28_76231363',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5daeac99635a28_76231363')) {function content_5daeac99635a28_76231363($_smarty_tpl) {?>


    <div class="col-lg-12 col-md-12 col-sm-7 col-xs-7 detail-header detail-header-row">
      <h3 class="fsmall">
        <detail-navigator>
          <span>
            <a ng-click="navigateBack(module)" style="font-size:small;">{{ptitle}}
            </a>
            </span>
        </detail-navigator>{{record[header]}}
        <button ng-if="isEditable" class="btn btn-primary attach-files-ticket" ng-click="editRecord(module,id)">{{'Edit'|translate}} {{ptitle}}</button>
      </h3>
    </div>
</div>

<hr class="hrHeader">
<div class="container-fluid">

<?php }} ?>
