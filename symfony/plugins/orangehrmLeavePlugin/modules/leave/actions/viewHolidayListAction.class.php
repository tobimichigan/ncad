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
 * view list of holidays
 */
class viewHolidayListAction extends sfAction {

    private $holidayService;
    private $leavePeriodService;
    private $workWeekEntity;
    
    /**
     * get Method for WorkWeekEntity
     *
     * @return WorkWeek $workWeekEntity
     */
    public function getWorkWeekEntity() {
        $this->workWeekEntity = new WorkWeek();
        return $this->workWeekEntity;
    }    
           

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
     * view Holiday list
     * @param sfWebRequest $request
     */ 
    public function execute($request) {
        //Keep Menu in Leave/Config 
        $request->setParameter('initialActionName', 'viewHolidayList'); 
        
        $this->searchForm = $this->getSearchForm();
        
        $dateRange = $this->getLeavePeriodService()->getCalenderYearByDate(time());
        $startDate = $dateRange[0];
        $endDate = $dateRange[1];

        if($request->isMethod('post')) {
            
            $this->searchForm->bind($request->getParameter($this->searchForm->getName()));
            
            if ($this->searchForm->isValid()) {
                $values = $this->searchForm->getValues();
                
                    $startDate = $values['calFromDate'];
                    $endDate = $values['calToDate'];
                
            }
        }

        $this->daysLenthList = WorkWeek::getDaysLengthList();
        $this->yesNoList = WorkWeek::getYesNoList();
        $this->holidayList = $this->getHolidayService()->searchHolidays($startDate, $endDate);

        $this->setListComponent($this->holidayList);

        $message = $this->getUser()->getFlash('templateMessage');        
        $this->messageType = (isset($message[0]))?strtolower($message[0]):"";
        $this->message = (isset($message[1]))?$message[1]:"";
        

        if ($this->getUser()->hasFlash('templateMessage')) {
            $this->templateMessage = $this->getUser()->getFlash('templateMessage');
            $this->getUser()->setFlash('templateMessage', array());
        }
    }
    
    protected function getSearchForm() {
        return new HolidayListSearchForm(array(), array(), true);
    }
    
    protected function setListComponent($holidayList) {
       
        
        $configurationFactory = $this->getListConfigurationFactory();
        
        
        
        ohrmListComponent::setActivePlugin('orangehrmLeavePlugin');
        ohrmListComponent::setConfigurationFactory($configurationFactory);
        ohrmListComponent::setListData($holidayList);
        ohrmListComponent::setPageNumber(0);
        $numRecords = count($holidayList);
        ohrmListComponent::setItemsPerPage($numRecords);
        ohrmListComponent::setNumberOfRecords($numRecords);
    }
    
    protected function getListConfigurationFactory() {
        return new HolidayListConfigurationFactory();
    }    

}
