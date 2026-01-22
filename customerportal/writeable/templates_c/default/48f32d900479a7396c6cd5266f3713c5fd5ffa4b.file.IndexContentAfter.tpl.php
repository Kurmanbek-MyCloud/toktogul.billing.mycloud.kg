<?php /* Smarty version Smarty-3.1.19, created on 2022-02-04 11:07:45
         compiled from "/var/www/html/obill/customerportal/layouts/default/templates/MetersData/partials/IndexContentAfter.tpl" */ ?>
<?php /*%%SmartyHeaderCode:211716343261fd0901b3d551-95538429%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '48f32d900479a7396c6cd5266f3713c5fd5ffa4b' => 
    array (
      0 => '/var/www/html/obill/customerportal/layouts/default/templates/MetersData/partials/IndexContentAfter.tpl',
      1 => 1633080132,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '211716343261fd0901b3d551-95538429',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_61fd0901b40365_64441903',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_61fd0901b40365_64441903')) {function content_61fd0901b40365_64441903($_smarty_tpl) {?>


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
