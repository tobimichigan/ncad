<?php

/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 *
 */
class JobTitleForm extends BaseForm {

    private $jobTitleService;
    public $jobTitleId;
    public $attachment;

    public function getJobTitleService() {
        if (is_null($this->jobTitleService)) {
            $this->jobTitleService = new JobTitleService();
            $this->jobTitleService->setJobTitleDao(new JobTitleDao());
        }
        return $this->jobTitleService;
    }

    const CONTRACT_KEEP = 1;
    const CONTRACT_DELETE = 2;
    const CONTRACT_UPLOAD = 3;

    public function configure() {

        $this->jobTitleId = $this->getOption('jobTitleId');

        $jobSpecUpdateChoices = array(self::CONTRACT_KEEP => __('Keep Current'),
            self::CONTRACT_DELETE => __('Delete Current'),
            self::CONTRACT_UPLOAD => __('Replace Current'));

        $this->setWidgets(array(
            'jobTitle' => new sfWidgetFormInputText(),
            'jobDescription' => new sfWidgetFormTextArea(),
            'note' => new sfWidgetFormTextArea(),
            'jobSpec' => new sfWidgetFormInputFile(),
            'jobSpecUpdate' => new sfWidgetFormChoice(array('expanded' => true, 'choices' => $jobSpecUpdateChoices))
        ));

        $this->setValidators(array(
            'jobTitle' => new sfValidatorString(array('required' => true, 'max_length' => 100)),
            'jobDescription' => new sfValidatorString(array('required' => false, 'max_length' => 400, 'trim' => true)),
            'note' => new sfValidatorString(array('required' => false, 'max_length' => 400, 'trim' => true)),
            'jobSpec' => new sfValidatorFile(array('required' => false, 'max_size' => 1024000,
                'validated_file_class' => 'orangehrmValidatedFile')),
            'jobSpecUpdate' => new sfValidatorString(array('required' => false))
        ));

        $this->widgetSchema->setNameFormat('jobTitle[%s]');

        if (!empty($this->jobTitleId)) {
            $jobTitle = $this->getJobTitleService()->getJobTitleById($this->jobTitleId);

            $this->setDefault('jobTitle', $jobTitle->getJobTitleName());
            $this->setDefault('jobDescription', $jobTitle->getJobDescription());
            $this->setDefault('note', $jobTitle->getNote());

            $this->attachment = $jobTitle->getJobSpecificationAttachment();
        }
    }

    public function save() {
        $resultArray = array();

        $jobTitle = $this->getValue('jobTitle');
        $jobDescription = $this->getValue('jobDescription');
        $note = $this->getValue('note');
        $jobSpec = $this->getValue('jobSpec');
        $jobSpecUpdate = $this->getValue('jobSpecUpdate');

        if (!empty($this->jobTitleId)) {
            $jobTitleObj = $this->getJobTitleService()->getJobTitleById($this->jobTitleId);
            $attachment = $jobTitleObj->getJobSpecificationAttachment();
            if (!empty($attachment) && $jobSpecUpdate != self::CONTRACT_KEEP) {
                $attachment->delete();
            }
            $resultArray['messageType'] = 'success';
            $resultArray['message'] = __(TopLevelMessages::UPDATE_SUCCESS);
        } else {
            $jobTitleObj = new JobTitle();
            $resultArray['messageType'] = 'success';
            $resultArray['message'] = __(TopLevelMessages::SAVE_SUCCESS);
        }

        $jobTitleObj->setJobTitleName($jobTitle);
        $jobTitleObj->setJobDescription($jobDescription);
        $jobTitleObj->setNote($note);
        if (!empty($jobSpec)) {
            $jobTitleObj->setJobSpecificationAttachment($this->__getJobSpecAttachmentObj());
        } else {
            $jobTitleObj->setJobSpecificationAttachment(null);
        }

        $jobTitleObj->save();


        return $resultArray;
    }

    private function __getJobSpecAttachmentObj() {

        $jobSpec = $this->getValue('jobSpec');

        $jobSpecAttachement = new JobSpecificationAttachment();

        $jobSpecAttachement->setFileName($jobSpec->getOriginalName());
        $jobSpecAttachement->setFileType($jobSpec->getType());
        $jobSpecAttachement->setFileSize($jobSpec->getSize());
        $jobSpecAttachement->setFileContent(file_get_contents($jobSpec->getTempName()));

        return $jobSpecAttachement;
    }

    public function getJobTitleListAsJson() {

        $list = array();
        $jobTitleList = $this->getJobTitleService()->getJobTitleList();
        foreach ($jobTitleList as $job) {
            $list[] = array('id' => $job->getId(), 'name' => $job->getJobTitleName());
        }
        return json_encode($list);
    }

}
