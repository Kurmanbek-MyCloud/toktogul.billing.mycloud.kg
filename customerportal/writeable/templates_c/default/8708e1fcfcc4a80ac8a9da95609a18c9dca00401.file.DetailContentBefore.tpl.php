<?php /* Smarty version Smarty-3.1.19, created on 2020-10-06 19:40:01
         compiled from "/var/www/html/bill/customerportal/layouts/default/templates/Portal/partials/DetailContentBefore.tpl" */ ?>
<?php /*%%SmartyHeaderCode:20722060125f7c585b699e78-02647890%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8708e1fcfcc4a80ac8a9da95609a18c9dca00401' => 
    array (
      0 => '/var/www/html/bill/customerportal/layouts/default/templates/Portal/partials/DetailContentBefore.tpl',
      1 => 1602013170,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '20722060125f7c585b699e78-02647890',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5f7c585b6ce8b0_11964922',
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5f7c585b6ce8b0_11964922')) {function content_5f7c585b6ce8b0_11964922($_smarty_tpl) {?>


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
