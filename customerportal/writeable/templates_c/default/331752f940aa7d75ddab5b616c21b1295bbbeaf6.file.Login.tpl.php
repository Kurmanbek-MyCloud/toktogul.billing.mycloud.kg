<?php /* Smarty version Smarty-3.1.19, created on 2019-02-13 13:55:00
         compiled from "/var/www/html/vtiger/customerportal/layouts/default/templates/Portal/Login.tpl" */ ?>
<?php /*%%SmartyHeaderCode:12477671395c63cd54c4caa1-89832186%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '331752f940aa7d75ddab5b616c21b1295bbbeaf6' => 
    array (
      0 => '/var/www/html/vtiger/customerportal/layouts/default/templates/Portal/Login.tpl',
      1 => 1549886706,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12477671395c63cd54c4caa1-89832186',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5c63cd54d5f142_25053539',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5c63cd54d5f142_25053539')) {function content_5c63cd54d5f142_25053539($_smarty_tpl) {?>

<div class="container-fluid">
    <br>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">&nbsp;</div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="padding-top: 50px;">
                    <div class="login-form">
                        <form class="form-horizontal" novalidate="novalidate" ng-submit="makeAutoComplete();login(loginForm.$valid)" name="loginForm">
                            <h4 style="text-align:center;"translate="Please provide your portal credentials">Please provide your portal credentials</h4>
                            <hr>
                            <div class="form-group">
                                <label for="Email" translate="E-mail" class="col-sm-4 control-label">Email</label>
                                <div class="col-sm-8">
                                    <input type="text" ng-model-options="{updateOn:'blur'}" ng-pattern='/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/' class="form-control"  ng-model="username" name="username" ng-required="true" autofill="autofill">
                                    <!-- SalesPlatform.ru begin --><!-- <span class="text-danger" ng-if="loginForm.username.$error.pattern">Please enter a valid email address.</span> --><span class="text-danger" translate="Please enter a valid email address" ng-if="loginForm.username.$error.pattern">Please enter a valid email address.</span><!-- SalesPlatform.ru end -->
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Password" translate="Password" class="col-sm-4 control-label">Password</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control"  ng-model="password" name="password" ng-required="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="language" translate="Language" class="col-sm-4 control-label">Language</label>
                                <div class="col-sm-4">
                                    <select name="language" ng-model="language" ng-change="setLanguage(language)"  class="select form-control" placeholder="Language">
                                        <!-- SalesPlatform.ru begin -->
                                        <option value="ru_ru">RU Russian</option>
                                        <!-- SalesPlatform.ru end -->
                                        <option value="en_us">US English</option>
                                        <option value="de_de">DE Deutsch</option>
                                        <option value="pt_br">PT Brasil</option>
                                        <option value="fr_fr">Francais</option>
                                        <option value="tr_tr">Turkce Dil Paketi</option>
                                        <option value="es_es">ES Spanish</option>
                                        <option value="nl_nl">NL-Dutch</option>
                                        <option value="zh_cn">简体中文</option>
                                        <option value="zh_tw">繁體中文</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" ng-if="loginFailed && !loginForm.username.$error.pattern">
                                <label for="  " class="col-sm-4">
                                </label>
                                <!-- SalesPlatform.ru begin --><!-- <div class="col-sm-8 text-danger">{{loginMessage}}</div>--><div class="col-sm-8 text-danger">{{loginMessage | translate}}</div>
                            </div>
                              <div class="form-group" ng-if="noUserName">
                                <label ng-hide="loginForm.username.$dirty && loginForm.username.$viewValue!==''" for="  " class="col-sm-4">
                                </label>
                                <div class="col-sm-8 text-danger" ng-hide="loginForm.username.$dirty && loginForm.username.$viewValue!==''">Enter your email address.</div>
                            </div>

                              <div class="form-group" ng-if="noPassword">
                                <label ng-hide="loginForm.username.$invalid && loginForm.username.$viewValue!==''" for="  " class="col-sm-4">
                                </label>
                                <div class="col-sm-8 text-danger" ng-hide="loginForm.username.$invalid && loginForm.username.$viewValue!==''">Enter your password.</div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-8">
                                    <button type="submit" translate="Sign in" class="btn btn-success">Sign in</button>
                                    <a href="#" class="text-info forgot-password" ng-click="forgotPassword()">
                                        {{'Forgot password?' | translate}}
                                    </a>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">&nbsp;</div>
            </div>
        </div>
    </div>
</div>

    <script type="text/ng-template" id="forgotPassword.template">
        <div class="modal-header">
        <button type="button" class="close" ng-click="cancel()" title="Close">×</button>
        <h3 class="modal-title">{{'Forgot Password'|translate}}</h3>
        </div>
        <form name="forgotPassword"  ng-submit="updatePassword()" class="form-horizontal" role="form">
        <div class="modal-body">
        <div class="form-group">
        <label class="col-sm-4 control-label">{{'E-mail'|translate}}</label>
        <div class="col-sm-5">
        <input ng-model="data.email" name="email" ng-pattern='/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/' type="email" class="form-control" required></input>
        <span class="text-danger"  ng-show="data.email===undefined">Enter a valid email address</span>
        </div>
        </div>
        </div>
        <div class="modal-footer">
          <button ng-if="data.email" class="btn btn-success" type="submit">{{'Submit'|translate}}</button>
        </div>
      </form>
    </script>

<?php }} ?>
