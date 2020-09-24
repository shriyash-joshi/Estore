<?php 
    $form_id = isset($_GET['erform_id']) ? absint($_GET['erform_id']) : 0;
    $form= false;
    if(empty($form_id)){
        $forms = erforms()->form->get('',array('orderby'=>'ID','order'=>'DESC'));
        if(!empty($forms)){
            $form = erforms()->form->get_form($forms[0]->ID);
        }
    }
    else
        $form = erforms()->form->get_form($form_id);
        
?>
<div class="erf-wrapper wrap">
    <div id="erforms-submission" class="erforms-admin-wrap">
        <div class="erf-page-title">
            <h1 class="page-title">
                <?php _e('Submissions Overview', 'erforms'); ?>
                <a href="javascript:void(0)" id="erf_submissions_change_columns" class="alignright"><span class="dashicons dashicons-admin-generic"></span></a>
            </h1>
           
            
        </div>
        <?php
        $submisson_table = new ERForms_Submission_Table;
        $submisson_table->prepare_items();
        ?>

        <div class="erforms-admin-content">
            <div class="erf-feature-request">
                <?php _e('Feature not available ? Request new features <a target="_blank" href="http://www.easyregistrationforms.com/support/">here</a>.','erforms'); ?>
            </div>
            <form id="erforms_submission_table" method="get" action="<?php echo esc_attr(admin_url('admin.php?page=erforms-submission')); ?>">
                <input type="hidden" name="page" value="erforms-submissions" />
                <?php $submisson_table->views(); ?>
                <?php $submisson_table->display(); ?>
            </form>
        </div>
    </div>
</div>

<?php if(!empty($form)): ?>
    <div id="erf_change_subs_cols_dialog" class="erf_dialog" style="display: none;">
        <div class="modal-dialog">    
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php _e('Change Columns','erforms'); ?></h5>
                    <button type="button" class="close erf_close_dialog">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="">
                        <span><?php _e('Select Columns','erforms'); ?></span>
                        <select id="erf_sub_columns" multiple="" class="erf-input-field">
                            <?php if(!empty($form['enable_unique_id'])): ?>
                                <option <?php echo in_array('unique_id',$form['sub_columns']) ? 'selected' : ''; ?> value="unique_id"><?php _e('Unique ID','erforms'); ?></option>
                            <?php endif; ?>
                            <?php 
                                $fields= erforms_get_form_input_fields($form['id']); 
                                foreach($fields as $field):
                            ?>
                            <option <?php echo in_array($field['name'],$form['sub_columns']) ? 'selected' : ''; ?> value="<?php echo esc_attr($field['name']); ?>"><?php echo $field['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span><a href="javascript:void(0)" id="erf_clear_sub_columns" class="button"><?php _e('Clear','erforms'); ?></a></span>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" id="erf_change_sub_cols_btn" data-form-id="<?php echo esc_attr($form['id']); ?>" class="button button-primary"><?php _e('Save','erforms'); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Labels -->
<?php 
    $labels= erforms()->label->get_labels();
    if(!empty($labels)):
?>
<div class="erf-legends wrap">
    <div class="erf-legends-heading">
        <?php _e('Legends','erforms'); ?></div>
    <div class="erf-legends-wrap flex-s-e">
        <?php foreach($labels as $label): ?>
                <div class="erf-legend"><span style="background-color: #<?php echo $label['color']; ?>">&nbsp;</span><?php echo $label['name']; ?></div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php $sanitized_tags= erforms()->label->get_tags(true); ?>
<script>
    jQuery(document).ready(function(){
        $= jQuery;
        var sanitized_tags= <?php echo json_encode($sanitized_tags); ?>;
        if(!$.isEmptyObject(sanitized_tags)){
            $.each(sanitized_tags, function(name,color) {
               $('.erf-label-' + name).attr('style','background-color:' + color);
            });
        }
        
        $("#erforms_submission_table").submit(function(event){
            var text_helpers = erf_admin_data.text_helpers;
            var action = $(this).find('#bulk-action-selector-top').val();
            if(action==='delete'){
                var selected_sub = [];
                $(this).find(".erf-sub-cb:checked").each(function(){
                    selected_sub.push($(this).val());
                });
                if(selected_sub.length>0){
                    if(confirm(text_helpers.sub_del_prompt)){
                        return true;
                    }
                    event.preventDefault();
                }
            }
        });
    });
</script>    