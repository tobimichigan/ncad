<?php
/** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */

/**
 * Description of TimesheetPeriodServiceTest
 *
 * @group Time
 */
class TimesheetPeriodServiceTest extends PHPUnit_Framework_Testcase {

	private $timesheetPeriodService;

	protected function setUp() {
	
		$this->timesheetPeriodService = new TimesheetPeriodService();
                $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
		$query1 = "DELETE FROM hs_hr_config WHERE `key` = 'timesheet_period_and_start_date' OR " .	
                          "`key` = 'timesheet_period_set'";
		$query2 = "INSERT INTO `hs_hr_config`(`key`, `value`) VALUES('timesheet_period_set', 'Yes')";
		$query3 = "INSERT INTO `hs_hr_config`(`key`, `value`) VALUES('timesheet_period_and_start_date', '<TimesheetPeriod><PeriodType>Weekly</PeriodType><ClassName>WeeklyTimesheetPeriod</ClassName><StartDate>1</StartDate><Heading>Week</Heading></TimesheetPeriod>')";
		$pdo->exec($query1);
		$pdo->exec($query2);
		$pdo->exec($query3);
	}

	public function testGetTimesheetPeriodDao() {

		$this->assertTrue($this->timesheetPeriodService->getTimesheetPeriodDao() instanceof TimesheetPeriodDao);
	}

	public function testSetTimesheetPeriodDao() {

		$timesheetPeriodDao = new TimesheetPeriodDao();
		$this->timesheetPeriodService->setTimesheetPeriodDao($timesheetPeriodDao);

		$this->assertTrue($this->timesheetPeriodService->getTimesheetPeriodDao() instanceof TimesheetPeriodDao);
	}

	public function testGetDefinedTimesheetPeriod() {

                // This is necessary to make timeStampDiff 0 in MonthlyTimesheetPeriod::getDatesOfTheTimesheetPeriod
                // $timeStampDiff = $clientTimeZoneOffset * 3600 - $serverTimezoneOffset;
                $userObj = new User();
                $serverTimezoneOffset = ((int) date('Z'));
                $userObj->setUserTimeZoneOffset($serverTimezoneOffset / 3600);
                sfContext::getInstance()->getUser()->setAttribute('user', $userObj); 
        
		$currentDate = '2011-06-30';
		$key = 'timesheet_period_and_start_date';
		
                $xmlString = TestDataService::getRecords("SELECT value from hs_hr_config WHERE `key` = '" . $key . "'");
                $value = $xmlString[0]['value']; 
        
		$timesheetPeriodDaoMock = $this->getMock('TimesheetPeriodDao', array('getDefinedTimesheetPeriod'));
		$timesheetPeriodDaoMock->expects($this->once())
			->method('getDefinedTimesheetPeriod')
			->will($this->returnValue($value));

		$this->timesheetPeriodService->setTimesheetPeriodDao($timesheetPeriodDaoMock);
		$array = $this->timesheetPeriodService->getDefinedTimesheetPeriod($currentDate);
    
		$this->assertEquals($array[0],'2011-06-27 00:00');
		$this->assertEquals($array[4],'2011-07-01 00:00');


	}

	public function testIsTimesheetPeriodDefined() {

		$key = 'timesheet_period_set';
               
                $xmlString = TestDataService::getRecords("SELECT value from hs_hr_config WHERE `key` = '" . $key . "'");
                $boolean = $xmlString[0]['value'];
                
		$timesheetPeriodDaoMock = $this->getMock('TimesheetPeriodDao', array('isTimesheetPeriodDefined'));
		$timesheetPeriodDaoMock->expects($this->once())
			->method('isTimesheetPeriodDefined')
			->will($this->returnValue($boolean));

		$this->timesheetPeriodService->setTimesheetPeriodDao($timesheetPeriodDaoMock);
		$isDefined = $this->timesheetPeriodService->isTimesheetPeriodDefined();
		$this->assertEquals($boolean,$isDefined);

	}

	public function testSetTimesheetPeriod(){

		$startDay='1';
		$xml = '<TimesheetPeriod><PeriodType>Weekly</PeriodType><ClassName>WeeklyTimesheetPeriod</ClassName><StartDate>1</StartDate><Heading>Week</Heading></TimesheetPeriod>';
		$timesheetPeriodDaoMock = $this->getMock('TimesheetPeriodDao', array('setTimesheetPeriod','setTimesheetPeriodAndStartDate'));
		$timesheetPeriodDaoMock->expects($this->once())
			->method('setTimesheetPeriod')
			->will($this->returnValue(true));

		$timesheetPeriodDaoMock->expects($this->once())
			->method('setTimesheetPeriodAndStartDate')
			->with($xml)
			->will($this->returnValue(true));

		$this->timesheetPeriodService->setTimesheetPeriodDao($timesheetPeriodDaoMock);
		$true = $this->timesheetPeriodService->setTimesheetPeriod($startDay);
		$this->assertTrue($true);
		

	}
    
    public function testGetTimesheetHeading(){
        
        
        		
		$key = 'timesheet_period_and_start_date';
		
                // Note: fetchObject fails due to primary key 'key' being a reserved word.
                //$xmlString = TestDataService::fetchObject('Config', $key);
                $xmlString = TestDataService::getRecords("SELECT value from hs_hr_config WHERE `key` = '" . $key . "'");
                $value = $xmlString[0]['value'];                 
                
		$timesheetPeriodDaoMock = $this->getMock('TimesheetPeriodDao', array('getDefinedTimesheetPeriod'));
		$timesheetPeriodDaoMock->expects($this->once())
			->method('getDefinedTimesheetPeriod')
			->will($this->returnValue($value));

		$this->timesheetPeriodService->setTimesheetPeriodDao($timesheetPeriodDaoMock);
		$timesheetHeading = $this->timesheetPeriodService->getTimesheetHeading();
        
        $this->assertEquals("Week",(string)$timesheetHeading );
        
    }



   


}

?>
