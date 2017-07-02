<?php

/*
 ** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 *
 */

class HolidayService extends BaseService {

    // Holiday Data Access Object
    private $holidayDao;

    /**
     * Get the Holiday Data Access Object
     * @return HolidayDao
     */
    public function getHolidayDao() {
        if (is_null($this->holidayDao)) {
            $this->holidayDao = new HolidayDao();
        }
        return $this->holidayDao;
    }

    /**
     * Set Holiday Data Access Object
     * @param HolidayDao $HolidayDao
     * @return void
     */
    public function setHolidayDao(HolidayDao $HolidayDao) {
        $this->holidayDao = $HolidayDao;
    }

    /**
     * Add, Update Holidays
     * @param Holiday $holiday
     * @return boolean
     */
    public function saveHoliday(Holiday $holiday) {

        return $this->getHolidayDao()->saveHoliday($holiday);
    }

    /**
     * Delete Holiday
     * @param int $holidayId
     * @return boolean
     */
    public function deleteHoliday($holidayId) {

        return $this->getHolidayDao()->deleteHoliday($holidayId);
    }

    /**
     * Read Holiday by given holidayId
     * @param int $holidayId
     * @return Holiday $Holiday
     */
    public function readHoliday($holidayId) {

        $holiday = $this->getHolidayDao()->readHoliday($holidayId);

        if (!$holiday instanceof Holiday) {
            $holiday = new Holiday();
        }

        return $holiday;
    }

    /**
     * Read Holiday by given Date
     * @param date $date
     * @param OperationalCountry $operationalCountry
     * @return Holiday $holiday
     */
    public function readHolidayByDate($date, OperationalCountry $operationalCountry = null) {

        $holiday = $this->getHolidayDao()->readHolidayByDate($date, $operationalCountry);

        if (!$holiday instanceof Holiday) {
            $holiday = new Holiday();
        }

        return $holiday;
    }

    /**
     * Get Holiday list
     * @param int $year
     * @param OperationalCountry $operationalCountry
     * @param int $offset
     * @param int $limit
     * @return Holidays $holidayList
     */
    public function getHolidayList($year = null, OperationalCountry $operationalCountry = null, $offset = 0, $limit = 50) {
        $holidayList = $this->getHolidayDao()->getHolidayList($year, $operationalCountry, $offset, $limit);
        return $holidayList;
    }

    /**
     * Search Holidays within a given leave period
     * @param String $startDate
     * @param String $endDate
     * @return Holidays
     */
    public function searchHolidays($startDate = null, $endDate = null) {

        $holidayList = $this->getHolidayDao()->searchHolidays($startDate, $endDate);
        
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        
        $startYear = $startDate->format('Y');
        $endYear = $endDate->format('Y');

        $results = array();
        
        foreach ($holidayList as $holiday) {
            if ($holiday->getRecurring() == 1) {
                
                $holidayDate = new DateTime($holiday->getDate());
                
                for ($year = $startYear; $year <= $endYear; $year++) {
                    
                    $recurringDateStr = "{$year}-{$holidayDate->format('m')}-{$holidayDate->format('d')}";                    
                    $recurringDate = new DateTime($recurringDateStr);
                    
                    if ($recurringDate >= $startDate && $recurringDate <= $endDate) {
                        $recurringHoliday = $holiday->copy();
                        $recurringHoliday->setDate($recurringDateStr);
                        $recurringHoliday->setId($holiday->getId());
                        
                        $results[] = $recurringHoliday;
                    }
                }
                             
            } else {
                $results[] = $holiday;
            }
        }
        
        usort($results, function($a, $b) {
            return strtotime($a->getDate()) - strtotime($b->getDate());
        });
        
        return array_values($results);
    }

    /**
     * check whether the given date is a holiday
     *
     * @param date $day
     * @return boolean
     * 
     */
    public function isHoliday($day) {

        $holiday = $this->getHolidayDao()->readHolidayByDate($day);

        if ($holiday != null && $holiday->getLength() == WorkWeek::WORKWEEK_LENGTH_FULL_DAY) {
            return true;
        }

        return false;
    }

    /**
     * Findout whether day is a half day
     * @param Date $day
     * @returns boolean
     * @throws LeaveServiceException
     */
    public function isHalfDay($day) {

        $holiday = $this->getHolidayDao()->readHolidayByDate($day);

        if ($holiday != null && $holiday->getLength() >= WorkWeek::WORKWEEK_LENGTH_HALF_DAY && $holiday->getLength() < WorkShift::DEFAULT_WORK_SHIFT_LENGTH) {
            return true;
        }

        return false;
    }

    /**
     * check whether the given date is a holiday
     *
     * @param date $day
     * @return boolean
     * 
     */
    public function isHalfdayHoliday($day) {

        $holiday = $this->getHolidayDao()->readHolidayByDate($day);

        if ($holiday != null && $holiday->getLength() == Holiday::HOLIDAY_HALF_DAY_LENGTH) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Holiday full holiday list
     * @return Holidays $holidayList
     */
    public function getFullHolidayList() {

        $holidayList = $this->getHolidayDao()->getFullHolidayList();
        return $holidayList;
    }

}
