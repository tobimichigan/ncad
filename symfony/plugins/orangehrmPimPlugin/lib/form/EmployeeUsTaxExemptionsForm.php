<?php

/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */
class EmployeeUsTaxExemptionsForm extends sfForm {

    private $countryService;
    private $employeeService;

    public function configure() {
        $this->taxExemptionPermission = $this->getOption('taxExemptionPermission');
        $empNumber = $this->getOption('empNumber');
        $employee = $this->getEmployeeService()->getEmployee($empNumber);
        $this->fullName = $employee->getFullName();
        $widgets = array('empNumber' => new sfWidgetFormInputHidden(array(), array('value' => $empNumber)));
        $validators = array('empNumber' => new sfValidatorString(array('required' => true)));
        if ($this->taxExemptionPermission->canRead()) {
            $taxExemptionWidgets = $this->getTaxExemptionsWidgets();
            $taxExemptionValidators = $this->getTaxExemptionsValidators();
            if (!$this->taxExemptionPermission->canUpdate()) {
                foreach ($taxExemptionWidgets as $widgetName => $widget) {
                    $widget->setAttribute('disabled', 'disabled');
                }
            }
            $widgets = array_merge($widgets, $taxExemptionWidgets);
            $validators = array_merge($validators, $taxExemptionValidators);
        }
        $this->setWidgets($widgets);
        $this->setValidators($validators);
        $this->widgetSchema->setNameFormat('tax[%s]');
    }
    
    /**
     * Create widgets and set default values
     * 
     * @return \sfWidgetFormSelect 
     */
    private function getTaxExemptionsWidgets() {
        $status = array(0 => "-- " . __('Select') . " --", 'S' => __('Single'), 'M' => __('Married'), 'NRA' => __('Non Resident Alien'), 'NA' => __('Not Applicable'));
        $states = $this->getStatesList();
        $empNumber = $this->getOption('empNumber');
        $empTaxExemption = $this->getEmployeeService()->getEmployeeTaxExemptions($empNumber);
        $widgets = array();
        $widgets['empNumber'] = new sfWidgetFormInputHidden();
        $widgets['federalStatus'] = new sfWidgetFormSelect(array('choices' => $status));
        $widgets['federalExemptions'] = new sfWidgetFormInputText();
        $widgets['state'] = new sfWidgetFormSelect(array('choices' => $states));
        $widgets['stateStatus'] = new sfWidgetFormSelect(array('choices' => $status));
        $widgets['stateExemptions'] = new sfWidgetFormInputText();
        $widgets['unempState'] = new sfWidgetFormSelect(array('choices' => $states));
        $widgets['workState'] = new sfWidgetFormSelect(array('choices' => $states));
                
        $this->setDefault('empNumber', $empNumber);
        if($empTaxExemption != null){
            $widgets['federalStatus']->setDefault($empTaxExemption->getFederalStatus());
            $widgets['federalExemptions']->setDefault($empTaxExemption->getFederalExemptions());
            $widgets['state']->setDefault($empTaxExemption->getState());
            $widgets['stateStatus']->setDefault($empTaxExemption->getStateStatus());
            $widgets['stateExemptions']->setDefault($empTaxExemption->getStateExemptions());
            $widgets['unempState']->setDefault($empTaxExemption->getUnemploymentState());
            $widgets['workState']->setDefault($empTaxExemption->getWorkState());
        }
        return $widgets;
    }
    
    /**
     * Validate form fields
     * 
     * @return validators
     */
    private function getTaxExemptionsValidators() {
        $status = array(0 => "-- " . __('Select') . " --", 'S' => __('Single'), 'M' => __('Married'), 'NRA' => __('Non Resident Alien'), 'NA' => __('Not Applicable'));
        $states = $this->getStatesList();
        $validators = array(
            'empNumber' => new sfValidatorString(array('required' => true)),
            'federalStatus' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($status))),
            'federalExemptions' => new sfValidatorInteger(array('required' => false, 'max' => 99)),
            'state' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($states))),
            'stateStatus' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($status))),
            'stateExemptions' => new sfValidatorInteger(array('required' => false, 'max' => 99)),
            'unempState' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($states))),
            'workState' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($states))),
        );
        return $validators;
    }

    /**
     * Get EmpUsTaxExemption object
     */
    public function getEmpUsTaxExemption() {
        
        $empNumber = $this->getValue('empNumber');
        $empUsTaxExemption = $this->getEmployeeService()->getEmployeeTaxExemptions($empNumber);

            if($empUsTaxExemption == null){
                $empUsTaxExemption = new EmpUsTaxExemption();
                $empUsTaxExemption->empNumber = $this->getValue('empNumber');
            }
            
        $empUsTaxExemption->federalStatus = $this->getValue('federalStatus');
        $empUsTaxExemption->federalExemptions = $this->getValue('federalExemptions');
        $empUsTaxExemption->state = $this->getValue('state');
        $empUsTaxExemption->stateStatus= $this->getValue('stateStatus');
        $empUsTaxExemption->stateExemptions = $this->getValue('stateExemptions');
        $empUsTaxExemption->unemploymentState = $this->getValue('unempState');
        $empUsTaxExemption->workState = $this->getValue('workState');

        return $empUsTaxExemption;
    }

    /**
     * Get EmployeeService
     * @returns EmployeeService
     */
    public function getEmployeeService() {
        if (is_null($this->employeeService)) {
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

    /**
     * Returns Country Service
     * @returns CountryService
     */
    public function getCountryService() {
        if (is_null($this->countryService)) {
            $this->countryService = new CountryService();
        }
        return $this->countryService;
    }

    /**
     * Returns States List
     * @return array
     */
    private function getStatesList() {
        $list = array("" => "-- " . __('Select') . " --");
        $states = $this->getCountryService()->getProvinceList();
        foreach ($states as $state) {
            $list[$state->province_code] = $state->province_name;
        }
        return $list;
    }

}
