<?php
$submission_id= !empty($_GET['submission_id']) ? absint($_GET['submission_id']) : false;
if(empty($submission_id))
    return;

$submission= erforms()->submission->get_submission($submission_id);
?>
<?php if(empty($submission['attachments'])) : ?>
        <div><?php echo __('No Attachments available','erforms'); ?></div>
<?php else:?>
        <?php foreach($submission['attachments'] as $attachment): ?>
                <?php 
                        if(wp_attachment_is_image($attachment['f_val'])): 
                            $img_url = esc_url(erforms_get_attachment_url($attachment['f_val'],$submission['id']));
                ?>
                            <a target="_blank" href="<?php echo $img_url; ?>">
                                <img src="<?php echo $img_url; ?>"  />
                            </a>
                <?php else: ?>
                            <?php
                                   $attachment_post = get_post($attachment['f_val']);
                                   if(!empty($attachment_post)):
                                   $title= get_the_title($attachment['f_val']);
                            ?>
                                    <a target="_blank" href="<?php echo erforms_get_attachment_url($attachment['f_val'],$submission['id']); ?>">
                                        <?php echo $title; ?>
                                        <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/file-attachment.png'; ?>"/>
                                    </a>
                            <?php else: ?>
                                    <?php _e('Unable to fetch file.File might have deleted from from WordPress media section.','erforms'); ?>
                            <?php endif;?>
                            
                <?php endif; ?>
        <?php endforeach;?>
<?php endif; ?>

    