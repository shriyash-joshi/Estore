<div class="erf-row">
    <div class="erf-control-label">
        <label><?php _e('Configure Offline', 'erforms'); ?></label>
    </div>
    <div class="erf-control">
        <input class="erf_toggle" type="checkbox" data-has-child="1" name="payment_methods[]" value="offline" <?php echo in_array('offline', $options['payment_methods']) ? 'checked' : ''; ?>/>
        <label></label>
        <p class="description"><?php _e('It allow merchants to track payments made via cash, checks, bank transfers, at the desk, postal orders, or any other means besides online payment methods such as cards, PayPal, etc. Once you have received the payment, you will have to manually record it.', 'erforms') ?></p>
    </div>  
</div>