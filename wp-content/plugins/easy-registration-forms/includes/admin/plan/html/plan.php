<div class="erf-wrapper erforms-settings wrap erf-wrapper-bg">
    <div class="erf-page-title">
        <h1><?php _e('Plan', 'erforms'); ?></h1>
        <div class="erf-page-menu">
            <ul class="erf-nav clearfix">

            </ul>        
        </div>
    </div>
    <div class="erforms-new-plan erforms-admin-content">
        <form action="" method="post">
            <fieldset class="erf-plan-wrap">
                <div class="erf-row">
                    <div class="erf-control-label">
                        <label><?php _e('Pricing Type', 'erforms'); ?></label>
                    </div>
                    <div class="erf-control">
                        <select name="type" id="erf_plan_type" class="erf-input-field">
                            <option <?php echo $plan['type'] == 'product' ? 'selected' : ''; ?> value="product"><?php _e('Product', 'erforms') ?></option>
                            <option <?php echo $plan['type'] == 'user' ? 'selected' : ''; ?> value="user"><?php _e('User Defined', 'erforms') ?></option>
                        </select>    
                        <p class="description erf-plan-type" id="erf_plan_type_product"><?php _e('Fixed price single plan', 'erforms'); ?></p>
                        <p class="description erf-plan-type" id="erf_plan_type_user"><?php _e('User has to enter custom price calue. Useful to accept Donations.', 'erforms') ?></p>
                    </div>  
                </div>    

                <div class="erf-row">
                    <div class="erf-control-label">
                        <label><?php _e('Name', 'erforms'); ?></label>
                    </div>
                    <div class="erf-control">
                        <input required="" type="text" class="erf-input-field" name="name" value="<?php echo esc_attr($plan['name']); ?>" />
                        <p class="description"><?php _e('This name will be used for internal purpose. It will not be visible on front end.') ?></p>
                    </div>  
                </div>
                
                <div id="erf_plan_product" class="erf-plan">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Price', 'erforms'); ?><?php echo erforms_currency_symbol($options['currency']); ?></label>
                        </div>
                        <div class="erf-control">
                             <input type="text" class="erf-input-number erf-input-field" name="price" value="<?php echo esc_attr($plan['price']); ?>" />
                        </div>
                    </div>
                </div>
                
                <div class="erf-row">
                    <div class="erf-control-label">
                        <label><?php _e('User Role Assignment', 'erforms'); ?></label>
                    </div>
                    <div class="erf-control erf-has-child">
                         <input type="checkbox" name="en_role" value="1" <?php echo empty($plan['en_role']) ? '' : 'checked'; ?> />
                         <p class="description"><?php _e('Assign user role if the plan is selected during submission.','erforms'); ?></p>
                    </div>
                </div>
                <div class="erf-child-rows" style="<?php echo !empty($plan['en_role']) ? '' : 'display:none'; ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Role(s)', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                             <select name="roles[]" multiple class="erf-input-field">
                                <?php $roles = erforms_wp_roles(); ?> 
                                <?php foreach ($roles as $key => $role): ?>
                                    <option <?php echo in_array($key,$plan['roles']) ? 'selected' : ''; ?> value="<?php echo $key; ?>"><?php echo $role['name']; ?></option>              
                                <?php endforeach; ?>
                            </select> 
                            <p class="description"><?php _e('Select roles to assign.','erforms'); ?></p>
                        </div>
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Remove Previous Role', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                             <input type="checkbox" name="del_old_role" value="1" <?php echo empty($plan['del_old_role']) ? '' : 'checked'; ?> />
                             <p class="description"><?php _e('Remove default role configured in the form.','erforms'); ?></p>
                        </div>
                    </div>
                </div>    
                
                <div class="erf-row">
                    <div class="erf-control-label">
                        <label><?php _e('Description', 'erforms'); ?></label>
                    </div>
                    <div class="erf-control">
                        <?php
                            $editor_id = 'description';
                            $settings = array('editor_class' => 'erf-editor erf-editor-small');
                            wp_editor($plan['description'], $editor_id, $settings);
                        ?>
                        <p class="description"><?php _e('Above text will appear just after the plan option. Purpose is to provide more details about the plan.','erforms'); ?></p>
                    </div>  
                </div>

            </fieldset>

            <p class="submit">
                <input type="hidden" name="erf_save_plan" />
                <input type="hidden" name="id" value="<?php echo esc_attr($plan['id']); ?>"  /> 
                <input type="submit" class="button button-primary" value="<?php _e('Save', 'erforms'); ?>" name="save" />
                <input type="submit" class="button button-primary" value="<?php _e('Save & Close', 'erforms'); ?>" name="save_close" />
            </p>
        </form>    
    </div>
</div>  


<script>

    function erf_delete_price_row(obj) {
        $ = jQuery;
        var outer = $(obj).closest('ul');
        if (outer.find('li').length == 1)
            return;
        var element = $(obj).closest('li');
        element.remove();
    }

    function erf_input_number_format() {
        //var cleave = new Cleave('.erf-input-number', {numeral: true});
    }


    jQuery(document).ready(function() {
        $= jQuery;

        $('#erf_plan_type').change(function () {
            var selectVal = $(this).val();
            $('#erf_plan_type_' + selectVal).slideDown();
            $('.erf-plan-type').not('#erf_plan_type_' + selectVal).hide();
           
            var childElement = $('#erf_plan_' + selectVal);
            $('.erf-plan').not('#erf_plan_' + selectVal).hide();
            if (childElement.length > 0) {
                childElement.slideDown();
            }

        });
        erf_input_number_format();
        $('#erf_plan_type').trigger('change');
    });
</script>