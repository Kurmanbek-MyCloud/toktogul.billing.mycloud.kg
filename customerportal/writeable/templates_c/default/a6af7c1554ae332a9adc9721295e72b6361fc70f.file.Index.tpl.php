<?php /* Smarty version Smarty-3.1.19, created on 2019-04-25 08:34:34
         compiled from "/var/www/html/vtiger/customerportal/layouts/default/templates/Faq/Index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:5429964475cc1711adc1e42-28493059%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a6af7c1554ae332a9adc9721295e72b6361fc70f' => 
    array (
      0 => '/var/www/html/vtiger/customerportal/layouts/default/templates/Faq/Index.tpl',
      1 => 1549886704,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '5429964475cc1711adc1e42-28493059',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5cc1711adcd5e7_56952636',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5cc1711adcd5e7_56952636')) {function content_5cc1711adcd5e7_56952636($_smarty_tpl) {?>

<div class="container-fluid"  ng-controller="<?php echo portal_componentjs_class($_smarty_tpl->tpl_vars['MODULE']->value,'IndexView_Component');?>
">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <?php echo $_smarty_tpl->getSubTemplate (portal_template_resolve($_smarty_tpl->tpl_vars['MODULE']->value,"partials/IndexContent.tpl"), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

        </div>
    </div>
</div>
<?php }} ?>
