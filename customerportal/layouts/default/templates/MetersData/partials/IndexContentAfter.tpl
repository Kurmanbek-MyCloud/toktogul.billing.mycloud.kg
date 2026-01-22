{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.2
* ("License.txt"); You may not use this file except in compliance with the License
* The Original Code is: Vtiger CRM Open Source
* The Initial Developer of the Original Code is Vtiger.
* Portions created by Vtiger are Copyright (C) Vtiger.
* All Rights Reserved.
************************************************************************************}

{literal}
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
{/literal}
