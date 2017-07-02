<?php
/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 LASUHRM Inc., http://www.lasu.edu.ng
 *
 * LASUHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. LASUHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in LASUHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that LASUHRM Inc creates for
 * Customer shall remain vested in LASUHRM Inc. Any rights not expressly granted herein are
 * reserved to LASUHRM Inc.
 *
 * You should have received a copy of the OrangeHRM Enterprise  proprietary license file along
 * with this program; if not, write to the LASUHRM Inc. 538 Teal Plaza, Secaucus , NJ 0709
 * to get the file.
 *
 */
class getActivityListJasonAction extends sfAction {
	
	private $projectService;

	public function getProjectService() {
		if (is_null($this->projectService)) {
			$this->projectService = new ProjectService();
			$this->projectService->setProjectDao(new ProjectDao());
		}
		return $this->projectService;
	}
	
	public function execute($request) {

		$this->setLayout(false);
		sfConfig::set('sf_web_debug', false);
		sfConfig::set('sf_debug', false);

		if ($this->getRequest()->isXmlHttpRequest()) {
			$this->getResponse()->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
		}

		$projectId = $request->getParameter('projectId');

		$activityList = $this->getProjectService()->getActivityListByProjectId($projectId);

		return $this->renderText(json_encode($activityList->toArray()));
	
	}
}

?>
