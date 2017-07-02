<?php

/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 */

/**
 * @todo Handle past employees
 * @todo Showing/not showing duplicate names
 * @todo If full name is pasted, hideen ID is not set
 * @todo Array or ajax switch
 * @todo Validating inside the widget
 */

class ohrmWidgetEmployeeNameAutoFill extends sfWidgetFormInput {
    
    
    
    public function configure($options = array(), $attributes = array()) {

        $this->addOption('employeeList', '');
        $this->addOption('jsonList', '');
        $this->addOption('loadingMethod','');
        $this->addOption('requiredPermissions', array());
        $this->addOption('typeHint', __('Type for hints') . '...');
    }    

    public function render($name, $value = null, $attributes = array(), $errors = array()) {

        $empNameValue       = isset($value['empName'])?$value['empName']:'';
        $empIdValue         = isset($value['empId'])?$value['empId']:'';
        $attributes['type'] = 'text';
        
        $html           = parent::render($name . '[empName]', $empNameValue, $attributes, $errors);
        $typeHint       = $this->getOption('typeHint');
        $hiddenFieldId  = $this->getHiddenFieldId($name);

        $javaScript     = sprintf(<<<EOF
        <script type="text/javascript">

            var employees_%s = %s;

            $(document).ready(function() {
            
                var nameField = $("#%s");
                var idStoreField = $("#%s");
                var typeHint = '%s';
                var hintClass = 'inputFormatHint';
                var loadingMethod = '%s';
                var loadingHint = '%s';
            
                nameField.data('typeHint', typeHint);
                nameField.data('loadingHint', loadingHint);
                
                nameField.one('focus', function() {

                        if ($(this).hasClass(hintClass)) {
                            $(this).val("");
                            $(this).removeClass(hintClass);
                        }

                    });
                    
                if( loadingMethod != 'ajax'){
                    if (nameField.val() == '' || nameField.val() == typeHint) {
                        nameField.val(typeHint).addClass(hintClass);
                    }

                    

                    nameField.autocomplete(employees_%s, {

                        formatItem: function(item) {
                            return $('<div/>').text(item.name).html();
                        },
                        formatResult: function(item) {
                            return item.name
                        }
                      ,matchContains:true
                        }).result(function(event, item) {
                            idStoreField.val(item.id);
                        }

                    );
                 }else{
                        var value = nameField.val().trim();
                        nameField.val(loadingHint).addClass('ac_loading');
                        $.ajax({
                               url: "%s",
                               data: "",
                               dataType: 'json',
                               success: function(employeeList){

                                     nameField.autocomplete(employeeList, {

                                                formatItem: function(item) {
                                                    return $('<div/>').text(item.name).html();
                                                },
                                                formatResult: function(item) {
                                                    return item.name
                                                }
                                                
                                                ,matchContains:true
                                            }).result(function(event, item) {
                                                idStoreField.val(item.id);
                                            }

                                        );
                                         nameField.removeClass('ac_loading'); 
                                        
                                         if(value==''){
                                            nameField.val(typeHint).addClass(hintClass);
                                         } else {
                                            nameField.val(value).addClass();
                                         }
                                    }
                             });
                 }
                
            }); // End of $(document).ready

                 
        </script>
EOF
                        ,
                        $this->generateId($name),
                        $this->getEmployeeListAsJson($this->getEmployeeList()),
                        $this->getHtmlId($name),
                        $hiddenFieldId,
                        $typeHint,
                        $this->getOption('loadingMethod'),
                        __('Loading'),                
                        $this->generateId($name),
                        url_for('pim/getEmployeeListAjax'));
                        
        

        return "\n\n" . $html . "\n\n" . $this->getHiddenFieldHtml($name, $empIdValue) . "\n\n" . $javaScript . "\n\n";
        
    }
    
    protected function getHiddenFieldHtml($name, $value) {
        
        //$hiddenName = substr($name, 0, strlen($name) - 1) . '_id]';
        $hiddenName = $name . '[empId]';
        $hiddenId   = $this->getHiddenFieldId($name);
        
        return "<input type=\"hidden\" name=\"$hiddenName\" id=\"$hiddenId\" value=\"$value\" />";
        
    }
    
    protected function getHiddenFieldId($name) {
        
        return $this->generateId($name) . '_empId';
        
    }

    protected function getHtmlId($name) {
        
        if (isset($this->attributes['id'])) {
            return $this->attributes['id'];
        }
        
        return $this->generateId($name) . '_empName';
        
    }
    
    protected function getEmployeeList() {
        
        $employeeList = $this->getOption('employeeList');
        $loadingMethod = $this->getOption('loadingMethod');
        $requiredPermissions = $this->getOption('requiredPermissions');
        
        if (is_array($employeeList)) {
            return $employeeList;
        }

        if( $loadingMethod != 'ajax'){
            $properties = array("empNumber","firstName", "middleName", "lastName", "termination_id");
            $employeeList = UserRoleManagerFactory::getUserRoleManager()->getAccessibleEntityProperties('Employee', 
                    $properties, null, null, array(), array(), $requiredPermissions);
        
            return $employeeList;
        }else{
            return array();
        }
    }

    protected function getEmployeeListAsJson($employeeList) {
        
        $jsonList = $this->getOption('jsonList');
        
        if (!empty($jsonList)) {
            return $jsonList;
        }

        $jsonArray = array();        
        
        foreach ($employeeList as $employee) {
            $name = trim(trim($employee['firstName'] . ' ' . $employee['middleName'],' ') . ' ' . $employee['lastName']);
            if ($employee['termination_id']) {
                $name = $name. ' ('.__('Past Employee') .')';
            }
            $jsonArray[$employee['empNumber']] = array('name' => $name, 'id' => $employee['empNumber']);
        }
        usort($jsonArray, array($this, 'compareByName'));
        return json_encode($jsonArray);

    }
    
    protected function compareByName($employee1, $employee2) {
        return strcmp($employee1['name'], $employee2['name']);
    }

}

