<?php extract($berocket_query_var_title); ?>
<div class="berocket_aapf_widget<?php if( ! empty($custom_css) ) echo ' '.esc_html($custom_css); ?>">
    <input value="<?php echo berocket_isset($title) ?>" class="berocket_aapf_widget_update_button<?php if ( ! empty($is_hide_mobile) ) echo ' berocket_aapf_hide_mobile' ?>" type="button" />
</div>
