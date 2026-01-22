/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.2
 * ("License.txt"); You may not use this file except in compliance with the License
 * The Original Code is: Vtiger CRM Open Source
 * The Initial Developer of the Original Code is Vtiger.
 * Portions created by Vtiger are Copyright (C) Vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function MetersData_IndexView_Component($scope, $api, $webapp, $modal, sharedModalService, $translatePartialLoader) {

	if ($translatePartialLoader !== undefined) {
		$translatePartialLoader.addPart('home');
		$translatePartialLoader.addPart('MetersData');
	}

	var availableModules = JSON.parse(localStorage.getItem('modules'));
	var currentModule = 'MetersData';

	//set creatable true
	if (availableModules !== null && availableModules[ currentModule ] !== undefined) {
		$scope.isCreatable = availableModules[ currentModule ].create;
		$scope.filterPermissions = availableModules[currentModule].recordvisibility;
	}

	angular.extend(this, new Portal_IndexView_Component($scope, $api, $webapp, sharedModalService));

	$scope.$on('editRecordModalMetersData.Template', function () {
		$modal.open({
			templateUrl: 'editRecordModalMetersData.template',
			controller: MetersData_EditView_Component,
			backdrop: 'static',
			size: 'lg',
			keyboard: 'false',
			resolve: {
				record: function () {
					return {};
				},
				api: function () {
					return $api;
				},
				webapp: function () {
					return $webapp;
				},
				module: function () {
					return 'MetersData';
				},
				language: function () {
					return $scope.$parent.language;
				},
				editStatus: function () {
					return false;
				}
			}
		});
	});
}

function MetersData_EditView_Component($scope, $modalInstance, record, api, webapp, module, $timeout, $translatePartialLoader, language, $filter, $http, editStatus) {

    $scope.data = {};
    $scope.datemodel = {};
    $scope.timemodel = {};
    $scope.editRecord = angular.copy(record);
    $scope.serviceContractFieldPresent = false;
    $scope.structure = null;



    if ($translatePartialLoader !== undefined) {
        $translatePartialLoader.addPart('home');
        $translatePartialLoader.addPart('MetersData');
    }


    $scope.save = function (validity) {

        if (!validity) {
            $scope.submit = true;
            return false;
        }

        webapp.busy();


        var params = {
            record: $scope.data
        };


        $modalInstance.close($scope.data);


        api.post(module + '/SaveRecord', params)
            .success(function (savedRecord) {
                webapp.busy(false);
                $modalInstance.dismiss('cancel');

                if (savedRecord.record === true) {
                    alert('Показания успешно отправлены');
                }
                else {
                    alert('Не удалось отправить показания. Обратитесь к администратору системы');
                }
            });
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    }
}


