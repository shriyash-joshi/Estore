<?php
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
    $form= erforms()->form->get_form($form_id);
    $options= erforms()->options->get_options();
    if(empty($form))
        return;
?>
<div class="erf-wrapper wrap">
    <div class="erf-page-title">
        <?php
            switch($active_tab){
                case 'build': $title = __('Build', 'erforms') ; break;
                case 'configure': $title = __('Configure', 'erforms') ; break;
                case 'report': $title = __('Report', 'erforms') ; break;
                case 'reports': $title = __('Reports', 'erforms') ; break;
                case 'notifications': $title = __('Notifications', 'erforms') ; break;
                default : $title = __('Form Settings', 'erforms') ;
             }
             $title= apply_filters('erf_dashboard_tab_title', $title, $active_tab);
            ?> 
        <h1 class="wp-heading-inline">
            <?php echo ucwords($form['title']); ?> - <?php echo $title ?>
        </h1>
            <ul class="erf-nav">
                    <li class="erf-nav-item">
                        <a href="?page=erforms-overview">
                            <img width="24" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/edit_submission.png">
                            <?php _e('All Forms', 'erforms'); ?>
                        </a>
                    </li>
                <?php if(!empty($active_tab)): ?>
                    <li class="erf-nav-item">
                        <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>">
                            <img width="24" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/configuration/display-icon.png">
                            <?php _e('Dashboard', 'erforms'); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="erf-nav-item">
                    <a target="_blank" href="<?php echo add_query_arg(array('erform_id'=>$form_id),get_permalink($options['preview_page'])); ?>">
                        <img width="24" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/preview-link.png">
                        <?php _e('Preview', 'erforms'); ?>
                    </a>
                </li>    
            </ul>
        
    </div>
    <div class="erforms-new-form clearfix">
        <?php if (empty($active_tab)): ?>
            <div class="erf-form-dashboard">
                <div class="erf-dashboard-section">
                    <div class="erf-section-title"><?php _e('Manage','erforms'); ?></div>
                    <div class="erf-section-item-container">
                        <div class="erf-section-item">
                            <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=build">
                                <div>
                                    <img title="<?php _e('Create, edit and manage fields.','erforms'); ?>" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/manage.png"><span><?php _e('Fields', 'erforms'); ?></span>
                                </div>
                            </a>
                        </div>
                        <div class="erf-section-item">
                            <a href="?page=erforms-submissions&erform_id=<?php echo $form_id; ?>">
                                <div>
                                    <img title="<?php _e('Shows list of all the submissions. Allows to filter/export submission records.','erforms'); ?>" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/submissions.png"><span><?php _e('Submissions', 'erforms'); ?></span>
                                </div>
                            </a>
                        </div>

                        <div class="erf-section-item">
                            <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=attachments">
                                <div>
                                    <img title="<?php _e('Enable email notifications. Modify email contents with mail merge feature.','erforms'); ?>" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/attachments.png">
                                    <span><?php _e('Attachments', 'erforms'); ?></span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="erf-dashboard-section">
                    <div class="erf-section-title"><?php _e('Configure','erforms'); ?></div>
                    <div class="erf-section-item-container">
                        <?php $menus= erforms_form_configuration_menus($form['type']); ?>
                        <?php foreach($menus as $slug=>$menu): ?>
                        <div class="erf-section-item">
                            <a href="?page=erforms-dashboard&form_id=<?php echo $form['id']; ?>&tab=configure&type=<?php echo $slug; ?>">
                                <img title="<?php echo implode(',',$menu['desc']);  ?>" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/configuration/<?php echo $slug; ?>-icon.png">
                                <span><?php echo $menu['label'];  ?></span>                       
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="erf-dashboard-section">
                    <div class="erf-section-title"><?php _e('Notifications','erforms'); ?></div>
                    <div class="erf-section-item-container">
                        <?php $notifications= erforms_system_form_notifications($form['type']); ?> 
                        <?php foreach($notifications as $type=>$label): $notification= erforms_system_form_notification_by_type($type,$form); ?>
                        <div class="erf-section-item">
                            <a  href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=notifications&type=<?php echo $type; ?>">
                                <img title="<?php echo $notification['help'] ?>" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/<?php echo $label; ?>.png">
                                <span><?php echo $label; ?></span>    
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                
                <div class="erf-dashboard-section">
                    <div class="erf-section-title"><?php _e('Reports','erforms'); ?></div>
                    <div class="erf-section-item-container">
                        <div class="erf-section-item">
                            <a  href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=reports">
                                <div><img title="<?php _e("Reports allows you to relay information (Submissions) to interested parties in a periodic manner. You can create multiple reports to share different submitted information with people.For each report, System will generate a CSV file containing the submitted information (which is selected by you) and will send an email to all it's recipients. In short <b>Report</b> allows you to share selected information from the submission to multiple people.",'erforms'); ?>" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/scheduled-reports.png">
                                    <span><?php _e('Scheduled Reports', 'erforms'); ?></span>
                                </div>
                            </a>
                        </div>
                        <div class="erf-section-item">
                            <a  href="?page=erforms-analytics&form_id=<?php echo $form_id; ?>">
                                <div><img title="<?php _e("Graph view of submissions over time.",'erforms'); ?>" src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/analytics.png">
                                      <span><?php _e('Analytics', 'erforms'); ?></span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="erf-dashboard-section">
                    <div class="erf-section-title"><?php _e('Add-ons','erforms'); ?></div>
                    <div class="erf-section-item-container"><?php do_action('erf_dashboard_addons_section', $form, $active_tab); ?></div>
                </div>
            </div>
        <div class="dashboard-sidebar">
            <div class="erf-sidebar-column-wrap short-code-column">
    <div class="erf-section-title"><?php _e('Shortcode','erforms'); ?></div>
    <div class="erf-section-body">
        <div class='erf-short-code-wrap erf-pop-wrap'>
            <?php
            $shortcode = '[erforms id="'.$form_id.'"]';
            ?>
            <input type='text' class='erf-shortcode' value='<?php echo $shortcode; ?>' readonly>
            <span style='display: none;' class='copy-message'><?php _e('Copied to Clipboard','erforms'); ?></span>
        </div>
    </div>
</div>
<div class="erf-sidebar-column-wrap">
    <div class="erf-section-title"><?php _e('Contact','erforms'); ?></div>
    <div class="erf-section-body">
        <div class='erf-contact-info'>
            <?php _e('<a target="_blank" href="https://www.easyregistrationforms.com/request-customization/">Request Customization</a><br/><a target="_blank" href="https://www.easyregistrationforms.com/support-forum/">Support Forum</a><br/><a target="_blank" href="https://www.easyregistrationforms.com/support/">Plugin Support</a>.','erforms'); ?>
        </div>
    </div>
</div>
<div class="erf-sidebar-column-wrap">
        <div class="erf-section-title"><?php _e('Plugin Bundle','erforms'); ?></div>
        <div class="erf-section-body">
            <div class="erf-add-ons">
                <a target="_blank" href="https://www.easyregistrationforms.com/product/plugin-bundle/">
                    <div class="add-on-img">
                        <img src="<?php echo ERFORMS_PLUGIN_URL?>assets/admin/images/addons/plugin-bundle.png">
                    </div>
                    <p>All our premium features in one bundle.</p>
                </a>
            </div>
        </div>
    </div>
</div>
        </div>
        <?php else: ?>
            <?php
                switch($active_tab){
                    case 'build': include('build.php'); break;
                    case 'configure': include('configure.php'); break;
                    case 'report': include('report.php'); break;
                    case 'reports': include('reports.php'); break;
                    case 'notifications': include('notifications.php'); break;
                    case 'attachments': include('attachments.php'); break;
                    case 'submission_attachments': include('submission_attachments.php'); break;
                }
                do_action('erf_dashboard_tabs', $form, $active_tab);
            ?>    
        <?php endif; ?>
    </div>    
</div>

