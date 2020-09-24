<?php if(current_user_can('administrator') && !empty($atts['layout_options'])) : ?> 
    <div class="erf_front_administration">
        <div class="erf_form_layout_admin_dialog" style="display: none;" title="<?php _e('Change Form Layout','erforms'); ?>">
            <form method="post">
                <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Layout','erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <select name="layout">
                                     <option <?php echo $layout=='one-column' ? 'selected': ''; ?> value="one-column"><?php _e('One Column','erforms'); ?></option>
                                     <option <?php echo $layout=='two-column' ? 'selected': ''; ?> value="two-column"><?php _e('Two Column','erforms'); ?></option>
                                </select>    
                            </div>  
                </div>

                <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Label Position','erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <select name="label_position">
                                    <option <?php echo $label_position=='top' ? 'selected': ''; ?> value="top"><?php _e('Top','erforms'); ?></option>
                                    <option <?php echo $label_position=='inline' ? 'selected': ''; ?> value="inline"><?php _e('Inline','erforms'); ?></option>
                                    <option <?php echo $label_position=='no-label' ? 'selected': ''; ?> value="no-label"><?php _e('No Label','erforms'); ?></option>
                                </select>    
                            </div>  
                </div>
                <input type="hidden" name="action" value="erf_change_form_layout"/>
                <input type="hidden" name="erform_id" value="<?php echo $id; ?>"/>
                <input type="hidden" name="change_form_layout_nonce" value="<?php echo wp_create_nonce('change_form_layout_nonce'); ?>"/>
            </form>    

        </div>
    </div>
<?php endif; ?>
    
