<div class="erf-container">
    <div class="erf-my-account clearfix">

        <div class="erf-my-account-nav">
            <ul class="clearfix">
                <li class="erf-my-account-navigation-link erf-my-account-navigation-link-active">
                    <a href="javascript:void(0);" data-tag="profile"><?php _e('Profile', 'erforms'); ?></a>
                </li>
                <li class="erf-my-account-navigation-link">
                    <a href="javascript:void(0);" data-tag="submissions"><?php _e('Submissions', 'erforms'); ?></a>
                </li>
                <li class="erf-my-account-navigation-link">
                    <a href="javascript:void(0);" data-tag="reset-password"><?php _e('Change Password','erforms'); ?></a>
                </li>
            </ul>
        </div>
        <div class="erf-my-account-content">
            <div class="erf-my-account-profile-tab" id="profile">
                <h3 class="erf-profile-welcome"><?php echo __('Welcome, ', 'erforms') . $current_user->display_name; ?></h3>
                <div class="erf-profile-details-wrap">
                    <div class="erf-profile-image">
                        <?php echo get_avatar($current_user->ID, 128, '', '', array('class' => 'erf-avatar')); ?>
                    </div>
                    <div class="erf-profile-details">

                        <!-- Personal Details -->
                        <div class="erf-profile-details-row erf-flex">
                            <div class="erf-profile-detail-title"><?php _e('Email', 'erforms'); ?> </div>
                            <div class="erf-profile-detail-content"><?php echo $current_user->user_email; ?></div>
                        </div>
                        <div class="erf-profile-details-row erf-flex">
                            <div class="erf-profile-detail-title"><?php _e('First Name', 'erforms'); ?> </div>
                            <div class="erf-profile-detail-content"><?php echo $current_user->first_name; ?></div>
                        </div>
                        <div class="erf-profile-details-row erf-flex">
                            <div class="erf-profile-detail-title"><?php _e('Last Name', 'erforms'); ?></div>
                            <div class="erf-profile-detail-content"><?php echo $current_user->last_name; ?></div>
                        </div>
                        <div class="erf-profile-details-row erf-flex">
                            <div class="erf-profile-detail-title"><?php _e('Bio', 'erforms'); ?> </div>
                            <div class="erf-profile-detail-content"><?php echo $current_user->description; ?></div>
                        </div>
                        <div class="erf-profile-details-row erf-flex">
                            <div class="erf-profile-detail-title"><?php _e('Nick Name', 'erforms'); ?> </div>
                            <div class="erf-profile-detail-content"><?php echo $current_user->nickname; ?></div>
                        </div>
                        
                        <?php do_action('erf_my_account_profile'); ?>
                        
                        <div class="erf-profile-logout">
                            <button onClick="location.href='<?php echo erforms_logout_url(); ?>'"><?php _e('Logout','erforms'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Submissions -->
            <div class="erf-my-account-profile-tab erf-hidden" id="submissions">
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
            
            <div class="erf-my-account-profile-tab erf-hidden" id="reset-password">
                
                <form class="erf-form erf-change-password" data-parsley-validate="" novalidate="true" autocomplete="off" method="POST">
                    <div class="erf-errors">
                        <div class="erf-error-row"></div>
                    </div>
                    <div class="erf-message"></div>
                    <div class="fb-text form-group">
                            <label for="password_current"><?php _e('Current password','erforms'); ?></label>
                            <input required="" type="password" class="form-control" name="password_current">
                    </div>
                    <?php $uid= uniqid(); ?>
                    <div class="fb-text form-group">
                            <label for="password_1"><?php _e('New password','erforms'); ?></label>
                            <input required="" minlength="5" type="password" class="form-control erf-password" name="password_1" id="erf_my_account_password_<?php echo $uid ?>">
                    </div>
                    <div class="fb-text form-group">
                            <label for="password_2"><?php _e('Confirm new password','erforms'); ?></label>
                            <input required="" type="password" class="form-control" data-parsley-confirm-password="erf_my_account_password_<?php echo $uid ?>" name="password_2">
                    </div>
                    <div class="fb-text form-group">
                        <input type="hidden" name="action" value="erf_change_password" />
                        <input type="hidden" name="erform_change_pwd_nonce" value="<?php echo wp_create_nonce('erform_change_pwd_nonce'); ?>" />
                        <button type="submit" name="change_password" value="<?php _e('Change Password','erforms'); ?>"><?php _e('Change Password','erforms'); ?></button>
                    </div>
                </form>

            </div>
            
        </div>
    </div>
</div>
