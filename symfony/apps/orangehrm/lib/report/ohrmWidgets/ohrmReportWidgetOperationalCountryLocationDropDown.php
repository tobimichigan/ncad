<?php

/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */

/**
 * Widget that displays countries and locations within the country
 * in a single drop down
 *
 */
class ohrmReportWidgetOperationalCountryLocationDropDown extends ohrmWidgetSelectableGroupDropDown implements ohrmEnhancedEmbeddableWidget {

    private $operationalCountryService;
    private $choices = null;

    protected function configure($options = array(), $attributes = array()) {

        parent::configure($options, $attributes);

        // Parent requires the 'choices' option.
        $this->addOption('choices', array());
        $this->addOption('set_all_option_value', true);
        if (isset($options['set_all_option_value']) && $options['set_all_option_value']) {
            $this->addOption('all_option_value', '-1');
        }
        $this->addOption('show_all_locations', false);
    }

    /**
     * Get array of operational country and location choices
     */
    public function getChoices() {

        if (is_null($this->choices)) {

            $operationalCountries = $this->getOperationalCountryService()->getOperationalCountryList();
            $locationList = $this->_getLocationList();

            $showAll = $this->getOption('show_all_locations');

            $choices = array();
            $addedLocationIds = array();
            $allCountriesAccessible = true;

            // adding locations that assigned to operational country first
            $accessibleCountries = UserRoleManagerFactory::getUserRoleManager()->getAccessibleEntityIds('OperationalCountry');

            foreach ($operationalCountries as $operationalCountry) {

                $country = $operationalCountry->getCountry();

                if ($showAll || in_array($operationalCountry->getId(), $accessibleCountries)) {
                    $locations = $country->getLocation();

                    if (count($locations) > 0) {
                        $locationChoices = array();
                        foreach ($locations as $location) {
                            $addedLocationIds[] = $location->getId();
                            $locationChoices[$location->getId()] = $location->getName();
                        }
                        asort($locationChoices);
                        $choices[$country->getCouName()] = $locationChoices;
                    }
                } else {
                    $allCountriesAccessible = false;
                }
            }

            //after that, adding all the remaining locations to the list
            foreach ($locationList as $countryName => $locations) {
                $locationChoices = array();
                if (!array_key_exists($countryName, $choices)) {
                    foreach ($locations as $location) {
                        if (!in_array($location->getId(), $addedLocationIds)) {
                            $locationChoices[$location->getId()] = $location->getName();
                        }
                    }

                    $choices[$countryName] = $locationChoices;
                }
            }
            
            if ($allCountriesAccessible) {
                if ($this->getOption('show_select_option')) {
                    $this->setOption('select_option_value', -1);                    
                } else if ($this->getOption('show_all_option') && $this->hasOption('all_option_value')) {
                    $this->setOption('all_option_value', -1);
                }
            }

            $this->choices = $choices;
        }

        return $this->choices;
    }

    public function getValidValues() {
        $choices = $this->getChoices();
        return array_keys($choices);
    }

    public function embedWidgetIntoForm(sfForm &$form) {

        $requiredMess = 'Select a location';

        $widgetSchema = $form->getWidgetSchema();
        $widgetSchema[$this->attributes['id']] = $this;
        $label = ucwords(str_replace("_", " ", $this->attributes['id']));
        $validator = new sfValidatorString();
        if (isset($this->attributes['required']) && ($this->attributes['required'] == "true")) {
            $label .= "<span class='required'> * </span>";
            $validator = new sfValidatorString(array('required' => true), array('required' => $requiredMess));
        }
        $widgetSchema[$this->attributes['id']]->setLabel($label);
        $form->setValidator($this->attributes['id'], $validator);
    }

    /**
     * Sets whereClauseCondition.
     * @param string $condition
     */
    public function setWhereClauseCondition($condition) {

        $this->whereClauseCondition = $condition;
    }

    /**
     * Gets whereClauseCondition. ( if whereClauseCondition is set returns that, else returns default condition )
     * @return string ( a condition )
     */
    public function getWhereClauseCondition() {

        if (isset($this->whereClauseCondition)) {
            $setCondition = $this->whereClauseCondition;
            return $setCondition;
        } else {
            $defaultCondition = "IN";
            return $defaultCondition;
        }
    }

    /**
     * This method generates the where clause part.
     * @param string $fieldName
     * @param string $value
     * @return string
     */
    public function generateWhereClausePart($fieldName, $value) {

        if ($value == '-1') {
            $whereClausePart = null;
        } else {
            $whereClausePart = $fieldName . " " . $this->getWhereClauseCondition() . " " . $value;
        }

        return $whereClausePart;
    }

    /**
     *
     * @param OperationalCountryService $service 
     */
    public function setOperationalCountryService(OperationalCountryService $service) {
        $this->operationalCountryService = $service;
    }

    /**
     * 
     * @return OperationalCountryService
     */
    public function getOperationalCountryService() {
        if (!($this->operationalCountryService instanceof OperationalCountryService)) {
            $this->operationalCountryService = new OperationalCountryService();
        }
        return $this->operationalCountryService;
    }

    /**
     * Gets all locations.
     * @return string[] $locationList
     */
    private function _getLocationList() {
        $locationService = new LocationService();

        $showAll = $this->getOption('show_all_locations');

        $locationList = array();
        $locations = $locationService->getLocationList();

        $accessibleLocations = UserRoleManagerFactory::getUserRoleManager()->getAccessibleEntityIds('Location');

        foreach ($locations as $location) {
            if ($showAll || in_array($location->id, $accessibleLocations)) {
                $locationList[$location->getCountry()->getCouName()][] = $location;
            }
        }

        return ($locationList);
    }

    public function getDefaultValue(SelectedFilterField $selectedFilterField) {
        return $selectedFilterField->value1;
    }

}