<div class="erf-wrapper wrap">
    <div class="erf-page-title">
        <h1 class="wp-heading-inline"><?php _e('ERF Help Tags', 'erforms') ?></h1>
    </div>
    <div class="wrap erf-help erf-tags erforms-admin-content">
        <?php if (!empty($form)): ?>
            <div class="erf-table-title">
                <h3 class="wp-heading-inline erf-tags-title"><?php _e('Form Field tags', 'erforms') ?></h3>
            </div>

            <table class="wp-list-table widefat fixed striped erf-tags-table">
                <thead>
                    <tr>
                        <th class="manage-column"><?php _e('Tag', 'erforms') ?></th>
                        <th class="manage-column column-title"><?php _e('Type', 'erforms') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $non_input_fields = erforms_non_input_fields();
                    array_push($non_input_fields, 'password');
                    $field_count=0;
                    foreach ($form['fields'] as $field) {
                        ?>
                        <?php if (!in_array($field['type'], $non_input_fields) ) { $field_count++; ?>
                            <tr>
                                <td><code>{{<?php echo $field['label']; ?>}}</code></td>
                                <td><?php echo $field['type']; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    
                    <?php if(empty($field_count)): ?>        
                        <tr>
                            <td colspan="2"><?php _e('No form fields available.','erforms'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div class="erf-table-title">
            <h3 class="wp-heading-inline erf-tags-title"><?php _e('Global Shortcodes', 'erforms') ?></h3>
        </div>
        
        <table class="wp-list-table widefat fixed striped erf-tags-table">
            <thead>
                <tr>
                    <th class="manage-column"><?php _e('Tag', 'erforms') ?></th>
                    <th class="manage-column column-title"><?php _e('Description', 'erforms') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[erforms id="X"]</code></td>
                    <td><?php _e("It renders form in any page/post, 'X' denotes the ID of a form.",'erforms') ?></td>
                </tr>

                <tr>
                    <td><code>[erforms_my_account]</code></td>
                    <td><?php _e('Shows listing of all the submissions completed by logged in user. Allows to edit or delete the submission from frontend. Also show profile information and password change options.','erforms') ?> </td>
                </tr>
                
                <tr>
                    <td><code>[erforms_my_submissions]</code></td>
                    <td><?php _e('Shows listing of all the submissions completed by logged in user. Also allows to edit or delete the submission from frontend.','erforms') ?> </td>
                </tr>

                <tr>
                    <td><code>[erforms_login]</code></td>
                    <td><?php _e('Shows login form with Lost Password link.','erforms') ?></td>
                </tr>
                
                <tr>
                    <td><code>[erforms_user_meta key="meta_key_name"]</code></td>
                    <td><?php _e("Shows user meta value for the user. <br>Example: [erforms_user_meta key='last_name']", 'erforms') ?> </td>
                </tr>
                
                <tr>
                    <td><code>[erforms_submission_counter form="form_id" skip="0" skip_before="mm/dd/yyyy"]</code></td>
                    <td><?php _e("Displays submission counter for a form. `skip` and `skip_before` are optionals.", 'erforms') ?> </td>
                </tr>
                
            </tbody>
        </table>
        
        
        <div class="erf-table-title">
            <h3 class="wp-heading-inline erf-tags-title"><?php _e('System tags', 'erforms') ?></h3>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="manage-column"><?php _e('Tag', 'erforms') ?></th>
                    <th class="manage-column column-title"><?php _e('Description', 'erforms') ?></th>
                </tr>
            </thead>    

            <tbody>
                <tr>
                    <td><code>{{registration_data}}</code></td>
                    <td><?php _e("Shows submission with all the fields and payment details(if applicable).", 'erforms') ?></td>
                </tr>
                
                <?php if(!empty($form) && $form['type']=='reg'): ?>
                <tr>
                    <td><code>{{verification_link}}</code></td>
                    <td><?php printf(__("Shows user verification link. Should be used within <a href='%s'>User Verification</a> template.", 'erforms'),admin_url('admin.php?page=erforms-dashboard&tab=notifications&form_id='.$form['id'])); ?></td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <td><code>{{unique_id}}</code></td>
                    <td><?php _e("Submission Unique ID(if enabled in the form configuration).", 'erforms') ?></td>
                </tr>

                <tr>
                    <td><code>{{payment_details}}</code></td>
                    <td><?php _e("Submission payment details including Amount,Payment status,Payment method,Payment invoice.", 'erforms') ?></td>
                </tr>

                <tr>
                    <td><code>{{plan_amount}}</code></td>
                    <td><?php _e("Payment amount associated with the submission.", 'erforms') ?></td>
                </tr>

                <tr>
                    <td><code>{{plan_payment_status}}</code></td>
                    <td><?php _e("Payment status associated with the submission.", 'erforms') ?></td>
                </tr>

                <tr>
                    <td><code>{{plan_invoice}}</code></td>
                    <td><?php _e("Payment invoice associated with the submission.", 'erforms') ?></td>
                </tr>

                <tr>
                    <td><code>{{plan_payment_method}}</code></td>
                    <td><?php _e("Payment method associated with the submission.", 'erforms') ?></td>
                </tr>
                
                <tr>
                    <td><code>{{user_email}}</code></td>
                    <td><?php _e("User's email address.", 'erforms') ?></td>
                </tr>

                <tr>
                    <td><code>{{user_login}}</code></td>
                    <td><?php _e("User's login name.", 'erforms') ?> </td>
                </tr>

                <tr>
                    <td><code>{{user_display_name}}</code></td>
                    <td><?php _e("User's display name.", 'erforms') ?> </td>
                </tr>
                
                <tr>
                    <td><code>{{display_name}}</code></td>
                    <td><?php _e("User's display name. Otherwise, returns Primary Contact name from the configuration.", 'erforms') ?> </td>
                </tr>
                
                <tr>
                    <td><code>{{user_nicename}}</code></td>
                    <td><?php _e("User's nicename.", 'erforms') ?> </td>
                </tr>
                
                <tr>
                    <td><code>{{user_firstname}}</code></td>
                    <td><?php _e("User's firstname.", 'erforms') ?> </td>
                </tr>

                <tr>
                    <td><code>{{user_lastname}}</code></td>
                    <td><?php _e("User's lastname.", 'erforms') ?> </td>
                </tr>

                <tr>
                    <td><code>{{user_id}}</code></td>
                    <td><?php _e("User's ID.", 'erforms') ?> </td>
                </tr>
                
            </tbody>
        </table>
    </div>
</div>      