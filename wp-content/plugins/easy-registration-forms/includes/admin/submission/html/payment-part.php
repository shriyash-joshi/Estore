<div class="erf-payment_info">
    <table class="erf-submission-table striped wp-list-table fixed widefat">
        <tbody>
            <tr>
                <th colspan="2" class="erf-submission-title">
                    <?php _e('Payment via','erforms'); ?> : 
                    <?php echo erforms_payment_method_title($submission['payment_method']); ?>
                </th>
            </tr>
            <tr>
                <th><?php _e('Amount', 'erforms'); ?></th>
                <td><?php echo erforms_currency_symbol($submission['currency'], false) . $submission['amount']; ?></td>
            </tr>
           <tr>
                <th><?php _e('Payment Status', 'erforms'); ?></th>
                <td><a href="javascript:void(0)" id="erf_payment_change_status" class="button"><?php echo ucwords($submission['payment_status']); ?></a></td>
            </tr>
    
            <tr>
                <th><?php _e('Payment Invoice', 'erforms'); ?></th>
                <td><?php echo $submission['payment_invoice']; ?></td>
            </tr>
        
            <?php if (!empty($submission['plans'])) : ?>
                    <?php $plan_names= array();
                          foreach($submission['plans'] as $row){
                              $plan= erforms()->plan->get_plan($row['id']);
                              if(!empty($plan)){
                                  array_push($plan_names, '<a target="_blank" href="?page=erforms-plan&plan_id='.$plan['id'].'">'.$plan['name'].'</a>');
                              }
                              else
                              { // Plan deleted
                                  $plan= $row['plan'];
                                  array_push($plan_names,$plan['name'].'('.__('Plan does not exist','erforms').')');
                              }
                          } 
                    ?>
                    <tr>
                        <th><?php _e('Plan Name', 'erforms'); ?></th>
                        <td><?php echo implode(', ', $plan_names); ?></td>
                    </tr>
            <?php endif; ?>
           
            <?php if(!empty($submission['payment_logs']) && is_array($submission['payment_logs'])): ?>
                <tr>
                    <th><?php _e('Payment Log (For Developers)', 'erforms'); ?></th>
                    <td>
                        <div class="erf-toggle-log"><?php _e('Show/Hide Log','erforms'); ?></div>
                        <div id="erf_payment_log" style="display:none">
                            <?php echo json_encode($submission['payment_logs']['log']); ?>
                            <p class="description"><?php _e('Above data is for debugging purpose. You can easily read above log using online JSON beautify tools.','erforms'); ?></p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Change Payment Status Dialog -->
<div id="erf_payment_status_dialog" class="erf_dialog" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php _e('Change Payment status','erforms'); ?></h5>
            <button type="button" class="close erf_close_dialog">
                <span>×</span>
            </button>
        </div>
        <form method="POST">    
            <div class="modal-body">
                    <div class="erf-row">
                        <div class="erf-control-label erf-payment-status">
                            <label><?php _e('Status', 'erforms'); ?></label>
                            <select name="payment_status" id="erf_payment_status" class="erf-input-field">
                                <?php $status_list= erforms_status_options(); ?>
                                <?php foreach($status_list as $status) : ?>
                                        <option <?php echo $submission['payment_status']==$status ? 'selected' : '' ?> value="<?php echo esc_attr($status); ?>"><?php echo ucwords($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div> 
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label erf-control">
                            <label><input type="checkbox" id="erf_notify_user" name="notify_user" value="1" /> <?php _e('Notify User', 'erforms'); ?></label>
                            <p class="description"><?php printf(__('If Payment status is changed, email will be sent to the User as per the corresponding status(If enabled). You can manage email(s) from <a target="_blank" href="%s">here</a>.','erforms'),'?page=erforms-settings&tab=notifications') ?></p>
                        </div>  
                    </div>
                    <input type="hidden" name="change_payment_status"/>
            </div>
            <div class="modal-footer">
                <input type="submit" class="button button-danger erf-confirm-btn" value="<?php _e('Confirm','erforms'); ?>" />
                <input type="button" class="button button-primary erf-close-btn erf_close_dialog" value="<?php _e('Close','erforms'); ?>" />
            </div>
        </form>    
    </div>
</div>  

<script>
jQuery(document).ready(function(){
   $= jQuery;
   $('#erf_payment_status').change(function(){
       var selectedVal= $(this).val();
       if(selectedVal=='completed' || selectedVal=='pending'){
           $('#erf_notify_user').closest('.erf-row').slideDown();
       }
       else
       {
           $('#erf_notify_user').prop('checked',false);
           $('#erf_notify_user').closest('.erf-row').slideUp();
       }
   });
   
   $(".erf-toggle-log").click(function(){
       $("#erf_payment_log").slideToggle();
   });
});
</script>
