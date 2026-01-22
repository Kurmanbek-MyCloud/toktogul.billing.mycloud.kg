<?php

class Offices_Detail_View extends Vtiger_Detail_View {


	public function showModuleDetailView(Vtiger_Request $request) {

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
		
		return parent::showModuleDetailView($request);
	}


}