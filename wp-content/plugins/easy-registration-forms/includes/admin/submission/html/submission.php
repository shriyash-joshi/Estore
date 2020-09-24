<?php
$options = erforms()->options->get_options();
$form = erforms()->form->get_form($submission['form_id']);
//erforms_debug($submission);
?>
<div class="erf-wrapper wrap">
    <div class="erf-page-title">
        <h1><?php echo $form['title']; ?> - <?php _e('Submission Details', 'erforms'); ?></h1>
    </div>
    <div class="erforms-admin-content">
        <div class="tablenav top">

            <div class="alignleft actions">
                <a id="erf_submission_add_note" class="button button-primary" href="javascript:void(0)"><?php _e('Add Note', 'erforms'); ?></a>
            </div>
            <div class="alignleft actions">
                <a id="erf_submission_print" class="button" href="#"><?php _e('Print', 'erforms'); ?></a>
            </div>
            
            <div class="alignleft actions">
                <form target="_blank" method="POST" action="<?php echo add_query_arg(array('erform_id'=>$submission['form_id']),get_permalink($options['preview_page'])); ?>">
                    <a class="button erf_edit_submission" href="javascript:void(0)"><?php _e('Edit', 'erforms'); ?></a>
                    <input type="hidden" name="submission_id" value="<?php echo esc_attr($submission['id']); ?>" />
                </form>
            </div>

            <?php if (!empty($form['en_edit_sub'])): ?>
                <?php if (isset($submission['dis_edit_submission']) && $submission['dis_edit_submission'] == 1): ?>
                    <div class="alignleft actions">
                        <a class="button button-primary" href="<?php print wp_nonce_url(admin_url('admin.php?page=erforms-submission&submission_id=' . $sub_id), 'enable_edit_submission', 'en_edit_sub_nonce'); ?>"><?php _e('Enable Edit Submission', 'erforms'); ?></a>
                    </div>
                <?php else: ?>
                    <div class="alignleft actions">
                        <a class="button button-danger" href="<?php print wp_nonce_url(admin_url('admin.php?page=erforms-submission&submission_id=' . $sub_id), 'disable_edit_submission', 'dis_edit_sub_nonce'); ?>"><?php _e('Disable Edit Submission', 'erforms'); ?></a>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
            
            <?php if(!empty($revisions)): ?>
                <div class="alignleft actions">
                    <a class="button button-primary" href="<?php echo admin_url('admin.php?page=erforms-submission&view=revisions&submission_id=' . $sub_id); ?>"><?php _e('History', 'erforms'); ?></a>
                </div>
            <?php endif; ?>
            
            <div class="alignleft actions">
                <a class="button button-danger" href="<?php print wp_nonce_url(admin_url('admin.php?page=erforms-submissions&submission_id=' . $sub_id), 'submission_delete', 'delete_nonce'); ?>"><?php _e('Delete', 'erforms'); ?></a>
            </div>
            
        </div>
        <?php if (!empty($submission['user'])): ?>
            <div class="erf-submission-from erf-feature-request">
                <strong><?php _e('Submission From: ', 'erforms'); ?></strong>
                <a target="_blank" href="<?php echo get_edit_user_link($submission['user']['ID']); ?>"><?php echo $submission['user']['user_email']; ?></a>
            </div>
        <?php endif; ?>
        <div class="erf-submission-tags clearfix">
            <?php
            // Show if any tag exists in the system.  
            $tags = erforms()->label->get_tags();
            $sanitized_tags = erforms()->label->get_tags(true);
            
            if(!empty($tags)){
                ?>
                <div class="erf-assigned-labels">
                    <?php
                    if(!empty($submission['tags'])){
                        foreach($submission['tags'] as $selected_tag){
                            echo '<div class="erf-label" style="background-color: '.$tags[$selected_tag].'">'.$selected_tag.'<span class="fa fa-times erf-remove-label" data-val="'.$selected_tag.'">X</span></div>';
                        }
                    }else{
                        echo '<div class="erf-no-label">'.__('No Label Assigned', 'erforms').'</div>';
                    }
                    ?>
                </div>
            <div class="erf-label-search">
                <input class="erf-input-field" type="search" placeholder="Labels">
                <div class="erf-label-list" style="display:none">
                    <div class="erf-label-heading">Apply labels to this submission</div>
                    <?php
                    foreach ($tags as $key => $value) {
                        if(!in_array($key, $submission['tags'])){
                            echo '<div class="erf-assign-label erf-submission-label"><span class="label-color" style="background-color: ' . $value. ';"></span>' . $key . '</div>';
                        }
                    }
                    ?>
                    <div class="add-new-label erf-submission-label"><a target="_blank"href="<?php echo admin_url('admin.php?page=erforms-labels'); ?>"><span class="dashicons dashicons-plus"></span><?php _e('Create New Label', 'erforms'); ?></a></div>
                </div>
            </div>
                <?php
            }
            ?>
        </div>
        <div id="erf_admin_submission_details">
            <?php erforms_admin_submission_table($submission); ?>
            <?php if (!empty($submission['plans'])) {
                    include('payment-part.php');
                  }
            ?>
            <div class="erf-payment-info">

            </div>
        </div>    

        <?php if (!empty($notes) && is_array($notes)) : ?>
            <div class="erf-notes">
                <div class="erf-notes-title"><?php _e('Note(s)', 'erforms'); ?></div>
                <div class="erf-notes-wrap">
                    <?php foreach ($notes as $note) : ?>
                        <div class="erf-note-row">
                            <p>
                                <?php echo $note['text']; ?>
                                <span class="erf-note-info"> 
                                    <?php _e('By', 'erforms'); ?>
                                    <?php echo $note['by']; ?> on
                                    <?php echo $note['time']; ?>
                                </span>
                                <?php if(!empty($note['recipients'])): ?>
                                    <span class="erf-note-info">
                                         <?php _e('Email sent to','erforms'); ?>  
                                         <?php echo $note['recipients']; ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>  
                    <?php endforeach; ?>
                </div>
            </div>    
        <?php endif; ?>


        <div id="erf_submission_add_note_dialog" class="erf_dialog" style="display: none;">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php _e('ADD  NOTE', 'erforms'); ?></h5>
                        <button type="button" class="close erf_close_dialog">
                            <span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="erf-form-name">
                            <?php _e('Note', 'erforms'); ?>
                            <textarea name="note_text" id="erf_submission_note_text" class="erf-input-field"></textarea>
                            <input type="hidden" name="add_note" />
                            <p class="description">
                                <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values. Email template can be changed from <a target="_blank" href="%s">here</a>.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form['id']),admin_url('admin.php?page=erforms-settings&tab=notifications&type=note_to_user')); ?>
                            </p>
                        </div>
                        <?php if(!empty($options['en_note_user_email'])): ?>

                            <div class="erf-row">
                                <div class="erf-control-label erf-has-child">
                                    <label><input type="checkbox" value='1' name="notify_user"/> <?php _e('Send Email', 'erforms'); ?></label>
                                </div>
                            </div>

                            <div class="erf-child-rows clearfix" style="display: none">
                                    <label><?php _e('Recipient(s)', 'erforms'); ?></label>
                                
                                    <?php 
                                            $recipient=''; 
                                            if(!empty($submission['user'])){
                                                $recipient= $submission['user']['user_email'];
                                            }
                                            else if(!empty($submission['primary_field_val'])){
                                                $recipient=$submission['primary_field_val'];
                                            }
                                    ?>
                                    <input class="erf-input-field" type="text" value="<?php echo esc_attr($recipient); ?>"  name="note_recipients" />
                                    <p class="decsription"><?php _e('Multiple emails can be given using comma(,) sepration.') ?></p> 
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><input type="checkbox" value='1' name="save_note" checked/> <?php _e('Save', 'erforms'); ?></label>
                                    <p class="description"><?php _e('Uncheck if you do not want to save this note. Useful if only email has to be sent.') ?></p>
                                </div>
                            </div>

                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="button button-primary" value="<?php _e('Save', 'erforms'); ?>" />
                    </div>
                </form>
            </div>
        </div>


        <?php if (is_array($submissions) && !empty($submissions)): ?>
            <hr class="erf-divider">
            <div class="erf-history-submissions">
                <h1><?php _e('Other Submissions', 'erforms'); ?></h1>
                <?php
                foreach ($submissions as $temp_sub) {
                    erforms_admin_submission_table($temp_sub);
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    jQuery(document).ready(function () {
        $ = jQuery;
        var sanitized_tags = <?php echo json_encode($sanitized_tags); ?>;
        if (!$.isEmptyObject(sanitized_tags)) {
            $.each(sanitized_tags, function (name, color) {
                $('.erf-label-' + name).attr('style', 'background-color:' + color);
            });
        }
        /*
         * Submission tag related
         */
        $(document).on('click', function (e) {
            if ($(e.target).closest(".erf-label-search").length === 0) {
                $('.erf-label-list').css('display','none');
            }else{
                $('.erf-label-list').css('display','block');
            }
        });
        $('.erf-label-search input').keyup(function(){
            $('.erf-label-list .erf-assign-label').hide();
            $('.erf-label-list .erf-assign-label:contains("'+$(this).val()+'")').show();
        });
        
        $('.erf-assign-label').on('click', function (e) {
            var data = {
                'action': 'erf_assign_label',
                'name': $(this).text(),
                'sub_id': <?php echo $submission['id']; ?>
            };
            $.post(ajaxurl, data, function (response) {
                if (response.success) {
                    location.reload();
                }else{
                    alert('<?php _e('Unable to connect to server.', 'erforms'); ?>');
                }
            });
        });
        
        $('.erf-remove-label').on('click', function (e) {
            var data = {
                'action': 'erf_remove_sub_label',
                'name': $(this).attr('data-val'),
                'sub_id': <?php echo $submission['id']; ?>
            };
            $.post(ajaxurl, data, function (response) {
                if (response.success) {
                    location.reload();
                }else{
                    alert('<?php _e('Unable to connect to server.', 'erforms'); ?>');
                }
            });
        });

    });
</script>
