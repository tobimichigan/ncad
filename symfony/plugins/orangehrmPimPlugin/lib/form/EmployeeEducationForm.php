<?php
/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */
class EmployeeEducationForm extends sfForm {
    
    private $employeeService;
    public $fullName;
    private $widgets = array();
    public $empEducationList;

    /**
     * Get EmployeeService
     * @returns EmployeeService
     */
    public function getEmployeeService() {
        if(is_null($this->employeeService)) {
            $this->employeeService = new EmployeeService();
            $this->employeeService->setEmployeeDao(new EmployeeDao());
        }
        return $this->employeeService;
    }

    /**
     * Set EmployeeService
     * @param EmployeeService $employeeService
     */
    public function setEmployeeService(EmployeeService $employeeService) {
        $this->employeeService = $employeeService;
    }

    public function configure() {
        $this->educationPermissions = $this->getOption('educationPermissions');
        
        $empNumber = $this->getOption('empNumber');
        $employee = $this->getEmployeeService()->getEmployee($empNumber);
        $this->fullName = $employee->getFullName();

        $this->empEducationList = $this->getEmployeeService()->getEmployeeEducations($empNumber);
        
        $widgets = array('emp_number' => new sfWidgetFormInputHidden(array(), array('value' => $empNumber)));
        $validators = array('emp_number' => new sfValidatorString(array('required' => true)));
        
        if ($this->educationPermissions->canRead()) {

            $educationWidgets = $this->getEducationWidgets();
            $educationValidators = $this->getEducationValidators();

            if (!($this->educationPermissions->canUpdate() || $this->educationPermissions->canCreate()) ) {
                foreach ($educationWidgets as $widgetName => $widget) {
                    $widget->setAttribute('disabled', 'disabled');
                }
            }
            $widgets = array_merge($widgets, $educationWidgets);
            $validators = array_merge($validators, $educationValidators);
        }
        $this->setWidgets($widgets);
        $this->setValidators($validators);
        
        $this->widgetSchema->setNameFormat('education[%s]');
    }
    
    /**
     * Get widgets
     * @return \sfWidgetFormInputText 
     */
    private function getEducationWidgets() {
        $widgets = array(
            'id' => new sfWidgetFormInputHidden(),
            'code' => new sfWidgetFormSelect(array('choices' => $this->_getEducationList())),
            'institute' => new sfWidgetFormInputText(),
            'major' => new sfWidgetFormInputText(),
            'year' => new sfWidgetFormInputText(),
            'gpa' => new sfWidgetFormInputText(),
            'start_date' => new ohrmWidgetDatePicker(array(), array('id' => 'education_start_date')),
            'end_date' => new ohrmWidgetDatePicker(array(), array('id' => 'education_end_date'))
        );
        return $widgets;
    }
    
    /**
     * Get validation for widgets
     * @return \ohrmDateValidator 
     */
    private function getEducationValidators() {
        $inputDatePattern = sfContext::getInstance()->getUser()->getDateFormat();
    
        $validators = array(
            'id' => new sfValidatorString(array('required' => false)),
            'code' => new sfValidatorString(array('required' => true, 'max_length' => 13)),
            'institute' => new sfValidatorString(array('required' => false, 'max_length' => 100)),
            'major' => new sfValidatorString(array('required' => false, 'max_length' => 100)),
            'year' => new sfValidatorNumber(array('required' => false, 'max'=>9999, 'min'=>0)),
            'gpa' => new sfValidatorString(array('required' => false, 'max_length' => 25)),
            'start_date' => new ohrmDateValidator(array('date_format'=>$inputDatePattern, 'required' => false), array('invalid'=>'Date format should be'. $inputDatePattern)),
            'end_date' => new ohrmDateValidator(array('date_format'=>$inputDatePattern, 'required' => false), array('invalid'=>'Date format should be'. $inputDatePattern)),
        );
        
        return $validators;
    }


    private function _getEducationList() {
        $educationService = new EducationService();
        $educationList = $educationService->getEducationList();
        $list = array("" => "-- " . __('Select') . " --");

        foreach($educationList as $education) {
            $list[$education->getId()] = $education->getName();
        }
        
        return $list;
    }
}
?>