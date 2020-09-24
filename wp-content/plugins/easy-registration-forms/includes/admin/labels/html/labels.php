<div class="wrap erf-wrapper">
    <div class="erf-page-title">
        <h1 class="wp-heading-inline"><?php _e('Labels', 'erforms') ?></h1>
    </div>
    <div class="erforms-admin-content">
        <p class="description">
            Label is inspired from WordPress tagging system. You can add label(s) to any submission(s). 
            Think of Label as keywords used to indicate submission status at any given point.

        </p>
        <div class="erf-new-label">
        <h3><?php _e('New Label','erforms'); ?></h3>
        <form method="POST" class="erf-new-form flex-s-e">
            <div class="erf-label-name erf-label-wrap">
                <div class="label"><?php _e('Label Name','erforms'); ?></div>
                <div class="label-input"><input class="erf-input-field" required="" type="text" name="label_name" value="<?php echo isset($_POST['label_name']) ? esc_attr(wp_unslash($_POST['label_name'])) : '' ?>" /></div>
            </div>
            
            <div class="erf-label-description erf-label-wrap">
                <div class="label"><?php _e('Description','erforms'); ?></div>
                <div class="label-input"><input class="erf-input-field" type="text" name="label_desc" value="<?php echo isset($_POST['label_desc']) ? esc_attr(wp_unslash($_POST['label_desc'])) : '' ?>" /></div>
            </div>
            
            <div class="erf-label-color erf-label-wrap">
                <div class="label"><?php _e('Color','erforms'); ?></div>
                <div class="label-input"><input required="" class="jscolor erf-input-field" type="text" value="<?php echo isset($_POST['label_color']) ? esc_attr(wp_unslash($_POST['label_color'])) : erforms_rand_hex_color(); ?>"  name="label_color" /></div>
            </div>
            
            <div class="erf-label-button">
                <div><input class="button button-primary" type="submit" value="<?php _e('Save','erforms'); ?>" /></div>
            </div>   
            <input type="hidden" name="erf_save_label" />
            <div class="erf-error">
                <ul>
                    <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </form>
    </div>
    
        <div class="erf-labels">
        <table class="wp-list-table widefat fixed">
            <thead>
                <tr>
                    <th><?php _e('Label Name','erforms'); ?></th>
                    <th><?php _e('Description','erforms'); ?></th>
                    <th><?php _e('Total Submissions','erforms'); ?></th>
                    <th><?php _e('Actions','erforms'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($labels as $label):?>
                    <tr class="erf-label-row" id="static_row_<?php echo $label['id']; ?>" data-label-id="<?php echo esc_attr($label['id']); ?>">
                        <td>
                            <span class="label" style="<?php echo !empty($label['color']) ? 'background-color:#'.$label['color'] : ''; ?>"><?php echo $label['name']; ?></span>   
                        </td>
                        <td>
                            <span class="desc"><?php echo $label['desc']; ?></span>
                        </td>
                        <td><?php echo $label['count'].' Submissions'; ?></td>
                        <td>
                            <input class="erf_edit_btn button button-primary" type="button" value="<?php _e('Edit','erfroms'); ?>" />
                            <input  class="erf_delete_btn button button-danger" type="button" value="<?php _e('Delete','erfroms'); ?>" />
                        </td>
                    </tr>
                    <tr style="display:none" class="erf-label-edit" id="form_row_<?php echo $label['id']; ?>" data-label-id="<?php echo esc_attr($label['id']); ?>">
                            <td>
                                <div class="erf-label-name erf-label-wrap">
                                    <div class="label"><?php _e('Label Name','erforms'); ?></div>
                                    <div class="label-input"><input id="label" type="text" class="erf-input-field" value="<?php echo esc_attr($label['name']); ?>" name="label_name" /></div>
                                </div> 
                            </td> 

                            <td>
                                <div class="erf-label-description erf-label-wrap">
                                    <div class="label"><?php _e('Description','erforms'); ?></div>
                                    <div class="label-input"><input id="desc" type="text" class="erf-input-field" value="<?php echo esc_attr($label['desc']); ?>" name="label_desc" /></div>
                                </div>
                            </td>  

                            <td>
                                <div class="erf-label-color erf-label-wrap">
                                    <div class="label"><?php _e('Color','erforms'); ?></div>
                                    <div class="label-input"><input id="color" class="jscolor erf-input-field"  value="<?php echo esc_attr($label['color']); ?>" type="text" name="label_color" /></div>
                                </div>
                            </td>

                            <td>
                                <div class="erf-label-button">
                                    <div>
                                        <input class="erf_save_btn button button-primary" type="button" value="<?php _e('Save','erforms'); ?>" />
                                        <input class="erf_cancel_btn button" type="button" value="<?php _e('Cancel','erforms'); ?>" />
                                    </div>
                                </div>
                            </td>
                    </tr>
                    <tr class="erf-label-error-row" id="form_error_row_<?php echo $label['id']; ?>">
                        <td colspan="4"><div class="erf-error"></div></td>
                    </tr>
                <?php endforeach;?>
                <?php if(empty($labels)): ?> 
                    <tr>
                        <td colspan="4"><?php _e('There is no label created.','erforms'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
</div>

<script>
    jQuery(document).ready(function(){
        $= jQuery;
        // Show edit button
        $('.erf_edit_btn').click(function(){
            var label_id= $(this).closest('tr').data('label-id');
            var form_row= $('#form_row_' + label_id);
            form_row.slideToggle();
        });

        // Cancel update form
        $('.erf_cancel_btn').click(function(){
            var label_id= $(this).closest('tr').data('label-id');
            var form_row= $('#form_row_' + label_id);
            form_row.slideUp();
        });
       
        // Delete label
        $('.erf_delete_btn').click(function(){
            var label_id= $(this).closest('tr').data('label-id');
            var static_row= $('#static_row_' + label_id);
            var form_row= $('#form_row_' + label_id);
            var error_row= $('#form_error_row_' + label_id);
            $(this).addClass('erf-progress');

            var data = {
                         'action': 'erf_delete_label',
                         'id': label_id
                       };
             jQuery.post(ajaxurl, data, function(response) {
                 var response_data= response.data;
                 if(response.success){
                    static_row.remove();
                    form_row.remove();
                    error_row.remove();
                    $(this).removeClass('erf-progress');
                 }
                 else
                 {
                     error_row.find('.erf-error').html(response_data.msg);
                     error_row.slideDown();
                     $(this).removeClass('erf-progress');
                 }
             }).fail(function (xhr,textStatus,e) {
                 $(this).removeClass('erf-progress');
             });

        });
       
        // Updating labels
        $('.erf_save_btn').click(function(){
            var label_id= $(this).closest('tr').data('label-id');
            var form_row= $('#form_row_' + label_id);
            var error_row= $('#form_error_row_' + label_id);
            var static_row= $('#static_row_' + label_id);

            var label= $.trim(form_row.find('#label').val());
            var color= $.trim(form_row.find('#color').val());
            var desc= $.trim(form_row.find('#desc').val());
            
            if(label=="" || color==""){
                form_row.addClass('erf-label-form-error');
                return;
            }
            form_row.removeClass('erf-label-form-error');
            error_row.slideUp();
            var target= $(this)
            target.addClass('erf-progress');
            var data = {
                         'action': 'erf_save_label',
                         'label': label,
                         'color': color,
                         'desc' : desc,
                         'id': label_id
                       };


             $.post(ajaxurl, data, function(response) {
                 var response_data= response.data;
                 if(response.success){
                     // Place new values
                     form_row.slideUp();
                     static_row.find('.label').html(label);
                     static_row.find('.label').css('background-color','#' + color);
                     static_row.find('.desc').html(desc);
                     target.removeClass('erf-progress');
                 }
                 else
                 {
                     error_row.find('.erf-error').html(response_data.msg);
                     error_row.slideDown();
                     target.removeClass('erf-progress');
                 }
             }).fail(function (xhr,textStatus,e) {
                 target.removeClass('erf-progress');
             });
        });
    });
</script>    