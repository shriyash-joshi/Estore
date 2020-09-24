<?php extract($berocket_query_var_title); ?>
<div class="<?php echo ( ! empty($is_hooked) ? 'berocket_aapf_selected_area_hook' : 'berocket_aapf_widget-wrapper' ); ?> berocket_aapf_selected_area_block<?php if( ! empty($custom_css) ) echo ' '.esc_html($custom_css); ?>">
    <?php if ( empty($is_hooked) ) { ?>
    <div class="berocket_aapf_widget-title_div<?php if ( ! empty($is_hide_mobile) ) echo ' berocket_aapf_hide_mobile' ?>">
        <?php if ( empty($hide_selected_arrow) ) { ?>
            <span class="berocket_aapf_widget_show <?php echo ( empty($selected_is_hide) ? 'hide_button' : 'show_button' ) ?> <?php echo ( ! empty($title) ? 'mobile_hide' : '' ) ?>"><i class="fa fa-angle-left "></i></span>
        <?php } ?>
        <h3 class="widget-title berocket_aapf_widget-title" style="<?php echo br_get_value_from_array($uo, array('style', 'title')) ?>"><span><?php echo berocket_isset($title) ?></span></h3>
    </div>
    <?php } ?>
    <div class="berocket_aapf_widget berocket_aapf_widget_selected_area <?php echo ( ! empty($selected_area_show) ? 'berocket_aapf_widget_selected_area_text' : 'berocket_aapf_widget_selected_area_hide' ); ?><?php if ( ! empty($is_hide_mobile) && ! empty($is_hooked) ) echo ' berocket_aapf_hide_mobile' ?>" style="<?php echo ( ! empty($selected_is_hide) ? 'display:none;' : 'display:block;' ).berocket_isset($style); ?>"></div>
</div>
