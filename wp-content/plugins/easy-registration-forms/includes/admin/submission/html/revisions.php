<div class="erf-wrapper wrap">
    <div class="erf-page-title">
        <h1><?php printf(__('Change history for #%d','erforms'),$submission['id']); ?></h1>
    </div>
    <div class="erf-submission-from erf-feature-request">
        <a href="<?php echo admin_url('admin.php?page=erforms-submission&submission_id='.$submission['id']); ?>"><?php _e('Go Back To original Submission','erforms'); ?></a>
    </div>
    <div class="erforms-admin-content">
        <?php 
            foreach($revisions as $revision){
                erforms_admin_submission_table($revision);    
            }
        ?>
    </div>
</div>