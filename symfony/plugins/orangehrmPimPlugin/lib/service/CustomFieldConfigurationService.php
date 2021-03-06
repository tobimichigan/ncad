<?php
/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */

/**
 * CustomFields Service
 * @package pim
 * @todo Rename to CustomFieldsConfigurationService [DONE]
 * @todo Remove exceptions that only wraps DAO exceptions [DONE]
 */
class CustomFieldConfigurationService extends BaseService {
	//not sure of the business purpose of the constants, need to check their references
   const FIELD_TYPE_STRING			=	0 ;
   const FIELD_TYPE_DROP_DOWN		=	1 ;
   const NUMBER_OF_FIELDS			=	10 ;

   /**
    * @ignore
    * @var CustomFieldConfigurationDao 
    */
   private $customFieldsDao;
   
   /**
    * Constructor
    */
   public function __construct() {
      $this->customFieldsDao = new CustomFieldConfigurationDao();
   }

   /**
    * @ignore
    * 
    * Sets CustomFieldsDao
    * @param CustomFieldConfigurationDao $customFieldsDao
    */
   public function setCustomFieldsDao(CustomFieldConfigurationDao $customFieldsDao) {
      $this->customFieldsDao = $customFieldsDao;
   }

   /**
    * @ignore
    * 
    * Returns CustomFieldsDao
    * @return CustomFieldConfigurationDao
    */
   public function getCustomFieldsDao() {
      return $this->customFieldsDao;
   }

   /**
    * Retrieve Custom Fields
    * @param String $screen
    * @param String $orderField
    * @param String $orderBy
    * @returns Collection
    * @throws DaoException
    * 
    * @todo rename method as searchCustomFieldList( $sortField , $sortOrder, $filters ) [DONE: Won't change. Can be implemented on request]
    */
   public function getCustomFieldList($screen = null, $orderField = "name", $orderBy = "ASC") {
       return $this->customFieldsDao->getCustomFieldList($screen, $orderField, $orderBy);
   } 
    
   /**
    * Save sustom field
    * 
    * Saves the given custom field object
    * 
    * @param CustomField $customField
    * @return CustomField
    * @throws DaoException, DuplicateNameException
    * 
    * @todo return saved entity [Done]
    * @todo rename entity as CustomField [Done]
    */
   public function saveCustomField(CustomField $customField) {
       
       $customField = $this->customFieldsDao->saveCustomField($customField);

       $reportGeneratorService = new ReportGeneratorService();      
       $reportGeneratorService->saveCustomDisplayField($customField, "3");
      
       return $customField;

   }
    
   /**
    * Delete CustomField
    * @param array $customFieldIdList
    * @returns integer Number of records deleted
    * @throws DaoException
    * 
    * @todo rename method as deleteCustomFields [DONE: There was no change to be done]
    * @todo return number of items deleted [DONE]
    */
   public function deleteCustomFields($customFieldIdList) {

      $reportGeneratorService = new ReportGeneratorService();
      $reportGeneratorService->deleteCustomDisplayFieldList($customFieldIdList);
      
      return $this->customFieldsDao->deleteCustomFields($customFieldIdList);

   }
    
   /**
    * Returns CustomField by Id. This need to be update to retrieve entity object
    * @param int $id
    * @return CustomField CustomField object on success and null if not found
    * @throws DaoException
    * 
    * @todo rename method as getCustomeField [DONE]
    */
   public function getCustomField($id) {
       return $this->customFieldsDao->getCustomField($id);
   }
    
   /**
    * @ignore
    * 
    * Retrievs available field numbers
    * @returns array()
    * @throws AdminServiceException
    * 
    * @todo remove method since it not used any where [DONE]
    */
    /*
    public function getAvailableFieldNumbers() {

        $availableFields	=	array();

        $customFieldList = $this->getCustomFieldList();
        for( $i=1 ; $i<= self::NUMBER_OF_FIELDS ; $i++) {
            $avaliabe	=	true; 
            foreach( $customFieldList as $customField) {
                if($customField->getFieldNum() == $i ) {
                    $avaliabe	=	false; 
                }
            }
            if( $avaliabe )
                array_push($availableFields,$i);			
        }

        return $availableFields;

    }
    */
   
   
}
