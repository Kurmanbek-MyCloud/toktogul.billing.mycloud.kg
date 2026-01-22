<?php /* Smarty version Smarty-3.1.19, created on 2020-11-06 09:58:08
         compiled from "/var/www/html/bill/customerportal/layouts/default/templates/MetersData/partials/IndexContentAfter.tpl" */ ?>
<?php /*%%SmartyHeaderCode:54027155fa2a4daaf9cd9-85055246%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '98d1d6c9d2b1f5e009f9431a658d0739d1722280' => 
    array (
      0 => '/var/www/html/bill/customerportal/layouts/default/templates/MetersData/partials/IndexContentAfter.tpl',
      1 => 1604656681,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '54027155fa2a4daaf9cd9-85055246',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5fa2a4dab08a57_74493246',
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5fa2a4dab08a57_74493246')) {function content_5fa2a4dab08a57_74493246($_smarty_tpl) {?>


    <script type="text/ng-template" id="editRecordModalMetersData.template">
        <form class="form form-vertical" novalidate="novalidate" name="metersDataForm">
            <div class="modal-header">
                <button type="button" class="close" ng-click="cancel()" title="Close">&times;</button>
                <h4 class="modal-title">Добавить показания</h4>
            </div>
            <div class="modal-body" scroll-me="{'height':'350px'}">
                <div class="form-group">
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-sm-4 col-md-4 col-lg-4">
                            <div>
                                <label>Газ</label>
                                <input type="text" class="form-control" ng-model="data['gas']" >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" ng-click="cancel()" translate="Cancel">Cancel</button>
                <button type="submit" class="btn btn-success" ng-click="save(true)" translate="Save">Save</button>
            </div>
        </form>
    </script>

<?php }} ?>
