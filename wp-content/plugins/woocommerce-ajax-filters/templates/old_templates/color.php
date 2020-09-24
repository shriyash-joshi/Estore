<?php
extract($berocket_query_var_title);
global $berocket_unique_value;
$berocket_unique_value++;
$random_name = strval($berocket_unique_value);
$hiden_value = false;
$child_parent = berocket_isset($child_parent);
$is_child_parent = $child_parent == 'child';
$is_child_parent_or = ( $child_parent == 'child' || $child_parent == 'parent' );
$child_parent_depth = berocket_isset($child_parent_depth, false, 0);
if ( $child_parent == 'parent' ) {
    $child_parent_depth = 0;
}
$item_i = 0;
if ( is_array(berocket_isset($terms)) ) {
    if( berocket_isset($color_image_checked) == 'brchecked_custom' ) {
        echo '<style>
        .berocket_aapf_widget .berocket_checkbox_color.brchecked_custom_'.$random_name.' input[type="checkbox"]:checked + label .berocket_color_span_block,
        .berocket_aapf_widget .berocket_checkbox_color.brchecked_custom_'.$random_name.' .berocket_checked .berocket_color_span_block{
            '.$color_image_checked_custom_css.'
        }
        </style>';
    }
    if( $color_image_block_size == 'hxpx_wxpx' ) {
        if( empty($color_image_block_size_height) ) {
            $color_image_block_size_height = 50;
        }
        if( empty($color_image_block_size_width) ) {
            $color_image_block_size_width = 50;
        }
        echo '<style>
        .berocket_aapf_widget .berocket_checkbox_color.berocket_color_with_value.hxpx_wxpx_'.$random_name.'.brchecked_default input[type="checkbox"]:checked + label .berocket_color_span_block,
        .berocket_aapf_widget .berocket_checkbox_color.berocket_color_with_value.hxpx_wxpx_'.$random_name.'.brchecked_default .berocket_checked .berocket_color_span_block{
            width: '.($color_image_block_size_width + 10).'px;
        }
        .berocket_aapf_widget .berocket_checkbox_color.hxpx_wxpx_'.$random_name.' label span.berocket_color_span_block, span.berocket_color_span_block{
            width: '.$color_image_block_size_width.'px;
        }
        .berocket_aapf_widget .berocket_checkbox_color.hxpx_wxpx_'.$random_name.' label span.berocket_color_span_block, span.berocket_color_span_block{
            height: '.$color_image_block_size_height.'px;
            line-height: '.$color_image_block_size_height.'px;
        }
        .berocket_aapf_widget .berocket_checkbox_color.hxpx_wxpx_'.$random_name.'{
            height: '.($color_image_block_size_height + 10).'px;
        }';
        if( ($color_image_block_size_ratio = $color_image_block_size_width / $color_image_block_size_height) < 1.3 ) {
            
            $color_image_block_size_margin = (15 / $color_image_block_size_ratio) + (1 - $color_image_block_size_ratio) * (30 + 5 / $color_image_block_size_ratio / $color_image_block_size_ratio);
            echo '.berocket_checkbox_color.hxpx_wxpx_'.$random_name.' .berocket_color_span_block .berocket_color_multiple {
                margin-left: -'.$color_image_block_size_margin.'%;
                margin-right: -'.$color_image_block_size_margin.'%;
            }';
        }
        echo '</style>';
    }
    foreach ( $terms as $term ) {
        $is_first = ($term->term_id == 'R__term_id__R');
        $term_taxonomy_echo = berocket_isset($term, 'wpml_taxonomy');
        if( empty($term_taxonomy_echo) ) {
            $term_taxonomy_echo = berocket_isset($term, 'taxonomy');
        }
        $item_i++;
        $variables_for_hooks = array(
            'type' => $type,
            'item_i' => $item_i,
            'is_child_parent' => $is_child_parent,
            'is_first' => $is_first,
        );
        $meta_class = apply_filters('berocket_widget_color_image_temp_meta_class_init', '&nbsp;', $term, $variables_for_hooks);
        $meta_after = '';
        if ( !$is_child_parent || !$is_first ) {
            if( $type == 'color' ) {
                $berocket_term = get_metadata( 'berocket_term', $term->term_id, 'color' );
                $berocket_term = br_get_value_from_array($berocket_term, 0, '');
                $meta_color = array($berocket_term);
            } else {
                $meta_color = get_metadata( 'berocket_term', $term->term_id, $type );
            }
        } else {
            $meta_color = 'R';
            if( $type == 'color' ) {
                $meta_color = array($meta_color);
            }
            ?><li class="berocket_child_parent_sample"><ul><?php
        }
        $meta_color = apply_filters('berocket_aapf_meta_color_values', $meta_color, $term, $variables_for_hooks);
        $meta_color_init = $meta_color;
        if( $type == 'color' ) {
            $meta_color = $meta_color[0];
            $meta_color = str_replace('#', '', $meta_color);
            $meta_color = 'background-color: #'.$meta_color.';';
            $meta_class = '<span class="berocket_color_span_absolute"><span>'.$meta_class.'</span></span>';
        } elseif( $type == 'image' ) {
            if ( ! empty($meta_color[0]) ) {
                if ( substr( $meta_color[0], 0, 3) == 'fa-' ) {
                    $meta_class = '<i class="fa '.$meta_color[0].'"></i>&nbsp;';
                    $meta_color = '';
                } else {
                    $meta_color = 'background: url('.$meta_color[0].') no-repeat scroll 50% 50% rgba(0, 0, 0, 0);background-size: cover;';
                    $meta_class = '&nbsp;';
                }
                $meta_after = '';
            } else {
                $meta_color = '';
                $meta_class = '';
            }
        }
        $data_jquery_arr = array(
            'term_slug' => urldecode(berocket_isset($term, 'slug')),
            'term_name' => ( ! empty($icon_before_value) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ) . htmlentities(berocket_isset($term, 'name'), ENT_QUOTES) . ( ! empty($icon_after_value) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' ),
            'filter_type' => berocket_isset($filter_type) ,
            'term_id' => berocket_isset($term, 'term_id'),
            'operator' => $operator,
            'term_ranges' => str_replace ( '*' , '-' , berocket_isset($term, 'term_id')),
            'taxonomy' => $term_taxonomy_echo,
            'term_count' => berocket_isset($term, 'count'),
        );
        $data_jquery = array();
        foreach($data_jquery_arr as $data_jquery_name => $data_jquery_string) {
            if( $data_jquery_string !== '' ) {
                $data_jquery[] = 'data-'.$data_jquery_name."='".$data_jquery_string."'";
            }
        }
        $data_jquery = implode(' ', $data_jquery);
        list($meta_class, $meta_after, $meta_color) = apply_filters('berocket_widget_color_image_temp_meta_ready', array($meta_class, $meta_after, $meta_color), $term, $meta_color_init, $variables_for_hooks);
        ?><li class="berocket_term_parent_<?php echo berocket_isset($term, 'parent'); 
            if ( $is_child_parent ) echo ' R__class__R';
            echo " brw-" . preg_replace( "#^(pa)?_#", "", $attribute ) . "-" . preg_replace( "#^(pa)?_#", "", berocket_isset($term, 'slug') );
            if( ! empty($hide_o_value) && berocket_isset($term, 'count') == 0 && ( !$is_child_parent || !$is_first ) ) {
                echo ' berocket_hide_o_value';
                $hiden_value = true;
            }
            if( ! empty($hide_sel_value) && br_is_term_selected( $term, true, $is_child_parent_or, $child_parent_depth ) != '' ) {
                echo ' berocket_hide_sel_value';
                $hiden_value = true;
            }
            if( ! empty($attribute_count) ) {
                if( $item_i > $attribute_count ) {
                    echo ' berocket_hide_attribute_count_value';
                    $hiden_value = true;
                } elseif( ! empty($hide_o_value) && berocket_isset($term, 'count') == 0 && ( !$is_child_parent || !$is_first ) ) {
                    echo ' berocket_hide_attribute_count_value';
                    $item_i--;
                    $hiden_value = true;
                }
            }
            if( $color_image_block_size == 'hxpx_wxpx' ) {
                echo ' hxpx_wxpx_'.$random_name;
            } else {
                echo ' '.$color_image_block_size;
            }
            if( berocket_isset($color_image_checked) == 'brchecked_custom' ) {
                echo ' brchecked_custom_'.$random_name;
            } else {
                echo ' '.(empty($color_image_checked) ? 'brchecked_default' : $color_image_checked);
            }
            ?> berocket_checkbox_color<?php echo ( ! empty($use_value_with_color) ? ' berocket_color_with_value' : ' berocket_color_without_value' );
            ?>"><span><input id='checkbox_<?php echo str_replace ( '*' , '-' , berocket_isset($term, 'term_id')), str_replace ( '*' , '-' , $term_taxonomy_echo) ?>_<?php echo berocket_isset($random_name);
                ?>' class="<?php echo ( empty($uo['class']['checkbox_radio']) ? '' : $uo['class']['checkbox_radio'] ) ?> checkbox_<?php echo str_replace ( '*' , '-' , berocket_isset($term, 'term_id')), str_replace ( '*' , '-' , $term_taxonomy_echo);
                ?>" type='<?php echo ( ! empty( $disable_multiple ) ? 'radio' : 'checkbox' )
                ?>' autocomplete="off" <?php 
                echo ( empty($uo['style']['checkbox_radio']) ? '' : 'style="' . $uo['style']['checkbox_radio'] . '"' );
                echo br_is_term_selected( $term, true, $is_child_parent_or, $child_parent_depth );
                echo ( ! empty( $disable_multiple ) ? ' name="radio_' . $term_taxonomy_echo . '_' . $x . '_' . $random_name . '"' : '' );
                echo ' '.$data_jquery;
                ?>/><label data-for='checkbox_<?php echo str_replace ( '*' , '-' , berocket_isset($term, 'term_id')), str_replace ( '*' , '-' , $term_taxonomy_echo);
                ?>' class="berocket_label_widgets<?php if( br_is_term_selected( $term, true, $is_child_parent_or, $child_parent_depth ) != '') echo ' berocket_checked'; ?>"><?php 
                    echo apply_filters( 'berocket_check_radio_color_filter_term_text', ( '<span class="'. apply_filters('berocket_widget_color_image_temp_span_class', 'berocket_color_span_block', array($meta_class, $meta_after, $meta_color), $term) . '"
                    style="' . $meta_color . '">' . $meta_class . '</span>' .
                    ( ! empty($use_value_with_color) ? '<span class="berocket_color_text">' . ( ! empty($icon_before_value) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ) . $term->name . ( ! empty($icon_after_value) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' ) . '</span>' : '' ) .
                    berocket_isset($meta_after) ), $term, $operator, FALSE );
                ?></label></span></li><?php
        if ( $is_child_parent && $is_first ) {
            ?></ul></li><?php
        }
    }
    if( $is_child_parent && is_array(berocket_isset($terms)) && count($terms) == 1 ) {
        if( BeRocket_AAPF_Widget_functions::is_parent_selected($attribute, $child_parent_depth - 1) ) {
            echo '<li>'.$child_parent_no_values.'</li>';
        } else {
            echo '<li>'.$child_parent_previous.'</li>';
        }
    } else {
        if( $child_parent_no_values ) {
            if ( ! $child_parent_depth ) $child_parent_depth = '0';
            ?><script>
            if ( typeof(child_parent_depth) == 'undefined' || child_parent_depth < <?php echo $child_parent_depth; ?> ) {
                child_parent_depth = <?php echo $child_parent_depth; ?>;
            }
            jQuery(document).ready(function() {
                if( child_parent_depth == <?php echo $child_parent_depth; ?> ) {
                    jQuery('.woocommerce-info').text('<?php echo $child_parent_no_values; ?>');
                }
            });
            </script><?php
        }
    }
    if( ! empty($attribute_count_show_hide) ) {
        if( $attribute_count_show_hide == 'hidden' ) {
            $hide_button_value = true;
        } elseif( $attribute_count_show_hide == 'visible' ) {
            $hide_button_value = false;
        }
    }
    if( empty($hide_button_value) ) { 
        ?><li class="berocket_widget_show_values"<?php if( !$hiden_value ) echo 'style="display: none;"' ?>><?php _e('Show value(s)', 'BeRocket_AJAX_domain') ?><span class="show_button fa"></span></li>
    <div style="clear: both;"></div><?php 
} }
