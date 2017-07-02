<?php

/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */
class punchInAction extends sfAction {

    private $attendanceService;

    public function getAttendanceService() {

        if (is_null($this->attendanceService)) {

            $this->attendanceService = new AttendanceService();
        }

        return $this->attendanceService;
    }

    public function setAttendanceService(AttendanceService $attendanceService) {

        $this->attendanceService = $attendanceService;
    }

    public function execute($request) {
        
        $this->_checkAuthentication();

        $this->editmode = null;
        $this->userObj = $this->getContext()->getUser()->getAttribute('user');
        $this->employeeId = $this->userObj->getEmployeeNumber();
        $actions = array(PluginWorkflowStateMachine::ATTENDANCE_ACTION_PUNCH_OUT);
        $actionableStatesList = $this->userObj->getActionableAttendanceStates($actions);
        $timeZoneOffset = $this->userObj->getUserTimeZoneOffset();
        $timeStampDiff = $timeZoneOffset * 3600 - date('Z');
        $this->currentDate = date('Y-m-d', time() + $timeStampDiff);
        $this->currentTime = date('H:i', time() + $timeStampDiff);
        $this->timezone = $timeZoneOffset * 3600;
        $localizationService = new LocalizationService();
        $inputDatePattern = sfContext::getInstance()->getUser()->getDateFormat();


        if ($this->getUser()->hasFlash('templateMessage')) {
            list($messageType, $message) = $this->getUser()->getFlash('templateMessage');
            $this->getUser()->setFlash($messageType, $message);
        }
        $attendanceRecord = $this->getAttendanceService()->getLastPunchRecord($this->employeeId, $actionableStatesList);


        if (is_null($attendanceRecord)) {
           
            $this->allowedActions = $this->userObj->getAllowedActions(WorkflowStateMachine::FLOW_ATTENDANCE, AttendanceRecord::STATE_INITIAL);
        } else {

            $this->redirect("attendance/punchOut");
        }


        $this->punchInTime = null;
        $this->punchInUtcTime = null;
        $this->punchInNote = null;
        $this->actionPunchOut = null;

        $this->form = new AttendanceForm();
        $this->actionPunchIn = $this->getActionName();
        $this->attendanceFormToImplementCsrfToken = new AttendanceFormToImplementCsrfToken();


        if ($request->isMethod('post')) {

            $accessFlowStateMachineService = new AccessFlowStateMachineService();
            $attendanceRecord = new AttendanceRecord();
            $attendanceRecord->setEmployeeId($this->employeeId);


            if (!(in_array(PluginWorkflowStateMachine::ATTENDANCE_ACTION_EDIT_PUNCH_TIME, $this->allowedActions)) ) {
                $this->attendanceFormToImplementCsrfToken->bind($request->getParameter('attendance'));
           
                if ($this->attendanceFormToImplementCsrfToken->isValid()) {

  
                    $punchInDate = $localizationService->convertPHPFormatDateToISOFormatDate($inputDatePattern, $this->request->getParameter('date'));
                    $punchIntime = $this->request->getParameter('time');
                    $punchInNote = $this->request->getParameter('note');
                    $timeZoneOffset = $this->request->getParameter('timeZone');

                    $nextState = $this->userObj->getNextState(WorkflowStateMachine::FLOW_ATTENDANCE, AttendanceRecord::STATE_INITIAL, WorkflowStateMachine::ATTENDANCE_ACTION_PUNCH_IN);

                    $punchIndateTime = strtotime($punchInDate . " " . $punchIntime);

                    $attendanceRecord = $this->setAttendanceRecord($attendanceRecord, $nextState, date('Y-m-d H:i', $punchIndateTime - $timeZoneOffset), date('Y-m-d H:i', $punchIndateTime), $timeZoneOffset / 3600, $punchInNote);

                    $this->redirect("attendance/punchOut");

                }
            } else {

                $this->form->bind($request->getParameter('attendance'));

                if ($this->form->isValid()) {

                    $punchInDate = $this->form->getValue('date');
                    $punchIntime = $this->form->getValue('time');
                    $punchInNote = $this->form->getValue('note');
                    $timeZoneOffset = $this->request->getParameter('timeZone');

                    $punchInEditModeTime = mktime(date('H', strtotime($punchIntime)), date('i', strtotime($punchIntime)), 0, date('m', strtotime($punchInDate)), date('d', strtotime($punchInDate)), date('Y', strtotime($punchInDate)));

                    $nextState = $this->userObj->getNextState(WorkflowStateMachine::FLOW_ATTENDANCE, AttendanceRecord::STATE_INITIAL, WorkflowStateMachine::ATTENDANCE_ACTION_PUNCH_IN);
                    $attendanceRecord = $this->setAttendanceRecord($attendanceRecord, $nextState, date('Y-m-d H:i', $punchInEditModeTime - $timeZoneOffset), date('Y-m-d H:i', $punchInEditModeTime), $timeZoneOffset / 3600, $punchInNote);
                    $this->redirect("attendance/punchOut");

                }
            }
        }

        $this->setTemplate("punchTime");
    }

    public function setAttendanceRecord($attendanceRecord, $state, $punchInUtcTime, $punchInUserTime, $punchInTimezoneOffset, $punchInNote) {

        $attendanceRecord->setState($state);
        $attendanceRecord->setPunchInUtcTime($punchInUtcTime);
        $attendanceRecord->setPunchInUserTime($punchInUserTime);
        $attendanceRecord->setPunchInNote($punchInNote);
        $attendanceRecord->setPunchInTimeOffset($punchInTimezoneOffset);
        return $this->getAttendanceService()->savePunchRecord($attendanceRecord);
    }
    
    protected function _checkAuthentication($empNumber) {
        
        $user = $this->getUser()->getAttribute('user');        
        $logedInEmpNumber   = $user->getEmployeeNumber();

        if (empty($logedInEmpNumber)) {
            $this->redirect('auth/login');
        }
        
    }

}