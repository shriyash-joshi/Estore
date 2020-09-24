<?php 
        $notifications= erforms_system_notifications($options);
        $type= !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : '';
        if(!in_array($type, array_keys($notifications))){
            $type= '';
        }
?>

<div class="erf-notifications" style="<?php echo empty($type) ? '' : 'display:none'; ?>">
    <table class="wp-list-table widefat fixed striped">
        <thead>
                <tr>
                    <th><?php _e('Status','erforms'); ?></th>
                    <th><?php _e('Email','erforms'); ?></th>
                    <th><?php _e('Subject','erforms'); ?></th>
                    <th><?php _e('Action','erforms'); ?></th>
                </tr>
        </thead>
        <tbody>
            <?php foreach($notifications as $t=>$label):
                    $notification= erforms_system_notification_by_type($t,$options);
            ?>
                    <tr>
                        <td class="notification-status <?php echo empty($notification['enabled']) ? 'disabled' : 'enabled' ?>">
                            <?php echo empty($notification['enabled']) ? '<span title="Notification Inactive" class="dashicons dashicons-no"></span>' : '<span title="Notification Active" class="dashicons dashicons-yes"></span>' ?>
                            &nbsp;</td> 
                        <td><?php echo $label. '<div class="erf-notifiaction-desc"><span class="dashicons dashicons-editor-help"></span><span class="erf-notification-help">' . $notification['help'] . '</span></div>' ; ?></td>
                        <td><?php echo $notification['subject']; ?></td>
                        <td><a class="button alignright" href="?page=erforms-settings&tab=notifications&type=<?php echo $t; ?>"><?php _e('Manage','erforms'); ?></a></td>
                    </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div style="<?php echo empty($type) ? 'display:none': ''; ?>">
<?php 
    include('notification-type.php');
?>
</div>
