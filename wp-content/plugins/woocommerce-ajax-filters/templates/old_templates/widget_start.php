<?php
extract($berocket_query_var_title);
$child_parent = berocket_isset($child_parent);
$is_child = $child_parent == 'child';
$is_child_parent = in_array($child_parent, array('child', 'parent'));
?>
<div class="berocket_aapf_widget-wrapper <?php echo "brw-" . preg_replace( "#^(pa)?_#", "", $attribute ); ?> <?php if( $is_child_parent ) echo ' br_child_parent_wrapper'; if ( ! empty($description) ) echo ' berocket_widget_has_description'; ?>">
    <?php if( $is_child ) { ?>
    <div class="berocket_child_no_values">
        <?php echo berocket_isset($child_parent_no_values); ?>
    </div>
    <div class="berocket_child_previous">
        <?php echo berocket_isset($child_parent_previous); ?>
    </div>
    <div class="berocket_child_no_products">
        <?php echo berocket_isset($child_parent_no_products); ?>
    </div>
    <?php } ?>
    <div class="berocket_aapf_widget-title_div bapf_head<?php if ( ! empty($is_hide_mobile) ) echo ' berocket_aapf_hide_mobile'; if(!empty($widget_collapse_disable)) echo ' disable_collapse';?>">
        <?php if ( empty($hide_collapse_arrow) ) { ?>
            <span class="berocket_aapf_widget_show <?php echo ( br_widget_is_hide( $attribute, ! empty( $widget_is_hide ) ) ? 'show_button' : 'hide_button' ) ?> <?php echo ( ! empty($title) ? 'mobile_hide' : '' ) ?>"><i class="fa fa-angle-left "></i></span>
        <?php } ?>
        <?php if ( ! empty($description) ) { ?><span class="berocket_aapf_description"><i class="fa fa-info-circle"></i><div style="background-color:#<?php echo br_get_value_from_array($notuo, array('description', 'color')).'; border:1px solid #'.br_get_value_from_array($notuo, array('description_border', 'color')).';'?>"><h3 style="<?php echo br_get_value_from_array($uo, array('style', 'description_title'))?>"><?php echo $title; ?></h3><p style="<?php echo br_get_value_from_array($uo, array('style', 'description_text'))?>"><?php echo $description ?></p><div class="berocket_aapf_description_arrow"  style="background-color:#<?php echo br_get_value_from_array($notuo, array('description', 'color')).'; border:1px solid #'.br_get_value_from_array($notuo, array('description_border', 'color')).';'?>"></div></div></span><?php } ?>
        <?php if( ! empty($title) || ! empty($icon_before_title) || ! empty($icon_after_title) ) { ?><h3 class="widget-title berocket_aapf_widget-title" style="<?php echo ( empty($uo['style']['title']) ? '' : $uo['style']['title'] ) ?>"><span><?php echo ( ( ! empty($icon_before_title) ) ? ( ( substr( $icon_before_title, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_title.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_title.'" alt=""></i>' ) : '' ).( empty($title) ? '' : $title ).( ( ! empty($icon_after_title) ) ? ( ( substr( $icon_after_title, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_title.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_title.'" alt=""></i>' ) : '' ) ?></span></h3><?php } ?>
    </div>
    <ul class='berocket_aapf_widget bapf_body <?php echo ( br_widget_is_hide( $attribute, ! empty( $widget_is_hide ) ) ? 'berocket_style_none' : 'berocket_style_block' ) ?> <?php echo berocket_isset($product_count_style); ?> <?php if ( ! empty($is_hide_mobile) ) echo ' berocket_aapf_hide_mobile' ?> <?php echo berocket_isset($class) ?> <?php echo berocket_isset($css_class) ?> <?php echo ( ( berocket_isset($type) == 'tag_cloud' ) ? 'berocket_aapf_widget-tag_cloud' : '' ) ?>
        <?php echo apply_filters('berocket_widget_aapf_start_temp_class', ''); ?>'
        style='<?php echo berocket_isset($style) ?>' data-scroll_theme='<?php echo berocket_isset($scroll_theme) ?>'
        data-widget_id="<?php echo $widget_id; ?>"
        data-widget_id_number="<?php echo $widget_id_number; ?>"
        data-child_parent="<?php echo $child_parent; ?>"
        <?php if( $child_parent == 'child' ) echo 'data-child_parent_depth="'.berocket_isset($child_parent_depth).'"'; ?>
        data-attribute='<?php echo $attribute; ?>' data-type='<?php echo $type; ?>'
        data-count_show='<?php if( ! empty($show_product_count_per_attr) ) echo 'show' ?>'
        data-cat_limit='<?php echo berocket_isset($cat_value_limit);  ?>'>
