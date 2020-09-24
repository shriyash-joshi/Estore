<form id="erforms-reports-table" method="get" action="">

    <input type="hidden" name="page" value="erforms-overview">

    <div class="tablenav top">
        <div class="tablenav-pages one-page">

            <span class="displaying-num">
                <?php echo count($form['reports']) > 0 ? count($form['reports']) . __(' Report(s)', 'erforms') : __('No Reports', 'erforms'); ?>
            </span>
            <br class="clear">
        </div>
        <br class="clear">
    </div>
    <div class="erf-add-block">
        <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=report" class="erf-add-new-block">
            <span class="dashicons dashicons-plus"></span>
        </a>
    </div>

    <table class="wp-list-table widefat fixed striped reports">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-report_name column-primary">
                    <?php _e('Name', 'erforms') ?>
                </th>

                <th scope="col" class="manage-column column-report_name column-primary">
                    <?php _e('Recurrence', 'erforms') ?>
                </th>
                <th scope="col" class="manage-column"><?php _e('Receipents', 'erforms') ?></th>
                <th scope="col" class="manage-column"><?php _e('Next Run', 'erforms') ?></th>
                <th scope="col" class="manage-column"><?php _e('Status', 'erforms') ?></th>
                <th scope="col" class="manage-column"><?php _e('Action', 'erforms') ?></th>

            </tr>
        </thead>

        <tbody id="the-list" data-wp-lists="list:form">
            <?php
            if (isset($form['reports']) && is_array($form['reports'])):
                $edit_nonce = wp_create_nonce('erf-report-edit-nonce');
                $delete_nonce = wp_create_nonce('erf-report-delete-nonce');
                $run_nonce = wp_create_nonce('erf-report-run-nonce');
                ?>
                <?php foreach ($form['reports'] as $key => $report): ?>
                    <tr>
                        <td><?php echo $report['name']; ?></td>
                        <td><?php echo ucwords($report['recurrence']); ?></td>
                        <td><?php echo ucwords($report['receipents']); ?></td>
                        <?php
                        $timestamp = wp_next_scheduled('erf_submission_report', array('form_id' => $form_id, 'index' => $key));
                        if (!empty($timestamp))
                            $next_time = get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'Y-m-d H:i:s') . ' (' . human_time_diff(time(), $timestamp) . ')';
                        ?>
                        <?php if (empty($timestamp)): ?>
                            <td><?php echo 'NA'; ?></td>
                        <?php else: ?>
                            <td><?php echo $next_time; ?></td>
                        <?php endif; ?>       

                        <td>
                            <?php if (empty($report['active'])) : ?>
                                <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=reports&index=<?php echo $key; ?>&status=1&nonce=<?php echo $edit_nonce; ?>" ><?php _e('Activate', 'erforms'); ?></a>
                            <?php else: ?>
                                <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=reports&index=<?php echo $key; ?>&status=0&nonce=<?php echo $edit_nonce; ?>" ><?php _e('Deactivate', 'erforms'); ?></a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a  href="javascript:void(0)" onclick="erf_report_test_run(<?php echo $key; ?>)" ><?php _e('Test Now', 'erforms'); ?></a>

                            <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=report&index=<?php echo $key; ?>&nonce=<?php echo $edit_nonce; ?>" ><?php _e('Edit', 'erforms'); ?></a>
                            <a href="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=reports&index=<?php echo $key; ?>&action=delete&nonce=<?php echo $delete_nonce; ?>" ><?php _e('Delete', 'erforms'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($form['reports']) == 0): ?>
                    <tr>
                        <td colspan="6"><?php _e('No report available.', 'erforms'); ?></td>
                    </tr>
                <?php endif; ?>        
            <?php endif; ?>

        </tbody>
    </table>
</form>

<form method="POST" action="?page=erforms-dashboard&form_id=<?php echo $form_id; ?>&tab=reports&nonce=<?php echo $run_nonce; ?>" id="erf_test_report">
    <input type="hidden" name="index" id="erf-report-index" />
    <input type="hidden" name="action" value="run"/>
</form>
<script>
    function erf_report_test_run(index) {
        jQuery("#erf-report-index").val(index);
        jQuery("#erf_test_report").submit();
    }
</script>