<?php /* Smarty version Smarty-3.1.19, created on 2020-10-06 20:06:05
         compiled from "/var/www/html/bill/customerportal/layouts/default/templates/Portal/partials/DetailContent.tpl" */ ?>
<?php /*%%SmartyHeaderCode:4347392685f7c57b15b5a28-67795646%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fd9e3f7b3ff77544130036fe1a934f5d7749e1a8' => 
    array (
      0 => '/var/www/html/bill/customerportal/layouts/default/templates/Portal/partials/DetailContent.tpl',
      1 => 1602014763,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4347392685f7c57b15b5a28-67795646',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5f7c57b15b9116_17822324',
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5f7c57b15b9116_17822324')) {function content_5f7c57b15b9116_17822324($_smarty_tpl) {?>


    <div ng-class="{'col-lg-5 col-md-5 col-sm-12 col-xs-12 leftEditContent':splitContentView, 'col-lg-12 col-md-12 col-sm-12 col-xs-12 leftEditContent nosplit':!splitContentView}">
        <div class="container-fluid">
            <div class="row">
                <div class="row detailRow" ng-hide="fieldname=='id' || fieldname=='identifierName' || fieldname=='{{header}}' || fieldname=='documentExists' || fieldname=='referenceFields'"  ng-repeat="(fieldname, value) in record">
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                        <label class="fieldLabel" translate="{{fieldname}}"> {{fieldname}} </label>
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                        <!-- <span class="label label-default">{{value}}</span> -->
                        <span style="white-space: pre-line;" class="value detail-break">{{value}}</span>
                    </div>
                </div>
                <div class="row detailRow" ng-if="module == 'Documents'">
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                        <label ng-if="module=='Documents'" class="fieldLabel" translate="Attachments">Attachments</label>
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12" ng-if="documentExists">

                        <!-- SalesPlatform.ru begin -->
                        <!-- <button class="btn btn-primary" ng-click="downloadFile(module,id,parentId)" title="Download {{record[header]}}">Download</button>-->
                        <button class="btn btn-primary" ng-click="downloadFile(module,id,parentId)" translate="Download" title="Download {{record[header]}}">Download</button>
                        <!-- SalesPlatform.ru end -->

                    </div>
                </div>
            </div>
        </div>
    </div>

<?php }} ?>
