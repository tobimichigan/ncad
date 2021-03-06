<?php

/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */

/**
 * Form object for holiday list search
 *
 */
class HolidayListSearchForm extends sfForm {
    
    private $leavePeriodService;
  
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
     * Configuring WorkWeek form widget
     */
    public function configure() {

        sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N', 'OrangeDate'));
        
        
        $this->setWidget('calFromDate',new ohrmWidgetDatePicker(array(), array('id' => 'calFromDate')));
        $this->setWidget('calToDate',new ohrmWidgetDatePicker(array(), array('id' => 'calToDate')));
        
        //$this->widgetSchema->setLabels(array('date' => __("Date Range")));
        
        $this->widgetSchema->setLabels(array('calFromDate' => __('From'),'calToDate' => __('To')));
       
        $this->setvalidators(array(
            'calFromDate' => new ohrmDateValidator(array('required' => true)),
            'calToDate' => new ohrmDateValidator(array('required' => true))
        ));
        
        $this->widgetSchema->setNameFormat('holidayList[%s]'); 
        
        $this->setDefaultDates();
    }    
    
    
    
    public function getJavaScripts() {
        $javaScripts = parent::getJavaScripts();
        $javaScripts[] = plugin_web_path('orangehrmLeavePlugin', 'js/viewHolidayListSuccessSearch.js');
        
        return $javaScripts;
    }    
    
    public function getStylesheets() {
        parent::getStylesheets();
        
        $styleSheets = parent::getStylesheets();
      //  $styleSheets['/orangehrmLeavePlugin/css/viewHolidayListSuccessSearch.css'] = 'screen';
        
        return $styleSheets;        
    }    
    
    protected function setDefaultDates() {
        $now = time();
        
        // If leave period defined, use leave period start and end date
        $calenderYear = $this->getLeavePeriodService()->getCalenderYearByDate($now);        
                
 
        
        $this->setDefaults(array('calFromDate' => set_datepicker_date_format($calenderYear[0]),
            'calToDate' => set_datepicker_date_format($calenderYear[1])));
    }
}

