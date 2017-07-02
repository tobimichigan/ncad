<?php

/*
 ** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 *
 */

/**
 * define a holiday
 */
class defineHolidayAction extends sfAction {

    protected $holidayService;
    protected $leavePeriodService;

    /**
     * Returns Leave Period
     * @return LeavePeriodService
     */
    public function getLeavePeriodService() {

        if (is_null($this->leavePeriodService)) {
            $leavePeriodService = new LeavePeriodService();
            $leavePeriodService->setLeavePeriodDao(new LeavePeriodDao());
            $this->leavePeriodService = $leavePeriodService;
        }

        return $this->leavePeriodService;
    }

    /**
     * Returns Leave Period
     * @return LeavePeriodService
     */
    public function setLeavePeriodService($leavePeriodService) {
        $this->leavePeriodService = $leavePeriodService;
    }

    /**
     * get Method for Holiday Service
     *
     * @return HolidayService $holidayService
     */
    public function getHolidayService() {
        if (is_null($this->holidayService)) {
            $this->holidayService = new HolidayService();
        }
        return $this->holidayService;
    }

    /**
     * Set HolidayService
     * @param HolidayService $holidayService
     */
    public function setHolidayService(HolidayService $holidayService) {
        $this->holidayService = $holidayService;
    }

    /**
     * Add Holiday
     * @param sfWebRequest $request
     */
    public function execute($request) {

        //Keep Menu in Leave/Config 
        $request->setParameter('initialActionName', 'viewHolidayList'); 
         
        $this->form = new HolidayForm();
        $editId = $request->getParameter('hdnEditId');

        $this->editMode = false; // Pass edit mode for teh view
        $this->form->editMode = false; // Pass edit mode for form

        if ($editId && $editId != '') {
            $this->form->setDefaultValues($editId);
        }

        if (($editId && $editId != '') || $request->getParameter('hdnEditMode') == 'yes') {
            $this->editMode = true;
            $this->form->editMode = true;
        }

        if ($request->isMethod('post')) {

            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $post = $this->form->getValues();
                /* Save holiday */

                if ($post['id'] != '') {
                    $this->getUser()->setFlash('success', __(TopLevelMessages::UPDATE_SUCCESS));
                    
                } else {
                    $this->getUser()->setFlash('success', __(TopLevelMessages::SAVE_SUCCESS));
                    
                }

                $date = $post['date'];
                $holidayId = $post['id'];

                /* Read the holiday by date */
                $holidayObjectDate = $this->getHolidayService()->readHolidayByDate($date);

                $allowToAdd = true;

                if ($this->editMode) {
                    $holidayObject = $this->getHolidayService()->readHoliday($holidayId);

                    if ($date != $holidayObjectDate->getDate() && $holidayObjectDate->getRecurring()) {
                        $allowToAdd = false;
                    }
                } else {
                    /* Days already added can not be selected to add */
                    if ($date == $holidayObjectDate->getDate() || $holidayObjectDate->getRecurring() == 1) {
                        $allowToAdd = false;
                    }
                }

                /* Error will not return if the date if not in the correct format */
                if (!$allowToAdd && !is_null($date)) {
                    $this->templateMessage = array('WARNING', __('Failed to Save: Date Already Assigned'));
                } else {

                    

                    $holidayObject = $this->getHolidayService()->readHoliday($post['id']);
                    $holidayObject->setDescription($post['description']);
                    $holidayObject->setDate($post['date']);

                    $recurringValue = $post['recurring'] == 'on' ? 1 : 0;
                    $holidayObject->setRecurring($recurringValue);

                    $holidayObject->setLength($post['length']);
                    $this->getHolidayService()->saveHoliday($holidayObject);
                    $this->redirect('leave/viewHolidayList');
                }
            }
        }
    }

    /**
     * Get form object 
     * @return
     */
    public function getForm() {
        if (!($this->form instanceof HolidayForm)) {
            $this->form = new HolidayForm();
        }
        return $this->form;
    }

}
