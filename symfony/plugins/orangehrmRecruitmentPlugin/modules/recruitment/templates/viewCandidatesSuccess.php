<?php
/*** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 *
 */
?>
<?php use_javascripts_for_form($form); ?>
<?php use_stylesheets_for_form($form); ?>
<?php use_javascript(plugin_web_path('orangehrmRecruitmentPlugin', 'js/viewCandidatesSuccess')); ?>

<div class="box searchForm toggableForm" id="srchCandidates">
    <div class="head">
        <h1><?php echo __('Candidates'); ?></h1>
    </div>

    <div class="inner">

        <form name="frmSrchCandidates" id="frmSrchCandidates" method="post" action="<?php echo url_for('recruitment/viewCandidates'); ?>">
            
            <fieldset>
              
                <ol>
                    <?php echo $form->render(); ?>
                    <?php include_component('core', 'ohrmPluginPannel', array('location' => 'listing_layout_navigation_bar_1')); ?>
                </ol>
                            
                <p>
                    <input type="button" id="btnSrch" value="<?php echo __("Search") ?>" name="btnSrch" />
                    <input type="button" class="reset" id="btnRst" value="<?php echo __("Reset") ?>" name="btnSrch" />                    
                </p>
            </fieldset>




            
        </form>
    </div>
    <a href="#" class="toggle tiptip" title="<?php echo __(CommonMessages::TOGGABLE_DEFAULT_MESSAGE); ?>">&gt;</a>
</div>
<?php include_component('core', 'ohrmList', $parmetersForListCompoment); ?>


<!-- Confirmation box HTML: Begins -->
<div class="modal hide" id="deleteConfirmation">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h3><?php echo __('LASU.HRM - Confirmation Required'); ?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo __(CommonMessages::DELETE_CONFIRMATION); ?></p>
    </div>
    <div class="modal-footer">
        <input type="button" class="btn" data-dismiss="modal" id="dialogDeleteBtn" value="<?php echo __('Ok'); ?>" />
        <input type="button" class="btn reset" data-dismiss="modal" value="<?php echo __('Cancel'); ?>" />
    </div>
</div>
<!-- Confirmation box HTML: Ends -->

<form name="frmHiddenParam" id="frmHiddenParam" method="post" action="<?php echo url_for('recruitment/viewCandidates'); ?>">
    <input type="hidden" name="pageNo" id="pageNo" value="<?php //echo $form->pageNo;        ?>" />
    <input type="hidden" name="hdnAction" id="hdnAction" value="search" />
</form>


<script type="text/javascript">

    function submitPage(pageNo) {

        document.frmHiddenParam.pageNo.value = pageNo;
        document.frmHiddenParam.hdnAction.value = 'paging';
        document.getElementById('frmHiddenParam').submit();

    }
    //<![CDATA[
    var datepickerDateFormat = '<?php echo get_datepicker_date_format($sf_user->getDateFormat()); ?>';
    var lang_validDateMsg = '<?php echo __(ValidationMessages::DATE_FORMAT_INVALID, array('%format%' => str_replace('yy', 'yyyy', get_datepicker_date_format($sf_user->getDateFormat())))) ?>';
    var candidates = <?php echo str_replace('&#039;', "'", $form->getCandidateListAsJson()) ?> ;
    var vacancyListUrl = '<?php echo url_for('recruitment/getVacancyListForJobTitleJson?jobTitle='); ?>';
    var hiringManagerListUrlForJobTitle = '<?php echo url_for('recruitment/getHiringManagerListJson?jobTitle='); ?>';
    var hiringManagerListUrlForVacancyId = '<?php echo url_for('recruitment/getHiringManagerListJson?vacancyId='); ?>';
    var addCandidateUrl = '<?php echo url_for('recruitment/addCandidate'); ?>';
    var lang_all = '<?php echo __("All") ?>';
    var lang_dateError = '<?php echo __("To date should be after from date") ?>';
    var lang_helpText = '<?php echo __("Click on a candidate to perform actions") ?>';
    var candidatesArray = eval(candidates);
    var lang_enterValidName = '<?php echo __(ValidationMessages::INVALID) ?>';
    var lang_typeForHints = '<?php echo __("Type for hints") . "..."; ?>';
    var lang_enterCommaSeparatedWords = '<?php echo __("Enter comma separated words") . "..."; ?>';
    var allowedCandidateListToDelete = <?php echo json_encode($form->allowedCandidateListToDelete); ?>;

    //]]>
</script>

