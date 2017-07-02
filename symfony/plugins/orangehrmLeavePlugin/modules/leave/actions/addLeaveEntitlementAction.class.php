<?php

/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 *
 */

/**
 * Description of addLeaveEntitlementAction
 */
class addLeaveEntitlementAction extends sfAction {
    
    protected $leaveEntitlementService;
    
    protected $employeeService;
    
    public function getEmployeeService() { 
        if (empty($this->employeeService)) {
            $this->employeeService = new EmployeeService();
        }
        return $this->employeeService;
    }

    public function setEmployeeService($employeeService) {
        $this->employeeService = $employeeService;
    }
    
    public function getLeaveEntitlementService() {
        if (empty($this->leaveEntitlementService)) {
            $this->leaveEntitlementService = new LeaveEntitlementService();
        }
        return $this->leaveEntitlementService;
    }

    public function setLeaveEntitlementService($leaveEntitlementService) {
        $this->leaveEntitlementService = $leaveEntitlementService;
    }    
    
    /**
     * Get employees matching the filter parameters.
     * @param type $parameters
     * @return type
     */
    protected function getMatchingEmployees($parameters) {

        $parameterHolder = new EmployeeSearchParameterHolder();
        $filters = array('location' => $parameters['location'],
            'sub_unit' => $parameters['subunit']);

        $parameterHolder->setFilters($filters);
        $parameterHolder->setLimit(NULL);
        $parameterHolder->setReturnType(EmployeeSearchParameterHolder::RETURN_TYPE_ARRAY);
        $employees = $this->getEmployeeService()->searchEmployees($parameterHolder);

        $ids = array();
        foreach($employees as $employee) {
            $ids[] = $employee['empNumber'];
        }
        
        return $ids;
    }
    
    public function execute($request) {
        
        $this->addMode = true;
        $this->form = $this->getForm();
        
        if ($request->hasParameter('id')) {                
            $id = $request->getParameter('id');
            $filters = $this->getFiltersFromEntitlement($id);  
            $this->addMode = false;
            $this->form->setEditMode();
        }       
        
        if ($request->isMethod('post')) {

            $this->form->bind($request->getParameter($this->form->getName()));

            if ($this->form->isValid()) {
                $leaveEntitlement = $this->getLeaveEntitlement($this->form->getValues());
                
                
                $bulkFilter = $this->form->getValue('filters');
                if (!isset($bulkFilter['bulk_assign'])) {
                    
                    $leaveEntitlement = $this->getLeaveEntitlementService()->saveLeaveEntitlement($leaveEntitlement);
                    
                    $eventType = $this->addMode ? LeaveEvents::ENTITLEMENT_ADD : LeaveEvents::ENTITLEMENT_UPDATE;
                    $this->dispatcher->notify(new sfEvent($this, $eventType, 
                            array('entitlement' => $leaveEntitlement)));                    
                    
                    $successMessage = $this->addMode ?  TopLevelMessages::ADD_SUCCESS : TopLevelMessages::UPDATE_SUCCESS;
                    $this->getUser()->setFlash('success', __($successMessage));

                    // Before redirecting, update saved search parameters, so that the 
                    // entitlement added now will be visible in the list
                    $filters = $this->getFiltersFromEntitlement($leaveEntitlement->getId());
                    $this->saveFilters($filters);                    
                } else {
                    $employeeNumbers = $this->getMatchingEmployees($this->form->getValue('filters'));
                  
                    $savedCount = $this->getLeaveEntitlementService()->bulkAssignLeaveEntitlements($employeeNumbers, $leaveEntitlement);
                    
                    $this->dispatcher->notify(new sfEvent($this, LeaveEvents::ENTITLEMENT_BULK_ADD, 
                            array('entitlement' => $leaveEntitlement,
                                  'employeeNumbers' => $employeeNumbers)));
                    
                    $this->getUser()->setFlash('success', __('Entitlements added to %count% employees(s)', array('%count%' => $savedCount)));
                }
                
                $this->redirect('leave/viewLeaveEntitlements?savedsearch=1');
            }
        } else {
            if ($request->hasParameter('id')) {                
                $id = $request->getParameter('id');
                $filters = $this->getFiltersFromEntitlement($id);  
            } else if ($request->hasParameter('savedsearch')){
                $filters = $this->getFilters();
            }
            if (!empty($filters)) {
                $this->setFormDefaults($filters);
            }            
        }        
    }
    
    protected function getFiltersFromEntitlement($id) {
        $entitlement = $this->getLeaveEntitlementService()->getLeaveEntitlement($id);    
        
        $employee = $entitlement->getEmployee();        
        
        $filters = array(
            'id' => $id,
            'employee' => array(
                'empName' => $employee->getFullName(),
                'empId' => $employee->getEmpNumber()),
            'leave_type' => $entitlement->getLeaveTypeId(),
            'date'=>array(
                    'from'=> $entitlement->getFromDate(),
                    'to'=> $entitlement->getToDate()
                    ),
            'entitlement' => $entitlement->getNoOfDays()
        );
        
        return $filters;
    }
    
    protected function getForm() {
        return new LeaveEntitlementAddForm();
    }
    
    protected function setFormDefaults($filters) {
        
        // convert back to localized format before setting in form
        $filters['date']['from'] = set_datepicker_date_format($filters['date']['from']);
        $filters['date']['to'] = set_datepicker_date_format($filters['date']['to']);  
        $filters['entitlement'] = number_format($filters['entitlement'], 2);
               
        $this->form->setDefaults($filters);
    }
    
    /**
     * Get search filters from user attribute
     * @param array $filters
     * @return array
     */
    protected function getFilters() {
        return $this->getUser()->getAttribute(viewLeaveEntitlementsAction::FILTERS_ATTRIBUTE_NAME, array(), 'leave');
    }  
    
    /**
     * Save search filters as user attribute
     * @param array $filters
     */
    protected function saveFilters(array $filters) {
        unset($filters['id']);        
        $this->getUser()->setAttribute(viewLeaveEntitlementsAction::FILTERS_ATTRIBUTE_NAME, $filters, 'leave');
    }  
    
    protected function getLeaveEntitlement($values) {
        
       if(isset($values['filters']['bulk_assign'])){
           $leaveEntitlement = new LeaveEntitlement(); 
           $leaveEntitlement->setNoOfDays($values['entitlement']);
        }else{
            if (isset($values['id'])) {
                $id = $values['id'];
                $leaveEntitlement = $this->getLeaveEntitlementService()->getLeaveEntitlement($id);
                $leaveEntitlement->setNoOfDays($values['entitlement']);
            } else {
                if(LeavePeriodService::getLeavePeriodStatus()== LeavePeriodService::LEAVE_PERIOD_STATUS_FORCED){
                    $empNumber = $values['employee']['empId'];
                    $fromDate = $values['date']['from'];
                    $toDate = $values['date']['to'];
                    $leaveTypeId = $values['leave_type'];

                    $entitlementList = $this->getLeaveEntitlementService()->getMatchingEntitlements($empNumber, $leaveTypeId, $fromDate, $toDate);                               

                    if(count($entitlementList) > 0){
                        $leaveEntitlement = $entitlementList->getFirst();

                        $newValue = $leaveEntitlement->getNoOfDays()+$values['entitlement'];
                        $leaveEntitlement->setNoOfDays($newValue);
                    }else{
                        $leaveEntitlement = new LeaveEntitlement(); 
                        $leaveEntitlement->setNoOfDays($values['entitlement']);
                    }
                }else{
                    $leaveEntitlement = new LeaveEntitlement(); 
                    $leaveEntitlement->setNoOfDays($values['entitlement']);
                }
        }

        }
            
            if(isset($values['employee']['empId'])){
                $leaveEntitlement->setEmpNumber($values['employee']['empId']);
            }
            if(isset($values['leave_type'])){
                $leaveEntitlement->setLeaveTypeId($values['leave_type']);
            }
            
        
            $user = $this->getUser();
            $userId = $user->getAttribute('auth.userId');
            $createdBy = $this->getUser()->getAttribute('auth.firstName');
            
            $leaveEntitlement->setCreditedDate(date('Y-m-d'));
            $leaveEntitlement->setCreatedById($userId);
            $leaveEntitlement->setCreatedByName($createdBy);        
            
            $leaveEntitlement->setEntitlementType(LeaveEntitlement::ENTITLEMENT_TYPE_ADD);
            $leaveEntitlement->setDeleted(0);            
        
            $leaveEntitlement->setNoOfDays(round($leaveEntitlement->getNoOfDays(), 2));
        
            $leaveEntitlement->setFromDate($values['date']['from']);
            $leaveEntitlement->setToDate($values['date']['to']);

        return $leaveEntitlement;
    }
}
