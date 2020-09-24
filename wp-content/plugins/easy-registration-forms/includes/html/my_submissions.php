<!-- Copy of the My Account template -->
<div class="erf-container">
    <div class="erf-my-account clearfix">
        <div class="erf-my-account-content">

            <!-- My Submissions -->
            <div class="erf-my-account-profile-tab">
                <div class="erf-my-account-submissions">
                    <div class="erf-my-account-titles">   
                        <div class="erf-my-account-title erf-my-account-col-sr"><?php _e('#', 'erforms'); ?></div>
                        <div class="erf-my-account-title erf-my-account-col-form-name"><?php _e('Form', 'erforms'); ?></div>
                        <div class="erf-my-account-title erf-my-account-col-date"><?php _e('Date', 'erforms'); ?></div>
                        <div class="erf-my-account-title erf-my-account-col-edit"><?php _e('Action(s)', 'erforms'); ?></div> 
                    </div>    
                    <div class="erf-my-account-details-wrap">
                        <?php
                        $sub_id = isset($_GET['sub_id']) ? absint($_GET['sub_id']) : 0;
                        if (!empty($submissions)) {
                            foreach ($submissions as $index => $submission) :
                                $form = erforms()->form->get_form($submission['form_id']);
                                ?>
                                <div class="erf-my-account-details">

                                    <div class="erf-my-account-detail erf-my-account-col-sr">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div class="erf-my-account-detail erf-my-account-col-form-name" data-submission-id="<?php echo esc_attr($submission['id']); ?>">
                                        <a data-form-id="<?php echo $form['id']; ?>" data-submission-id="<?php echo esc_attr($submission['id']); ?>" class="erf-load-submission-row" href="javascript:void(0)"><?php echo $form['title']; ?></a>
                                        <div  style="display:none" class="erf-modal">
                                            <div class="erf-submission-info">
                                                <div class="erf-modal-header">
                                                    <button type="button" class="erf-modal-close">X</button>
                                                    <?php echo $form['title']; ?>
                                                    <button class="erf-print-submission"><i class="fa fa-print"></i></button>
                                                </div>
                                                <div class="erf-modal-body">
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($sub_id) && $sub_id == $submission['id']): ?>
                                            <div id="erf_submission_<?php echo $submission['id'] ?>" class="erf-edit-submission">
                                                <?php echo do_shortcode('[erforms layout_options="0" id="' . $submission['form_id'] . '"]') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="erf-my-account-detail erf-my-account-col-date">
                                        <?php echo $submission['created_date']; ?>
                                    </div>
                                    <div class="erf-my-account-detail erf-my-account-col-edit">
                                        <a class="erf-edit-submission-row <?php echo !erforms_edit_permission($form,$submission) ? 'erf-link-disabled' : ''; ?>" data-form-id="<?php echo esc_attr($form['id']); ?>" data-submission-id="<?php echo esc_attr($submission['id']); ?>" href="javascript:void(0)"><?php _e('Edit', 'erforms');  ?></a>
                                        <a class="erf-delete-submission-row <?php echo !erforms_delete_permission($form,$submission) ? 'erf-link-disabled' : ''; ?>" data-form-id="<?php echo esc_attr($form['id']); ?>" data-submission-id="<?php echo esc_attr($submission['id']); ?>" href="javascript:void(0)"><?php _e('Delete', 'erforms');  ?></a>
                                         
                                            
                                        <div class="erf-modal" style="display: none;">
                                            <div class="erf-modal-header">
                                                <button type="button" class="erf-modal-close">X</button>
                                                <?php _e('Edit Submission','erforms') ?>
                                            </div>
                                            <div class="erf-modal-body erf-edit-submission-form">
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                        } else { ?>
                            <div class="erf-message-info">
                                <?php _e('No submission yet.', 'erforms'); ?>	
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="erf-account-pagination clearfix">
                    <?php if ($show_prev): ?>
                    <a href="<?php echo home_url(add_query_arg(array('erf_paged' => $paged - 1), $wp->request)); ?>" class="erf-pagination erf-prev"><?php _e('Prev', 'erforms'); ?></a>
                    <?php endif; ?>

                    <?php if ($show_next): ?>
                        <a href="<?php echo home_url(add_query_arg(array('erf_paged' => $paged + 1), $wp->request)); ?>" class="erf-pagination erf-next"><?php _e('Next', 'erforms'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Pagination -->
        </div>
    </div>
</div>
