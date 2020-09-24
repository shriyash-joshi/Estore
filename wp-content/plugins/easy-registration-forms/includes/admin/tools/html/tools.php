<div class="erf-wrapper erforms-settings wrap">
    <div class="erf-page-title">
        <h1 class="wp-heading-inline">
            <?php _e('Tools', 'erforms'); ?>
        </h1>
        <div class="erf-page-menu">
            <ul class="erf-nav clearfix">

            </ul>        
        </div>
    </div>
    <div class="erforms-admin-content">
        <div class="erforms-import">
            <form method="post" enctype="multipart/form-data">
                <div class="group-title"><?php _e('Form Import','erforms'); ?> </div>
                <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Choose File', 'erforms'); ?><sup>*</sup></label>
                        </div>
                        <div class="erf-control">
                            <div class="erf-file-field">
                                <input required="" type="file" name="file" accept=".json" class="custom-file-input"/>
                            </div>
                            <input type="submit" class="button button-primary" value="<?php _e('Import', 'erforms'); ?>" name="save" />
                        </div>  
                </div>

                <input type="hidden" name="action" value="erf_import" />
                <div class="erf-error">
                    <ul>
                        <?php foreach($errors['import'] as $error): ?>
                                <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php if(!empty($success)): ?>
                <div class="erf-success-msg"><?php _e('Form successfully imported.') ?></div>
                <?php endif; ?>
            </form>
            <?php if(!empty($submissions_to_import)): ?>
                    <script>
                        var submissions= <?php echo json_encode($submissions_to_import); ?>;
                    </script>    
                    <table>
                        <tbody>
                            <?php foreach($submissions_to_import as $index=>$submission): ?>
                                <tr submission-index="<?php echo esc_attr($index); ?>">
                                    <?php foreach($submission['fields_data'] as $fd): ?>
                                            <?php if(is_array($fd['f_val'])): ?>
                                                <td><?php echo implode(',',$fd['f_val']).' ('.$fd['f_label'].') '; ?></td>
                                            <?php else: ?>
                                                <td><?php echo $fd['f_val'].' ('.$fd['f_label'].') '; ?></td>
                                            <?php endif; ?>    
                                    <?php endforeach; ?>
                                    <td class="status"><?php _e('Status','erforms'); ?></td>            
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <script>
                        jQuery(document).ready(function(){
                           $= jQuery;
                            var submission_count= submissions.length;
                            if(submission_count>0){
                                var batch= 10;
                                var batch_submissions;
                                var chunk_size = 10;
                                var submission_groups = submissions.map( function(e,i){ 
                                    return i%chunk_size===0 ? submissions.slice(i,i+chunk_size) : null; 
                                }).filter(function(e){ return e; });

                                var request_data= {
                                                   action: erf_import_submissions,
                                                   submissions: batch_submissions;
                                }
                                $.post(ajaxurl,function(response){
                                if(response.success)
                                {
                                    window.location= response.data.redirect;
                                }
                                else
                                {
                                    $("#erf_overview_add_form_response").html("Something went wrong.");
                                }
                                }).complete(function(){
                                    show_progress(false);
                                });
                           }
                        });
                    </script>
            <?php endif; ?>
        </div>

        <div class="erforms-export">
            <form method="post" action="<?php echo esc_attr(admin_url('admin-ajax.php?action=erf_export')); ?>">
                <div class="group-title"><?php _e('Form Export','erforms'); ?></div>

                <div class="erf-row">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Select Form', 'erforms'); ?><sup>*</sup></label>
                        </div>
                        <div class="erf-control">
                            <select required="" name="form"  class="erf-input-field">
                                <option value=""><?php _e('Select Form','erforms'); ?></option>
                                <?php foreach($forms as $form) : ?>
                                    <option value="<?php echo esc_attr($form['id']); ?>"><?php echo $form['title']; ?></option>
                                <?php endforeach; ?>   
                            </select>
                            <input type="submit" class="button button-primary" value="<?php _e('Export', 'erforms'); ?>" name="save" />
                        </div>  
                    </div>   
                </div>
                <div class="erf-error">
                    <ul>
                        <?php foreach($errors['export'] as $error): ?>
                                <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </form>
        </div>
        
        
    </div>
</div>    