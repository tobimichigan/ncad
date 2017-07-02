<?php 

// Allow header partial to be overridden in individual actions
// Can be overridden by: slot('header', get_partial('module/partial'));
include_slot('header', get_partial('global/header'));
$currentYear = date('Y');
?>

    </head>
    <body>
      
        <div id="wrapper">
            
            <div id="branding">
                <img src="<?php echo theme_path('images/logo.png')?>" width="600" height="150" alt="OrangeHRM"/>
                <?php //echo <a href="http://www.lasu.edu.ng/user-survey-registration.php" class="subscribe" target="_blank"> __('Join OrangeHRM Community')</a>; ?>
                <a href="#" id="welcome" class="panelTrigger"><?php echo __("Welcome %username%", array("%username%" => $sf_user->getAttribute('auth.firstName'))); ?></a>
                <div id="welcome-menu" class="panelContainer">
                    <ul>
                        <li><a href="<?php echo url_for('admin/changeUserPassword'); ?>"><?php echo __('Change Password'); ?></a></li>
                        <li><a href="<?php echo url_for('auth/logout'); ?>"><?php echo __('Logout'); ?></a></li>
                    </ul>
                </div>
                <a href="#" id="help" class="panelTrigger"><?php //echo __("Help & Training"); ?></a>
                <div id="help-menu" class="panelContainer">
                    <ul>
                        <li><a href="http://www.lasu.edu.ng/support-plans.php?utm_source=application_support&amp;utm_medium=app_url&amp;utm_campaign=orangeapp" target="_blank"><?php //echo __('Support'); ?></a></li>
                        <li><a href="http://www.lasu.edu.ng/training.php?utm_source=application_traning&amp;utm_medium=app_url&amp;utm_campaign=orangeapp" target="_blank"><?php //echo __('Training'); ?></a></li>
                        <li><a href="http://www.lasu.edu.ng/addon-plans.shtml?utm_source=application_addons&amp;utm_medium=app_url&amp;utm_campaign=orangeapp" target="_blank"><?php //echo __('Add-Ons'); ?></a></li>
                        <li><a href="http://www.lasu.edu.ng/customizations.php?utm_source=application_cus&amp;utm_medium=app_url&amp;utm_campaign=orangeapp" target="_blank"><?php //echo __('Customizations'); ?></a></li>
                        <li><a href="http://forum.lasu.edu.ng/" target="_blank"><?php //echo __('Forum'); ?></a></li>
                        <li><a href="http://blog.lasu.edu.ng/" target="_blank"><?php //echo __('Blog'); ?></a></li>
                        <li><a href="http://sourceforge.net/apps/mantisbt/orangehrm/view_all_bug_page.php" target="_blank"><?php //echo __('Bug Tracker'); ?></a></li>                        
                    </ul>
                </div>
            </div> <!-- branding -->      
            
            <?php include_component('core', 'mainMenu'); ?>

            <div id="content">

                  <?php echo $sf_content ?>

            </div> <!-- content -->
          
        </div> <!-- wrapper -->
        
         <div id="footer">
            <strong><a href="http://lasunigeria.edu.ng">LAGOS STATE UNIVERSITY HUMAN RESOURCE MANAGER</a></strong> &copy; by <a href="http://www.lasu.edu.ng" target="_blank">LASUHRM</a>. 1983 - <?php echo $currentYear?> All rights reserved.
        </div> <!-- footer -->  

        
        
<?php include_slot('footer', get_partial('global/footer'));?>