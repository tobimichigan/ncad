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
 * PerformanceReview Dao class 
 *
 * @author Samantha Jayasinghe
 */
class PerformanceReviewDao extends BaseDao {
	
    /**
     * Save Performance Review
     * @param PerformanceReview $performanceReview
     * @return PerformanceReview
     */
    public function savePerformanceReview(PerformanceReview $performanceReview) {
        try {
            if ( $performanceReview->getId() == '') {
                $idGenService = new IDGeneratorService( );
                $idGenService->setEntity($performanceReview);
                $performanceReview->setId($idGenService->getNextID());
            }

            $performanceReview->save();
            return $performanceReview;

        } catch (Exception $e) {
            throw new DaoException ( $e->getMessage () );
        }
    }
    
    
 	/**
     * Read Performance Review
     * @param $reviewId
     * @return PerformanceReview
     */
    public function readPerformanceReview($reviewId) {

        try {
            $performanceReview = Doctrine::getTable('PerformanceReview')
            ->find($reviewId);
            return $performanceReview;
        } catch(Exception $e) {
            throw new DaoException ( $e->getMessage () );
        }
    }
    
    /**
     * Get Performance Review List
     * @return unknown_type
     */
    public function getPerformanceReviewList( )
    {
        try
        {
            $q = Doctrine_Query::create()
                ->from('PerformanceReview pr')
                ->orderBy('pr.id');

            $performanceReviewList = $q->execute();

            return  $performanceReviewList ;

        }catch( Exception $e)
        {
            throw new DaoException ( $e->getMessage() );
        }
    }

    /**
     * Delete PerformanceReview
     * @param array reviewList
     * @returns boolean
     * @throws PerformanceServiceException
     */
    public function deletePerformanceReview($reviewList) {

        try {

            $q = Doctrine_Query::create()
               ->delete('PerformanceReview')
               ->whereIn('id', $reviewList);
               $numDeleted = $q->execute();
            if($numDeleted > 0) {
               return true ;
            }
            return false;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage());
        }
    }

    /**
     * Builds the search query that fetches all the
     * records for given search clues
     */
    private function _getSearchReviewQuery($clues) {

        try {

            $from = $clues['from'];
            $to = $clues['to'];
            $jobCode = $clues['jobCode'];
            $divisionId = $clues['divisionId'];
            $empId = $clues['empId'];
            $reviewerId = $clues['reviewerId'];

            if (isset($clues['loggedReviewerId']) && $clues['loggedReviewerId'] != $clues['empId']) {
                $reviewerId = $clues['loggedReviewerId'];
            }

            if (isset($clues['loggedEmpId'])) {
                $empId = $clues['loggedEmpId'];
            }

            $q = Doctrine_Query::create()
                 ->from('PerformanceReview p')
                 ->leftJoin('p.Employee e');
            
            if (!empty($from)) {
                $q->andWhere("p.periodFrom >= ?", $from);
            }

            if (!empty($to)) {
                $q->andWhere("p.periodTo <= ?", $to);
            }

            if (!empty($empId)) {
                $q->andWhere("p.employeeId = ?", $empId);
            }

            if (!empty($reviewerId)) {

                /* $q->andWhere("reviewerId = ?", $reviewerId) throws
                 * "Invalid parameter number" error.
                 */

                if (empty($empId) && isset($clues['loggedReviewerId'])) {
                    $q->andWhere("(p.reviewerId = ? OR employeeId = ?)",
                             array($reviewerId, $reviewerId));
                } else {
                    $q->andWhere("p.reviewerId = ?", $reviewerId);
                }
            }

            if (!empty($jobCode)) {
                $q->andWhere("p.jobTitleCode = ?", $jobCode);
            }

            if (!empty($divisionId)) {
                $q->andWhere("p.subDivisionId = ?", $divisionId);
            }

            return $q;

        } catch(Exception $e) {
            throw new DaoException($e->getMessage());
        }

    }

    /**
     * Returns Object based on the combination of search
     * @param array $clues
     * @param array $offset
     * @param array $limit
     * @throws DaoException
     */
     
    public function searchPerformanceReview($clues, $offset=null, $limit=null) {

        try {

            $q = $this->_getSearchReviewQuery($clues);

            if (isset($offset) && isset($limit)) {
                $q->offset($offset)->limit($limit);
            }
            
            $q->orderBy('e.lastName ASC, e.firstName ASC');
            
            return $q->execute();

        } catch(Exception $e) {
            throw new PerformanceServiceException($e->getMessage());
        }

    }

    /**
     * Returns the count of records
     * that matched given $clues
     */

    public function countReviews($clues) {

        try {

            $q = $this->_getSearchReviewQuery($clues);

            return $q->count();

        } catch(Exception $e) {
            throw new PerformanceServiceException($e->getMessage());
        }

    }
    
     /**
     * Update status of performance review
     * @param array $clues
     * @param array $offset
     * @param array $limit
     * @throws DaoException
     */
    public function updatePerformanceReviewStatus( PerformanceReview $performanceReview , $status){
    	try {
             $q = Doctrine_Query::create()
				    ->update('PerformanceReview')
				    ->set("state='?'", $status)
				    ->where("id = ?",$performanceReview->getId());
                $q->execute();
                
                return true ;
			
        } catch(Exception $e) {
            throw new DaoException($e->getMessage());
        }
    }
}