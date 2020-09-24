<?php 
extract($berocket_query_var_title);
global $berocket_unique_value;
$berocket_unique_value++;
$unique = strval($berocket_unique_value);
$is_child_parent = ( isset($child_parent) && $child_parent == 'child' );
$is_child_parent_or = ( isset($child_parent) && ( $child_parent == 'child' || $child_parent == 'parent' ) );
if ( $is_child_parent ) {
    ?>
<li class="berocket_child_parent_sample"><ul>
<li class='<?php echo berocket_isset($main_class) ?>'>
    <span class='left'>
        <?php echo ( ! empty($icon_before_value) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ) . $text_before_price ?>
                   <input disabled class="berocket_slider_start_val" type='text'  id='R__slug__R_<?php echo $unique; ?>_1'
                                                 value='<?php echo berocket_isset($slider_value1) ?>'
                                                 style="<?php echo br_get_value_from_array($uo, array('style', 'slider_input'))?>"
        /><?php echo berocket_isset($text_after_price) . ( ! empty($icon_after_value) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' ) ?>
    </span>
    <span class='right'>
        <?php echo ( ! empty($icon_before_value) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ) . berocket_isset($text_before_price) ?>
                  <input disabled class="berocket_slider_end_val" type='text'  id='R__slug__R_<?php echo $unique; ?>_2'
                                                 value='<?php echo $slider_value2 ?>'
                                                 style="<?php echo br_get_value_from_array($uo, array('style', 'slider_input'))?>"
        /><?php echo berocket_isset($text_after_price) . ( ! empty($icon_after_value) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' ) ?>
    </span>
    <div class='slide <?php echo br_get_value_from_array($uo, array('class', 'slider')) ?>'>
        <div class='<?php echo berocket_isset($slider_class) ?>' data-taxonomy='<?php echo berocket_isset($filter_slider_id) ?>'
            data-min='R__min__R' data-max='R__max__R'
            data-value1='R__value1__R' data-value2='R__value2__R'
            data-value_1='R__value1__R' data-value_2='R__value2__R'
            data-term_slug='<?php echo urldecode($term->slug) ?>' data-filter_type='<?php echo berocket_isset($filter_type) ?>'
            disabled="disabled"
            data-step='<?php echo berocket_isset($step) ?>' data-all_terms_name='R__allterm__R'
            data-all_terms_slug='R__sallterm__R'
            data-child_parent="<?php if ( $is_child_parent_or ) echo $child_parent ;?>"
            data-child_parent_depth="<?php echo berocket_isset($child_parent_depth) ;?>"
            data-fields_1='R__slug__R_<?php echo $unique; ?>_1'
            data-fields_2='R__slug__R_<?php echo $unique; ?>_2'
            data-number_style="<?php if( ! empty($number_style) ) echo json_encode($number_style); ?>"></div>
    </div>
</li>
</ul></li>
<?php 
while ( isset( $all_terms_name[0] ) && $all_terms_name[0] == 'R__name__R' ) {
    array_splice( $all_terms_name, 0, 1 );
    $max--;
    $slider_value1--;
    $slider_value2--;
}
}
if( !$is_child_parent || count( $all_terms_name ) > 0 ) {
    if( is_array($all_terms_slug) ) {
        foreach($all_terms_slug as &$all_term_slug) {
            $all_term_slug = str_replace("'", '&#39;', $all_term_slug);
        }
    }
?>
<li class='<?php echo berocket_isset($main_class) ?>'>
    <span class='left'>
        <?php echo ( ! empty($icon_before_value) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ) . berocket_isset($text_before_price) ?>
                   <input <?php if( empty($enable_slider_inputs) ) echo 'disabled '; ?>class="berocket_slider_start_val" type='text' id='text_<?php echo berocket_isset($filter_slider_id) . $unique ?>_1'
                                                 value='<?php echo berocket_isset($slider_value1) ?>'
                                                 style="<?php echo br_get_value_from_array($uo, array('style', 'slider_input'))?>"
        /><?php echo berocket_isset($text_after_price) . ( ! empty($icon_after_value) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' ) ?>
    </span>
    <span class='right'>
        <?php echo ( ! empty($icon_before_value) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ) . berocket_isset($text_before_price) ?>
                  <input <?php if( empty($enable_slider_inputs) ) echo 'disabled '; ?>class="berocket_slider_end_val" type='text' id='text_<?php echo berocket_isset($filter_slider_id) . $unique ?>_2'
                                                 value='<?php echo berocket_isset($slider_value2) ?>'
                                                 style="<?php echo br_get_value_from_array($uo, array('style', 'slider_input'));?>"
        /><?php echo berocket_isset($text_after_price) . ( ! empty($icon_after_value) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' ) ?>
    </span>
    <div disabled class='slide <?php echo br_get_value_from_array($uo, array('class', 'slider'))?>'>
        <div class='<?php echo berocket_isset($slider_class) ?>' data-taxonomy='<?php echo berocket_isset($filter_slider_id) ?>'
            data-min='<?php echo berocket_isset($min) ?>' data-max='<?php echo berocket_isset($max) ?>'
            data-value1='<?php echo berocket_isset($slider_value1) ?>' data-value2='<?php echo berocket_isset($slider_value2) ?>'
            data-value_1='<?php echo berocket_isset($slider_value1) ?>' data-value_2='<?php echo berocket_isset($slider_value2) ?>'
            data-term_slug='' data-filter_type='<?php echo berocket_isset($filter_type) ?>'
            data-step='<?php echo berocket_isset($step) ?>' data-all_terms_name='<?php echo json_encode(berocket_isset($all_terms_name)); ?>'
            data-all_terms_slug='<?php echo json_encode(berocket_isset($all_terms_slug)); ?>'
            data-child_parent="<?php if ( $is_child_parent_or ) echo $child_parent ;?>"
            data-disabled=true
            data-child_parent_depth="<?php echo berocket_isset($child_parent_depth) ;?>"
            data-fields_1='text_<?php echo berocket_isset($filter_slider_id) . $unique ?>_1'
            data-fields_2='text_<?php echo berocket_isset($filter_slider_id) . $unique ?>_2'
            data-number_style='<?php if( ! empty($number_style) ) echo json_encode($number_style); ?>'></div>
    </div>
</li>
<?php } ?>
