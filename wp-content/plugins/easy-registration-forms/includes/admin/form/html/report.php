<?php
wp_enqueue_style('timepicker');
wp_enqueue_script('timepicker');

$index = isset($_REQUEST['index']) ? absint($_REQUEST['index']) : 0;
$report = null;
if (isset($form['reports'][$index]) && isset($_REQUEST['index'])) {
    $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'erf-report-edit-nonce')) {
        die('Invalid security token, Please go tp Reports page and try again.');
    }
    $report = $form['reports'][$index];
}
?>
<div class="erf-form-report-wrapper">
    <form method="POST" action="<?php echo admin_url('?page=erforms-dashboard&form_id=' . $form_id . '&tab=reports'); ?>">
        <fieldset>
            <h1><?php _e('Add/Edit Report','erforms'); ?></h1>
            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Name', 'erforms'); ?><sup>*</sup></label>
                </div>
                <div class="erf-control">
                    <input type="text" class="erf-input-field" name="name" required value="<?php echo isset($report['name']) ? esc_attr($report['name']) : ''; ?>"/>
                    <p class="description"><?php _e('Report Name.', 'erforms'); ?></p>
                </div>  
            </div>

            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Description', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <textarea class="erf-input-field" name="description"><?php echo isset($report['description']) ? esc_textarea($report['description']) : ''; ?></textarea>
                    <p class="description"><?php _e('Report Description.', 'erforms'); ?></p>
                </div>  
            </div>

            <!-- Sortable Field -->
            <div class="erforms_sortable_fields-wrap">
                <ul id="erforms_sortable_fields">
                    <?php
                    $fields = erforms_get_report_fields($form_id);
                    $form_fields = erforms()->form->get_fields_dropdown($form_id);
                    $form_field_names = array_keys($form_fields);
                    $df_sub_fields = erforms_get_default_submission_fields();
                    ?>
                    <?php if (!empty($report) && !empty($report['fields'])): ?>
                        <?php foreach ($report['fields'] as $name => $field): ?>
                            <?php
                            if (isset($form_fields[$name])) {
                                unset($form_fields[$name]);
                            }

                            if (!in_array($name, $form_field_names) && !in_array($name, $df_sub_fields))
                                continue; // Making sure to exclude deleted fields
                            ?>
                            <li class="ui-state-default" id="<?php echo esc_attr($name); ?>">
                                <div class="group-wrap">
                                    <div class="erf-report-field-label"><div class="field-arrow"><span class="dashicons dashicons-move"></span></div> <?php echo $field['label']; ?></div>

                                    <div class="erf-report-field-options" style="display:none">
                                        <div class="erf-row">
                                            <div class="erf-control-label">
                                                <label><?php _e('Alias', 'erforms'); ?></label>
                                            </div>
                                            <div class="erf-control">
                                                <input type="text" class="erf-input-field" name="<?php echo $name; ?>_alias" value="<?php echo esc_attr($field['alias']); ?>"/>
                                            </div>  
                                        </div>

                                        <div class="erf-row">
                                            <div class="erf-control-label">
                                                <label><?php _e('Include in report', 'erforms'); ?></label>
                                            </div>
                                            <div class="erf-control">
                                                <input  type="checkbox" name="<?php echo $name; ?>_included" value="1" <?php echo empty($field['included']) ? '' : 'checked'; ?>>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </li>  

                        <?php endforeach; ?>
                    <?php else : ?> 
                        <?php foreach ($fields as $name => $label) : ?>
                            <li class="ui-state-default" id="<?php echo $name; ?>">
                                <div class="group-wrap">
                                    <div class="erf-report-field-label"><div class="field-arrow"><span class="dashicons dashicons-move"></span></div> <?php echo $label; ?></div>

                                    <div class="erf-report-field-options" style="display:none">
                                        <div class="erf-row">
                                            <div class="erf-control-label">
                                                <label><?php _e('Alias', 'erforms'); ?></label>
                                            </div>
                                            <div class="erf-control">
                                                <input type="text" class="erf-input-field" name="<?php echo $name; ?>_alias" value="<?php echo esc_attr($label); ?>"/>
                                            </div>  
                                        </div>


                                        <div class="erf-row">
                                            <div class="erf-control-label">
                                                <label><?php _e('Include in report', 'erforms'); ?></label>
                                            </div>
                                            <div class="erf-control">
                                                <input type="checkbox" name="<?php echo $name; ?>_included" value="1" checked>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </li>

                        <?php endforeach; ?>      
                    <?php endif; ?>

                    <?php if (!empty($report)): ?>             
                        <?php foreach ($form_fields as $fn => $fl): //Printing new fields from dropdown ?>  
                            <li class="ui-state-default" id="<?php echo $fn; ?>">
                                <div class="group-wrap">
                                    <div class="erf-report-field-label"><div class="field-arrow"><span class="dashicons dashicons-move"></span></div> <?php echo $fl; ?></div>

                                    <div class="erf-report-field-options" style="display:none">
                                        <div class="erf-row">
                                            <div class="erf-control-label">
                                                <label><?php _e('Alias', 'erforms'); ?></label>
                                            </div>
                                            <div class="erf-control">
                                                <input type="text" class="erf-input-field" name="<?php echo $fn; ?>_alias" value="<?php echo esc_attr($fl); ?>"/>
                                            </div>  
                                        </div>

                                        <div class="erf-row">
                                            <div class="erf-control-label">
                                                <label><?php _e('Include in report', 'erforms'); ?></label>
                                            </div>
                                            <div class="erf-control">
                                                <input  type="checkbox" name="<?php echo $fn; ?>_included" value="1">
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </li> 
                        <?php endforeach; ?>  
                    <?php endif; ?>               

                </ul>
            </div>
            <!-- Sortable Fields area ends here -->

            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Receipents', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <input type="text" class="erf-input-field" name="receipents" value="<?php echo isset($report['receipents']) ? $report['receipents'] : ''; ?>"/>
                    <p class="description"><?php echo __('Email where you want to receive the report. Multiple emails can be given using comma(,) sepration. In case this value is empty, system will send the notification to site admin ', 'erforms') . '(' . get_option('admin_email') . ')'; ?></p>
                </div>  
            </div>

            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Email Subject', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <input type="text" class="erf-input-field" required name="email_subject" value="<?php echo isset($report['email_subject']) ? esc_attr($report['email_subject']) : $form['title'] . ' Report'; ?>"/>
                    <p class="description"><?php echo __('Subject of the email.', 'erforms'); ?></p>
                </div>  
            </div>

            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Email Message', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <?php
                    $email_message = isset($report['email_message']) ? $report['email_message'] : 'Please find the attached report.';
                    echo wp_editor($email_message, 'email_message');
                    ?>
                </div>  
            </div>

            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Status', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <select name="active" class="erf-input-field">
                        <option <?php echo isset($report['active']) && $report['active'] == '1' ? 'selected' : ''; ?> value="1"><?php _e('Active', 'erforms'); ?></option>
                        <option <?php echo isset($report['active']) && $report['active'] == '0' ? 'selected' : ''; ?> value=""><?php _e('Deactive', 'erforms'); ?></option>
                    </select>
                    <p class="description"><?php _e('Activate/Deactivate the report. Report will not be sent for Deactivated status.', 'erforms'); ?></p>
                </div>  
            </div>

            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Start Date', 'erforms'); ?><sup>*</sup></label>
                </div>
                <div class="erf-control">
                    <input required="" id="erf_report_start_date" value="<?php echo isset($report['start_date']) ? esc_attr($report['start_date']) : ''; ?>" name="start_date" type="text" class="erf-input-field">
                    <p class="description"><?php _e('The first Date that you want the event to occur.','erforms'); ?></p>
                </div>  
            </div>
            
            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Start Time', 'erforms'); ?><sup>*</sup></label>
                </div>
                <div class="erf-control">
                    <input value="<?php echo isset($report['time']) ? esc_attr($report['time']) : ''; ?>" id="erforms_time" required="" name="time" type="text" class="time ui-timepicker-input erf-input-field" autocomplete="off">
                    <p class="description"><?php _e('The first Time that you want the event to occur.','erforms'); ?></p>
                </div>  
            </div>
            
            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Recurrence', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <select name="recurrence" class="erf-input-field">
                        <option <?php echo (isset($report['recurrence']) && $report['recurrence'] == 'twicedaily') ? 'selected' : ''; ?> value="twicedaily"><?php _e('Twice Daily', 'erforms'); ?></option>
                        <option <?php echo (isset($report['recurrence']) && $report['recurrence'] == 'daily') ? 'selected' : ''; ?> value="daily"><?php _e('Daily', 'erforms'); ?></option>
                        <option <?php echo (isset($report['recurrence']) && $report['recurrence'] == 'weekly') ? 'selected' : ''; ?> value="weekly"><?php _e('Weekly', 'erforms'); ?></option>
                        <option <?php echo (isset($report['recurrence']) && $report['recurrence'] == 'monthly') ? 'selected' : ''; ?> value="monthly"><?php _e('Monthly', 'erforms'); ?></option>
                    </select>
                    <p class="description"><?php _e('How often the report should send.', 'erforms'); ?></p>
                </div>  
            </div>
            <?php
            $field_names = array();
            if (!empty($report) && !empty($report['fields'])) {
                $field_names = array_keys($report['fields']);
            }
            // erforms_debug($field_names);
            foreach ($field_names as $name_index => $fn) {
                if (!in_array($fn, $form_field_names) && !in_array($fn, $df_sub_fields)) {
                    unset($field_names[$name_index]);
                }
            }
            // erforms_debug($field_names); die;
            ?>
            <input type="hidden" name="field_names" id="erf_field_names" value="<?php echo empty($field_names) ? '' : implode(',', $field_names); ?>" />
            <input type="hidden" name="created" value="<?php echo isset($report['created']) ? $report['created'] : '' ?>" />
            <input type="hidden" name="index" value="<?php echo empty($report) ? -1 : $index; ?>" />
            <input type="hidden" name="erf_save_report" value="1" />
            <input type="hidden" id="erf_report_offset" name="time_offset" />
            
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php _e('Save', 'erforms'); ?>" name="save" /> 
            </p>
        </fieldset>
    </form>


<?php wp_enqueue_script('jquery-ui-sortable'); ?>
    <script>
        jQuery(document).ready(function () {
            $ = jQuery;

            $('#erforms_sortable_fields').sortable({
                stop: function (e, ui) {
                    var fieldName = $(this).sortable('toArray', {attribute: 'id'});
                    console.log(fieldName);
                    $('#erf_field_names').val(fieldName);
                }
            });
            $("#erforms_sortable_fields").disableSelection();

            // Timepicker
            $('#erforms_time').timepicker();

            $('.erf-report-field-label').click(function () {
                var optionsContainer = $(this).next('.erf-report-field-options');
                optionsContainer.slideToggle();
            });
            
            $('#erf_report_start_date').datepicker({ dateFormat: 'yy-mm-dd', minDate: new Date()-1});
            $('#erf_report_offset').val(new Date().getTimezoneOffset()/60);
        });
    </script>
</div>