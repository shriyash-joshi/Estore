<?php
$attributes        = br_aapf_get_attributes();
$categories        = BeRocket_AAPF_Widget_functions::get_product_categories( '' );
$categories        = BeRocket_AAPF_Widget_functions::set_terms_on_same_level( $categories );
$tags              = get_terms( 'product_tag' );
$custom_taxonomies = get_object_taxonomies( 'product' );
$custom_taxonomies = array_combine($custom_taxonomies, $custom_taxonomies);
if( empty($instance['filter_type']) ) {
    $instance['filter_type'] = 'attribute';
}
if( empty($instance['attribute']) ) {
    $instance['attribute'] = 'price';
}
if( ! empty($instance['version']) ) {
    $popup_text = '<h2>' . __('ATTENTION! This filter was created with new styles and settings.', 'BeRocket_AJAX_domain') . '</h2>'
    . '<p>' . __('Plugin do not have feature to move it back to old(DEPRECATED) version automatically.', 'BeRocket_AJAX_domain') . '</p>'
    . '<p>' . __('If you enabled old(DEPRECATED) filters addon because you have some issues with new version, then please ', 'BeRocket_AJAX_domain')
    . '<a target="_blank" href="https://berocket.com/contact?step=issue&plugin=1">' . __('CONTACT US', 'BeRocket_AJAX_domain') . '.</a>' . '</p>'
    . '<p>' . __('You will need to re-create the filter while moving from new to old version.', 'BeRocket_AJAX_domain') . '</p>'
    . '<p><strong>' . __('Do you want to edit anyway?', 'BeRocket_AJAX_domain') . '</strong></p>';
    BeRocket_popup_display::add_popup(
        array(
            'yes_no_buttons' => array(
                'show'          => true,
                'yes_text'      => __('Yes, edit filter', 'BeRocket_AJAX_domain'),
                'no_text'       => __('No, return back', 'BeRocket_AJAX_domain'),
                'location'      => 'popup',
                'yes_func'      => '',
                'no_func'       => 'window.history.back();setTimeout(function(){if (!braapf_has_history){window.close();}}, 200);',
            ),
            'no_x_button'   => true,
            'close_with'    => array(
                'yes_button',
                'no_button', '', '', '', '', '', '', ''
            ),
            'width'         => '600px',
            'height'        => '400px'
        ), 
        $popup_text, 
        array('page_open' => array('type' => 'page_open'))
    );
    ?>
    <script>
    var braapf_has_history = false;
    jQuery(window).on('beforeunload', function(){
        braapf_has_history = true;
    });
    </script>
    <?php
}
?>
<div class="berocket_aapf_widget_content">
    <div class="widget-liquid-right tab-item  current">
    <div class="berocketwizard_widget_type">
        <label class="br_admin_center"><?php _e('Widget Type', 'BeRocket_AJAX_domain') ?></label>
        <select id="<?php echo 'widget_type'; ?>" name="<?php echo $post_name.'[widget_type]'; ?>" class="berocket_aapf_widget_admin_widget_type_select br_select_menu_left">
            <?php
            $widget_type_array = apply_filters( 'berocket_widget_widget_type_array', apply_filters( 'berocket_aapf_display_filter_type_list', array(
                'filter' => __('Filter', 'BeRocket_AJAX_domain'),
            ) ) );
            $set_widget_type = false;
            if( ! array_key_exists($instance['widget_type'], $widget_type_array) ) {
                $set_widget_type = true;
            }
            foreach($widget_type_array as $widget_type_id => $widget_type_name) {
                if( $set_widget_type ) {
                    $instance['widget_type'] = $widget_type_id;
                    $set_widget_type = false;
                }
                echo '<option value="'.$widget_type_id.'"'.($widget_type_id == $instance['widget_type'] ? ' selected' : '').'>'.$widget_type_name.'</option>';
            }
            ?>
        </select>
    </div>
    <?php if( empty($instance['filter_type']) ) $instance['filter_type'] = ''; ?>
    <div class="berocket_aapf_admin_filter_widget_content" <?php if ( $instance['widget_type'] == 'update_button' or $instance['widget_type'] == 'reset_button' or $instance['widget_type'] == 'selected_area' or $instance['widget_type'] == 'search_box'  ) echo 'style="display: none;"'; ?>>
        <div class="berocketwizard_attribute">
            <div class="br_admin_half_size_left">
                <label class="br_admin_center"><?php _e('Filter By', 'BeRocket_AJAX_domain') ?></label>
                <select id="<?php echo 'filter_type'; ?>" name="<?php echo $post_name.'[filter_type]'; ?>" class="berocket_aapf_widget_admin_filter_type_select br_select_menu_left">
                    <?php
                    $filter_type_array = array(
                        'attribute' => array(
                            'name' => __('Attribute', 'BeRocket_AJAX_domain'),
                            'sameas' => 'attribute',
                        ),
                        'tag' => array(
                            'name' => __('Tag', 'BeRocket_AJAX_domain'),
                            'sameas' => 'tag',
                        ),
                        'all_product_cat' => array(
                            'name' => __('Product Category', 'BeRocket_AJAX_domain'),
                            'sameas' => 'custom_taxonomy',
                            'attribute' => 'product_cat',
                        ),
                    );
                    if ( function_exists('wc_get_product_visibility_term_ids') ) {
                        $filter_type_array['_rating'] = array(
                            'name' => __('Rating', 'BeRocket_AJAX_domain'),
                            'sameas' => '_rating',
                        );
                    }
                    $filter_type_array = apply_filters('berocket_filter_filter_type_array', $filter_type_array, $instance);
                    if( ! array_key_exists($instance['filter_type'], $filter_type_array) ) {
                        foreach($filter_type_array as $filter_type_key => $filter_type_val) {
                            $instance['filter_type'] = $filter_type_key;
                            break;
                        }
                    }
                    if( isset($filter_type_array['price']) ) unset($filter_type_array['price']);
                    foreach($filter_type_array as $filter_type_key => $filter_type_val) {
                        echo '<option';
                        foreach($filter_type_val as $data_key => $data_val) {
                            if( ! empty($data_val) ) {
                                echo ' data-'.$data_key.'="'.( is_array($data_val) ? json_encode($data_val) : $data_val).'"';
                            }
                        }
                        echo ' value="'.$filter_type_key.'"'.($instance['filter_type'] == $filter_type_key ? ' selected' : '').'>'.$filter_type_val['name'].'</option>';
                        if( $instance['filter_type'] == $filter_type_key ) {
                            $sameas = $filter_type_val;
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="br_admin_half_size_right berocket_aapf_widget_admin_filter_type_ berocket_aapf_widget_admin_filter_type_attribute" <?php if ( $instance['filter_type'] and $instance['filter_type'] != 'attribute') echo 'style="display: none;"'; ?>>
                <label class="br_admin_center"><?php _e('Attribute', 'BeRocket_AJAX_domain') ?></label>
                <select id="<?php echo 'attribute'; ?>" name="<?php echo $post_name.'[attribute]'; ?>" class="berocket_aapf_widget_admin_filter_type_attribute_select br_select_menu_right">
                    <option <?php if ( $instance['attribute'] == 'price' ) echo 'selected'; ?> value="price"><?php _e('Price', 'BeRocket_AJAX_domain') ?></option>
                    <?php foreach ( $attributes as $k => $v ) { ?>
                        <option <?php if ( $instance['attribute'] == $k ) echo 'selected'; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="br_admin_half_size_right berocket_aapf_widget_admin_filter_type_ berocket_aapf_widget_admin_filter_type_custom_taxonomy" <?php if ( $instance['filter_type'] != 'custom_taxonomy') echo 'style="display: none;"'; ?>>
                <label class="br_admin_center"><?php _e('Custom Taxonomies', 'BeRocket_AJAX_domain') ?></label>
                <select id="<?php echo 'custom_taxonomy'; ?>" name="<?php echo $post_name.'[custom_taxonomy]'; ?>" class="berocket_aapf_widget_admin_filter_type_custom_taxonomy_select br_select_menu_right">
                    <?php foreach( $custom_taxonomies as $k => $v ){ ?>
                        <option <?php if ( $instance['custom_taxonomy'] == $k ) echo 'selected'; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                    <?php } ?>
                </select>
            </div>
            <div style="clear:both;"></div>
        </div>
        <?php
        if( ! empty($sameas) ) {
            $instance['filter_type'] = $sameas['sameas'];
            if( ! empty($sameas['attribute']) ) {
                if( $sameas['sameas'] == 'custom_taxonomy' ) {
                    $instance['custom_taxonomy'] = $sameas['attribute'];
                } elseif( $sameas['sameas'] == 'attribute' ) {
                    $instance['attribute'] = $sameas['attribute'];
                }
            }
        }
        ?>
        <div class="br_clearfix"></div>
        <div class="br_admin_three_size_left br_type_select_block"<?php if( $instance['filter_type'] == 'date' ) echo 'style="display: none;"'; ?>>
            <label class="br_admin_center"><?php _e('Type', 'BeRocket_AJAX_domain') ?></label>
            <?php
            list($berocket_admin_filter_types, $berocket_admin_filter_types_by_attr) = berocket_aapf_get_filter_types();

            $select_options_variants = array();
            if ( $instance['filter_type'] == 'tag' ) {
                $select_options_variants = $berocket_admin_filter_types['tag'];
            } else if ( $instance['filter_type'] == 'product_cat' || ( $instance['filter_type'] == 'custom_taxonomy' && ( $instance['custom_taxonomy'] == 'product_tag' || $instance['custom_taxonomy'] == 'product_cat' ) ) ) {
                $select_options_variants = $berocket_admin_filter_types['product_cat'];
            } else if ( $instance['filter_type'] == '_sale' || $instance['filter_type'] == '_stock_status' || $instance['filter_type'] == '_rating' ) {
                $select_options_variants = $berocket_admin_filter_types['sale'];
            } else if ( $instance['filter_type'] == 'custom_taxonomy' ) {
                $select_options_variants = $berocket_admin_filter_types['custom_taxonomy'];
            } else if ( $instance['filter_type'] == 'attribute' ) {
                if ( $instance['attribute'] == 'price' ) {
                    $select_options_variants = $berocket_admin_filter_types['price'];
                } else {
                    $select_options_variants = $berocket_admin_filter_types['attribute'];
                }
            } else if ( $instance['filter_type'] == 'filter_by' ) {
                $select_options_variants = $berocket_admin_filter_types['filter_by'];
            }
            ?>
            <select id="<?php echo 'type'; ?>" name="<?php echo $post_name.'[type]'; ?>" class="berocket_aapf_widget_admin_type_select br_select_menu_left">
                <?php
                $selected = false;
                $first = false;
                foreach($select_options_variants as $select_options_variant) {
                    if( ! empty($berocket_admin_filter_types_by_attr[$select_options_variant]) ) {
                        echo '<option value="' . $berocket_admin_filter_types_by_attr[$select_options_variant]['value'] . '"'
                        . ($instance['type'] == $berocket_admin_filter_types_by_attr[$select_options_variant]['value'] ? ' selected' : '')
                        . '>' . $berocket_admin_filter_types_by_attr[$select_options_variant]['text'] . '</option>';
                        if( $instance['type'] == $berocket_admin_filter_types_by_attr[$select_options_variant]['value'] ) {
                            $selected = true;
                        }
                        if( $first === false ) {
                            $first = $berocket_admin_filter_types_by_attr[$select_options_variant]['value'];
                        }
                    }
                }
                if( ! $selected ) {
                    $instance['type'] = $first;
                }
                ?>
            </select>
        </div>
        <div class="br_admin_three_size_left" <?php if ( ( ! $instance['filter_type'] or $instance['filter_type'] == 'attribute' ) and  $instance['attribute'] == 'price' or $instance['type'] == 'slider' or $instance['filter_type'] == 'date' or $instance['filter_type'] == '_sale' or $instance['filter_type'] == '_rating' ) echo " style='display: none;'"; ?> >
            <label class="br_admin_center"><?php _e('Operator', 'BeRocket_AJAX_domain') ?></label>
            <select id="<?php echo 'operator'; ?>" name="<?php echo $post_name.'[operator]'; ?>" class="berocket_aapf_widget_admin_operator_select br_select_menu_left">
                <option <?php if ( $instance['operator'] == 'AND' ) echo 'selected'; ?> value="AND">AND</option>
                <option <?php if ( $instance['operator'] == 'OR' ) echo 'selected'; ?> value="OR">OR</option>
            </select>
        </div>
        <div class="berocket_aapf_order_values_by br_admin_three_size_left" <?php if ( ! $instance['filter_type'] or $instance['filter_type'] == 'date' or $instance['filter_type'] == '_sale' or $instance['filter_type'] == '_rating' or $instance['filter_type'] == '_stock_status' or ( $instance['filter_type'] == 'attribute' and $instance['attribute'] == 'price' )) echo 'style="display: none;"'; ?>>
            <label class="br_admin_center"><?php _e('Values Order', 'BeRocket_AJAX_domain') ?></label>
            <select id="<?php echo 'order_values_by'; ?>" name="<?php echo $post_name.'[order_values_by]'; ?>" class="berocket_aapf_order_values_by_select br_select_menu_left">
                <option value=""><?php _e('Default', 'BeRocket_AJAX_domain') ?></option>
                <?php foreach ( array( 'Alpha' => __('Alpha', 'BeRocket_AJAX_domain'), 'Numeric' => __('Numeric', 'BeRocket_AJAX_domain') ) as $v_i => $v ) { ?>
                    <option <?php if ( $instance['order_values_by'] == $v_i ) echo 'selected'; ?> value="<?php echo $v_i ?>"><?php echo $v; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="berocket_aapf_order_values_type br_admin_three_size_left" <?php if ( (( $instance['filter_type'] != 'attribute' && $instance['filter_type'] != 'custom_taxonomy') || ($instance['filter_type'] == 'attribute' && $instance['attribute'] == 'price') || $instance['type'] == 'ranges') && $instance['filter_type'] != '_rating' && $instance['filter_type'] != 'tag' ) echo 'style="display: none;"'; ?>>
            <label class="br_admin_center"><?php _e('Order Type', 'BeRocket_AJAX_domain') ?></label>
            <select id="<?php echo 'order_values_type'; ?>" name="<?php echo $post_name.'[order_values_type]'; ?>" class="berocket_aapf_order_values_type_select br_select_menu_left">
                <?php foreach ( array( 'asc' => __( 'Ascending', 'BeRocket_AJAX_domain' ), 'desc' => __( 'Descending', 'BeRocket_AJAX_domain' ) ) as $v_i => $v ) { ?>
                    <option <?php if ( $instance['order_values_type'] == $v_i ) echo 'selected'; ?> value="<?php echo $v_i; ?>"><?php echo $v; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="br_clearfix"></div>
        <div class="berocket_widget_color_pick">
            <?php if ( $instance['type'] == 'color' || $instance['type'] == 'image' ) {
                if ( $instance['filter_type'] == 'attribute' ) {
                    $attribute_color_view = $instance['attribute'];
                } elseif ( $instance['filter_type'] == 'product_cat' ) {
                    $attribute_color_view = 'product_cat';
                } elseif ( $instance['filter_type'] == 'tag' ) {
                    $attribute_color_view = 'product_tag';
                } elseif ( $instance['filter_type'] == 'custom_taxonomy' ) {
                    $attribute_color_view = $instance['custom_taxonomy'];
                }
                BeRocket_AAPF_Widget_functions::color_list_view( $instance['type'], $attribute_color_view, true );
            } ?>
        </div>
        <div class="berocket_ranges_block"<?php if ( ! $instance['filter_type'] or $instance['filter_type'] != 'attribute' or $instance['attribute'] != 'price' or $instance['type'] != 'ranges' ) echo ' style="display: none;"'; ?>>
        <?php
            if ( isset( $instance['ranges'] ) && is_array( $instance['ranges'] ) && count( $instance['ranges'] ) > 0 ) {
                foreach ( $instance['ranges'] as $range ) {
                    ?><div class="berocket_ranges">
                        <input type="number" min="1" id="<?php echo 'ranges'; ?>" name="<?php echo $post_name.'[ranges]'; ?>[]" value="<?php echo $range; ?>">
                        <a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a>
                    </div><?php
                }
            } else {
                ?><div class="berocket_ranges">
                    <input type="number" min="1" id="<?php echo 'ranges'; ?>" name="<?php echo $post_name.'[ranges]'; ?>[]" value="1">
                    <a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a>
                </div>
                <div class="berocket_ranges">
                    <input type="number" min="1" id="<?php echo 'ranges'; ?>" name="<?php echo $post_name.'[ranges]'; ?>[]" value="50">
                    <a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a>
                </div> <?php
            }
            ?><div><a href="#add" class="berocket_add_ranges" data-html='<div class="berocket_ranges"><input type="number" min="1" id="<?php echo 'ranges'; ?>" name="<?php echo $post_name.'[ranges]'; ?>[]" value="1"><a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a></div>'><i class="fa fa-plus"></i></a></div>
            <label>
                <select name="<?php echo $post_name.'[range_display_type]'; ?>">
                    <optgroup label="<?php _e('Ranges: 1,100,200,1000', 'BeRocket_AJAX_domain'); ?>">
                    <?php
                    $range_types = array(
                        array('value' => '',        'name' => __('1.00-100.00, 101.00-200.00, 201.00-1000.00', 'BeRocket_AJAX_domain')),
                        array('value' => 'same',    'name' => __('1.00-100.00, 100.00-200.00, 200.00-1000.00', 'BeRocket_AJAX_domain')),
                        array('value' => 'decimal', 'name' => __('1.00-99.99, 100.00-199.99, 200.00-999.99', 'BeRocket_AJAX_domain')),
                    );
                    foreach($range_types as $range_type) {
                        echo '<option value="'.$range_type['value'].'"'.(br_get_value_from_array($instance, 'range_display_type') == $range_type['value'] ? ' selected' : '').'>'.$range_type['name'].'</option>';
                    }
                    ?>
                    </optgroup>
                </select>
            </label>
            <br />
            <label>
                <input type="checkbox" name="<?php echo $post_name.'[hide_first_last_ranges]'; ?>" <?php if ( ! empty($instance['hide_first_last_ranges']) ) echo 'checked'; ?> value="1" />
                <?php _e('Hide first and last ranges without products', 'BeRocket_AJAX_domain') ?>
            </label>
            <br />
            <label>
                <input class="braapf_show_last_to_infinity" type="checkbox" name="<?php echo $post_name.'[show_last_to_infinity]'; ?>" <?php if ( ! empty($instance['show_last_to_infinity']) ) echo 'checked'; ?> value="1" />
                <?php _e('Replace the last range value with an infinity symbol', 'BeRocket_AJAX_domain') ?>
            </label>
            <br />
            <label class="braapf_to_infinity_text"<?php if ( empty($instance['show_last_to_infinity']) ) echo 'style="display:none;"'; ?>>
                <?php _e('Infinity text', 'BeRocket_AJAX_domain') ?>
                <input type="text" name="<?php echo $post_name.'[to_infinity_text]'; ?>" placeholder="&#8734;"value="<?php echo berocket_isset($instance['to_infinity_text']); ?>">
            </label>
            <script>
            jQuery('.braapf_show_last_to_infinity').change(function() {
                if( jQuery(this).prop('checked') ) {
                    jQuery('.braapf_to_infinity_text').show();
                } else {
                    jQuery('.braapf_to_infinity_text').hide();
                }
            });
            </script>
            <br />
            <label>
                <input type="checkbox" name="<?php echo $post_name.'[disable_multiple_ranges]'; ?>" <?php if ( ! empty($instance['disable_multiple_ranges']) ) echo 'checked'; ?> value="1" />
                <?php _e('Disable multiple selection?', 'BeRocket_AJAX_domain') ?>
            </label>
        </div>
        <div <?php if ( $instance['filter_type'] != 'attribute' || $instance['attribute'] != 'price' ) echo " style='display: none;'"; ?> class="berocket_aapf_widget_admin_price_attribute" >
            <div class="br-row">
                <div class="br-column-6">
                    <label class="br_admin_center" for="<?php echo 'text_before_price'; ?>"><?php _e('Text before price:', 'BeRocket_AJAX_domain') ?> </label>
                    <input class="br_admin_full_size"  id="<?php echo 'text_before_price'; ?>" type="text" name="<?php echo $post_name.'[text_before_price]'; ?>" value="<?php echo $instance['text_before_price']; ?>"/>
                </div>
                <div class="br-column-6">
                    <label class="br_admin_center" for="<?php echo 'text_after_price'; ?>"><?php _e('after:', 'BeRocket_AJAX_domain') ?> </label>
                    <input class="br_admin_full_size"  id="<?php echo 'text_after_price'; ?>" type="text" name="<?php echo $post_name.'[text_after_price]'; ?>" value="<?php echo $instance['text_after_price']; ?>" /><br>
                </div>
            </div>
            <span>%cur_symbol% will be replaced with currency symbol($)<br/>%cur_slug% will be replaced with currency code(USD)</span><br>
            <div class="berocket_aapf_widget_admin_ranges_hide" style="<?php echo ($instance['type'] == 'ranges' ? 'display: none;' : '' ) ?>">
                <input  id="<?php echo 'enable_slider_inputs'; ?>" type="checkbox" name="<?php echo $post_name.'[enable_slider_inputs]'; ?>" value="1"<?php if( ! empty($instance['enable_slider_inputs']) ) echo ' checked'; ?>/>
                <label for="<?php echo 'enable_slider_inputs'; ?>"><?php _e('Enable Slider input fields', 'BeRocket_AJAX_domain') ?> </label>
            </div>
        </div>
        <div <?php if ( $instance['filter_type'] != 'attribute' || $instance['attribute'] != 'price' ) echo " style='display: none;'"; ?> class="berocket_aapf_widget_admin_price_attribute" >
            <label for="<?php echo 'price_values'; ?>"><?php _e('Use custom values(comma separated):', 'BeRocket_AJAX_domain') ?> </label>
            <input class="br_admin_full_size" id="<?php echo 'price_values'; ?>" type="text" name="<?php echo $post_name.'[price_values]'; ?>" value="<?php echo br_get_value_from_array($instance, 'price_values'); ?>"/>
            <small><?php _e('* use numeric values only, strings will not work as expected', 'BeRocket_AJAX_domain') ?></small>
        </div>
        <div class="br_clearfix"></div>
        <div class="berocket_aapf_product_sub_cat_current" <?php if( $instance['filter_type'] != 'product_cat' ) echo 'style="display:none;"'; ?>>
            <div class="br-line-space double"></div>
            <div>
                <label>
                    <input class="berocket_aapf_product_sub_cat_current_input" type="checkbox" name="<?php echo $post_name.'[parent_product_cat_current]'; ?>" <?php if ( $instance['parent_product_cat_current'] ) echo 'checked'; ?> value="1" />
                    <?php _e('Use current product category to get child', 'BeRocket_AJAX_domain') ?>
                </label>
            </div>
            <div class="br-line-space"></div>
            <div>
                <label for="<?php echo 'depth_count'; ?>"><?php _e('Deep level:', 'BeRocket_AJAX_domain') ?></label>
                <input id="<?php echo 'depth_count'; ?>" type="number" min=0 name="<?php echo $post_name.'[depth_count]'; ?>" value="<?php echo $instance['depth_count']; ?>" />
            </div>
            <div class="br-line-space double"></div>
        </div>
        <div class="berocket_aapf_product_sub_cat_div" <?php if( $instance['filter_type'] != 'product_cat' || $instance['parent_product_cat_current'] ) echo 'style="display:none;"'; ?>>
                <label><?php _e('Product Category:', 'BeRocket_AJAX_domain') ?></label>
                <ul class="berocket_aapf_advanced_settings_categories_list">
                        <li>
                            <?php
                            echo '<input type="radio" name="' . $post_name.'[parent_product_cat]'. '" ' .
                                 ( empty($instance['parent_product_cat']) ? 'checked' : '' ) . ' value="" ' .
                                 'class="berocket_aapf_widget_admin_height_input" />';
                            ?>
                            <?php _e('None', 'BeRocket_AJAX_domain') ?>
                        </li>
                <?php
                $selected_category = false;
                foreach ( $categories as $category ) {
                    if ( (int) $instance['parent_product_cat'] == (int) $category->term_id ) {
                        $selected_category = true;
                    }
                    echo '<li>';
                    if ( (int) $category->depth ) {
                        for ( $depth_i = 0; $depth_i < $category->depth; $depth_i ++ ) {
                            echo "&nbsp;&nbsp;&nbsp;";
                        }
                    }
                    echo '<input type="radio" name="' . $post_name.'[parent_product_cat]' . '" ' .
                         ( ( $selected_category ) ? 'checked' : '' ) . ' value="' . ( $category->term_id ).'" ' .
                         'class="berocket_aapf_widget_admin_height_input" />' . ( $category->name );
                    echo '</li>';
                    $selected_category = false;
                }
                ?>
                </ul>
        </div>
        <div class="berocket_options_for_select"<?php if( ( $instance['filter_type'] != 'tag' and $instance['filter_type'] != 'custom_taxonomy' and $instance['filter_type'] != 'attribute' and $instance['filter_type'] != 'product_cat' ) or $instance['type'] != 'select' ) echo ' style="display:none;"'; ?>>
            <div>
                <label for="<?php echo 'select_first_element_text'; ?>"><?php _e('Text of the first element', 'BeRocket_AJAX_domain') ?> </label>
                <input placeholder="<?php _e('Any', 'BeRocket_AJAX_domain'); ?>" id="<?php echo 'select_first_element_text'; ?>" type="text" name="<?php echo $post_name.'[select_first_element_text]'; ?>" value="<?php echo $instance['select_first_element_text']; ?>" />
            </div>
            <div>
                <label>
                    <input type="checkbox" name="<?php echo $post_name; ?>[select_multiple]" <?php if ( ! empty($instance['select_multiple']) ) echo 'checked'; ?> value="1" />
                    <?php _e('Multiple select', 'BeRocket_AJAX_domain') ?>
                </label>
            </div>
        </div>
        <div class="br_clearfix"></div>
            <h3><?php _e('Advanced Settings', 'BeRocket_AJAX_domain') ?></h3>
            <div>
                <?php $advanced_settings_elements = array(
                    'attribute_count' => '
                        <div class="berocket_attributes_checkbox_radio_data"'
                            .( ( ( $instance['filter_type'] != 'custom_taxonomy' and $instance['filter_type'] != 'attribute' ) or ( $instance['type'] != 'checkbox' and $instance['type'] != 'radio' and $instance['type'] != 'color' and $instance['type'] != 'image' )) ? ' style="display:none;"' : '' ).'>
                            <label for="attribute_count">'.__('Number of Attribute values', 'BeRocket_AJAX_domain').'</label>
                            <input id="attribute_count" type="number" name="'.$post_name.'[attribute_count]" placeholder="'.__('From settings', 'BeRocket_AJAX_domain').'" value="'.$instance['attribute_count'].'" />
                            <div>'.__('Show/Hide button', 'BeRocket_AJAX_domain').'
                                <select name="'.$post_name.'[attribute_count_show_hide]">
                                    <option value="">'.__('Default', 'BeRocket_AJAX_domain').'</option>
                                    <option value="visible"'.( (br_get_value_from_array($instance, 'attribute_count_show_hide') == 'visible') ? ' selected' : '' ).'>'.__('Always visible', 'BeRocket_AJAX_domain').'</option>
                                    <option value="hidden"'.( (br_get_value_from_array($instance, 'attribute_count_show_hide') == 'hidden') ? ' selected' : '' ).'>'.__('Always hidden', 'BeRocket_AJAX_domain').'</option>
                                </select>
                            </div>
                        </div>',
                    'number_style' => '
                        <div class="berocket_attributes_number_style_data"'.( ( ( $instance['filter_type'] != 'custom_taxonomy' and $instance['filter_type'] != 'attribute' ) or $instance['type'] != 'slider') ? ' style="display:none;"' : '' ).'>
                            <div>
                                <input class="berocket_attributes_number_style" id="number_style" type="checkbox" name="'.$post_name.'[number_style]"'.( empty($instance['number_style']) ? '' : 'checked' ).' value="1" />
                                <label for="number_style">'.__('Use specific number style', 'BeRocket_AJAX_domain').'</label>
                            </div>
                            <div class="berocket_attributes_number_styles"'.( empty($instance['number_style']) ? ' style="display:none;"' : '' ).'>
                                <div>
                                    <label for="number_style_thousand_separate">'.__('Thousands separator', 'BeRocket_AJAX_domain').'</label>
                                    <input id="number_style_thousand_separate" type="text" name="'.$post_name.'[number_style_thousand_separate]" value="'.$instance['number_style_thousand_separate'].'" />
                                </div>
                                <div>
                                    <label for="number_style_decimal_separate">'.__('Decimal separator', 'BeRocket_AJAX_domain').'</label>
                                    <input id="number_style_decimal_separate" type="text" name="'.$post_name.'[number_style_decimal_separate]" value="'.$instance['number_style_decimal_separate'].'" />
                                </div>
                                <div>
                                    <label for="number_style_decimal_number">'.__('Number of digits after decimal point', 'BeRocket_AJAX_domain').'</label>
                                    <input id="number_style_decimal_number" type="number" name="'.$post_name.'[number_style_decimal_number]" value="'.$instance['number_style_decimal_number'].'" />
                                </div>
                            </div>
                        </div>
                    ',
                    /*'widget_collapse_disable' => '
                        <div>
                            <input id="widget_collapse_disable" type="checkbox" name="'.$post_name.'[widget_collapse_disable]"'.( empty($instance['widget_collapse_disable']) ? '' : ' checked' ).' value="1" />
                            <label for="widget_collapse_disable">'.__('Disable collapse option', 'BeRocket_AJAX_domain').'</label>
                        </div>
                    ',*/
                    'widget_collapse_enable' => '
                        <div>
                            <input id="widget_collapse_enable" type="checkbox" name="'.$post_name.'[widget_collapse_enable]"'.( empty($instance['widget_collapse_enable']) ? '' : ' checked' ).' value="1" />
                            <label for="widget_collapse_enable">'.__('Enable minimization option', 'BeRocket_AJAX_domain').'</label>
                        </div>
                    ',
                    'widget_is_hide' =>'
                        <div class="berocket_aapf_widget_is_hide">
                            <input id="widget_is_hide" type="checkbox" name="'.$post_name.'[widget_is_hide]"'.( empty($instance['widget_is_hide']) ? '' : ' checked' ).' value="1" />
                            <label for="widget_is_hide">'.__('Minimize the widget on load?', 'BeRocket_AJAX_domain').'</label>
                        </div>
                    ',
                    'hide_collapse_arrow' => '
                        <div class="berocket_aapf_hide_collapse_arrow">
                            <input id="hide_collapse_arrow" type="checkbox" name="'.$post_name.'[hide_collapse_arrow]"'.( empty($instance['hide_collapse_arrow']) ? '' : ' checked' ).' value="1" />
                            <label for="hide_collapse_arrow">'.__('Hide minimization arrow?', 'BeRocket_AJAX_domain').'</label>
                        </div>
                    ',
                    'hide_child_attributes' => '
                        <div class="berocket_aapf_widget_admin_non_price_tag_cloud_select"'
                        .( ( $instance['filter_type'] == 'date' || ( $instance['filter_type'] != 'date' && ( $instance['type'] == 'tag_cloud' || $instance['type'] == 'slider' || $instance['type'] == 'select' ) ) ) ? ' style="display:none;"' : '' ).'>
                            <input id="hide_child_attributes" type="checkbox" name="'.$post_name.'[hide_child_attributes]"'.( empty($instance['hide_child_attributes']) ? '' : ' checked' ).' value="1" />
                            <label for="hide_child_attributes">'.__('Show hierarchical values as a tree with hidden child values on load?', 'BeRocket_AJAX_domain').'</label>
                        </div>
                    ',
                );
                $advanced_settings_elements = apply_filters('berocket_widget_advanced_settings_elements', $advanced_settings_elements, $post_name, $instance);
                echo implode($advanced_settings_elements);
                ?>
                <div class="berocket_aapf_advanced_color_pick_settings"<?php if ( $instance['type'] != 'color' && $instance['type'] != 'image' ) echo " style='display: none;'"; ?>>
                    <div>
                        <input id="<?php echo 'use_value_with_color'; ?>" type="checkbox" name="<?php echo $post_name.'[use_value_with_color]'; ?>" <?php if ( $instance['use_value_with_color'] ) echo 'checked'; ?> value="1" />
                        <label for="<?php echo 'use_value_with_color'; ?>"><?php _e('Display value next to color/image?', 'BeRocket_AJAX_domain') ?></label>
                    </div>
                    <div>
                        <input id="<?php echo 'disable_multiple'; ?>" type="checkbox" name="<?php echo $post_name.'[disable_multiple]'; ?>" <?php if ( ! empty( $instance['disable_multiple'] ) ) echo 'checked'; ?> value="1" />
                        <label for="<?php echo 'disable_multiple'; ?>"><?php _e('Disable multiple selection?', 'BeRocket_AJAX_domain') ?></label>
                    </div>
                    <div>
                        <label for="color_image_block_size"><?php _e('Size of blocks(Height x Width)', 'BeRocket_AJAX_domain') ?></label>
                        <select id="color_image_block_size" name="<?php echo $post_name; ?>[color_image_block_size]">
                            <?php
                                $color_image_sizes = array(
                                    'h2em w2em' => __('2em x 2em', 'BeRocket_AJAX_domain'),
                                    'h1em w1em' => __('1em x 1em', 'BeRocket_AJAX_domain'),
                                    'h1em w2em' => __('1em x 2em', 'BeRocket_AJAX_domain'),
                                    'h2em w3em' => __('2em x 3em', 'BeRocket_AJAX_domain'),
                                    'h2em w4em' => __('2em x 4em', 'BeRocket_AJAX_domain'),
                                    'h3em w3em' => __('3em x 3em', 'BeRocket_AJAX_domain'),
                                    'h3em w4em' => __('3em x 4em', 'BeRocket_AJAX_domain'),
                                    'h3em w5em' => __('3em x 5em', 'BeRocket_AJAX_domain'),
                                    'h4em w4em' => __('4em x 4em', 'BeRocket_AJAX_domain'),
                                    'h4em w5em' => __('4em x 5em', 'BeRocket_AJAX_domain'),
                                    'h5em w5em' => __('5em x 5em', 'BeRocket_AJAX_domain'),
                                    'hxpx_wxpx' => __('Custom size', 'BeRocket_AJAX_domain'),
                                );
                                foreach($color_image_sizes as $color_image_size_id => $color_image_size_name) {
                                    echo '<option value="'.$color_image_size_id.'"'.(br_get_value_from_array($instance, 'color_image_block_size') == $color_image_size_id ? ' selected' : '').'>'.$color_image_size_name.'</option>';
                                }
                            ?>
                        </select>
                        <div class="color_image_block_size_ color_image_block_size_hxpx_wxpx"<?php if( br_get_value_from_array($instance, 'color_image_block_size') != 'hxpx_wxpx') echo ' style="display: none;"'; ?>>
                            <label><?php _e('Custom size(Height x Width)', 'BeRocket_AJAX_domain') ?></label>
                            <p>
                                <input type="number" placeholder="50" name="<?php echo $post_name; ?>[color_image_block_size_height]" value="<?php echo br_get_value_from_array($instance, 'color_image_block_size_height'); ?>">px x
                                <input type="number" placeholder="50" name="<?php echo $post_name; ?>[color_image_block_size_width]" value="<?php echo br_get_value_from_array($instance, 'color_image_block_size_width'); ?>">px</p>
                        </div>
                    </div>
                    <div>
                        <label for="color_image_checked"><?php _e('Selected value style', 'BeRocket_AJAX_domain') ?></label>
                        <select id="color_image_checked" name="<?php echo $post_name; ?>[color_image_checked]">
                            <?php
                                $color_image_sizes = array(
                                    'brchecked_default' => __('Default', 'BeRocket_AJAX_domain'),
                                    'brchecked_rotate' => __('Rotate', 'BeRocket_AJAX_domain'),
                                    'brchecked_scale' => __('Scale', 'BeRocket_AJAX_domain'),
                                    'brchecked_shadow' => __('Blue Shadow', 'BeRocket_AJAX_domain'),
                                    'brchecked_custom' => __('Custom CSS', 'BeRocket_AJAX_domain'),
                                );
                                foreach($color_image_sizes as $color_image_size_id => $color_image_size_name) {
                                    echo '<option value="'.$color_image_size_id.'"'.(br_get_value_from_array($instance, 'color_image_checked') == $color_image_size_id ? ' selected' : '').'>'.$color_image_size_name.'</option>';
                                }
                            ?>
                        </select>
                        <div class="color_image_checked_ color_image_checked_brchecked_custom"<?php if( br_get_value_from_array($instance, 'color_image_checked') != 'brchecked_custom') echo ' style="display: none;"'; ?>>
                            <label for="color_image_checked_custom_css"><?php _e('Custom CSS for Checked block', 'BeRocket_AJAX_domain') ?></label>
                            <p><textarea style="width: 100%;" id="color_image_checked_custom_css" name="<?php echo $post_name; ?>[color_image_checked_custom_css]"><?php echo br_get_value_from_array($instance, 'color_image_checked_custom_css');?></textarea></p>
                        </div>
                    </div>
                </div>
                <div class="br-line-space double"></div>
                <div class="br_accordion br_icons">
                    <h3><?php _e('Icons', 'BeRocket_AJAX_domain') ?></h3>
                    <div>
                        <label class="br_admin_center"><?php _e('Title Icons', 'BeRocket_AJAX_domain') ?></label>
                        <div class="br_clearfix"></div>
                        <div class="br_admin_half_size_left"><?php echo berocket_font_select_upload(__('Before', 'BeRocket_AJAX_domain'), 'icon_before_title', $post_name.'[icon_before_title]', $instance['icon_before_title'] ); ?></div>
                        <div class="br_admin_half_size_right"><?php echo berocket_font_select_upload(__('After', 'BeRocket_AJAX_domain'), 'icon_after_title' , $post_name.'[icon_after_title]' , $instance['icon_after_title'] ); ?></div>
                        <div class="br_clearfix"></div>
                        <div class="berocket_aapf_icons_select_block" <?php if ($instance['type'] == 'select') echo 'style="display:none;"' ?>>
                            <label class="br_admin_center"><?php _e('Value Icons', 'BeRocket_AJAX_domain') ?></label>
                            <div class="br_clearfix"></div>
                            <div class="br_admin_half_size_left"><?php echo berocket_font_select_upload(__('Before', 'BeRocket_AJAX_domain'), 'icon_before_value', $post_name.'[icon_before_value]', $instance['icon_before_value'] ); ?></div>
                            <div class="br_admin_half_size_right"><?php echo berocket_font_select_upload(__('After', 'BeRocket_AJAX_domain') , 'icon_after_value' , $post_name.'[icon_after_value]', $instance['icon_after_value'] ); ?></div>
                            <div class="br_clearfix"></div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="br_admin_center" style="text-align: left;" for="<?php echo 'description'; ?>"><?php _e('Description', 'BeRocket_AJAX_domain') ?></label>
                    <textarea style="resize: none; width: 100%;" id="<?php echo 'description'; ?>" name="<?php echo $post_name.'[description]'; ?>"><?php echo $instance['description']; ?></textarea>
                </div>
                <?php echo br_get_value_from_array($instance, 'filter_type_attribute'); ?>
                <div class="berocket_aapf_widget_admin_tag_cloud_block" <?php if ($instance['type'] != 'tag_cloud') echo 'style="display:none;"' ?>>
                    <div>
                        <label for="<?php echo 'tag_cloud_height'; ?>"><?php _e('Tags Cloud Height:', 'BeRocket_AJAX_domain') ?> </label>
                        <input id="<?php echo 'tag_cloud_height'; ?>" type="text" name="<?php echo $post_name.'[tag_cloud_height]'; ?>" value="<?php echo berocket_isset($instance['tag_cloud_height']); ?>" class="berocket_aapf_widget_admin_height_input" />px
                    </div>
                    <div>
                        <label for="<?php echo 'tag_cloud_min_font'; ?>"><?php _e('Min Font Size:', 'BeRocket_AJAX_domain') ?> </label>
                        <input id="<?php echo 'tag_cloud_min_font'; ?>" type="text" name="<?php echo $post_name.'[tag_cloud_min_font]'; ?>" value="<?php echo berocket_isset($instance['tag_cloud_min_font']); ?>" class="berocket_aapf_widget_admin_height_input" />px
                    </div>
                    <div>
                        <label for="<?php echo 'tag_cloud_max_font'; ?>"><?php _e('Max Font Size:', 'BeRocket_AJAX_domain') ?> </label>
                        <input id="<?php echo 'tag_cloud_max_font'; ?>" type="text" name="<?php echo $post_name.'[tag_cloud_max_font]'; ?>" value="<?php echo berocket_isset($instance['tag_cloud_max_font']); ?>" class="berocket_aapf_widget_admin_height_input" />px
                    </div>
                    <div>
                        <label for="<?php echo 'tag_cloud_tags_count'; ?>"><?php _e('Max Tags Count:', 'BeRocket_AJAX_domain') ?> </label>
                        <input id="<?php echo 'tag_cloud_tags_count'; ?>" type="text" name="<?php echo $post_name.'[tag_cloud_tags_count]'; ?>" value="<?php echo berocket_isset($instance['tag_cloud_tags_count']); ?>" class="berocket_aapf_widget_admin_height_input" />
                    </div>
                </div>
                <div class="berocket_aapf_widget_admin_price_attribute" <?php if ( ! ( $instance['attribute'] == 'price' && $instance['type'] == 'slider' ) ) echo " style='display: none;'"; ?> >
                    <div class="br_admin_half_size_left">
                        <div class="berocket_aapf_checked_show_next">
                            <input id="<?php echo 'use_min_price'; ?>" type="checkbox" name="<?php echo $post_name.'[use_min_price]'; ?>" <?php if ( $instance['use_min_price'] ) echo 'checked'; ?> value="1" class="berocket_aapf_widget_admin_input_price_is"/>
                            <label class="br_admin_full_size" for="<?php echo 'use_min_price'; ?>"><?php _e('Use min price', 'BeRocket_AJAX_domain') ?></label>
                        </div>
                        <div <?php if ( !$instance['use_min_price'] ) echo 'style="display:none"'; ?>>
                            <input type=number min=0 id="<?php echo 'min_price'; ?>" name="<?php echo $post_name.'[min_price]'; ?>" value="<?php echo ( ( $instance['min_price'] ) ? $instance['min_price'] : '0' ); ?>" class="br_admin_full_size berocket_aapf_widget_admin_input_price">
                        </div>
                    </div>
                    <div class="br_admin_half_size_right">
                        <div class="berocket_aapf_checked_show_next">
                            <input id="<?php echo 'use_max_price'; ?>" type="checkbox" name="<?php echo $post_name.'[use_max_price]'; ?>" <?php if ( $instance['use_max_price'] ) echo 'checked'; ?> value="1" class="berocket_aapf_widget_admin_input_price_is"/>
                            <label class="br_admin_full_size" for="<?php echo 'use_max_price'; ?>"><?php _e('Use max price', 'BeRocket_AJAX_domain') ?></label>
                        </div>
                        <div <?php if ( !$instance['use_max_price'] ) echo 'style="display:none"'; ?>>
                            <input type=number min=0 id="<?php echo 'max_price'; ?>" name="<?php echo $post_name.'[max_price]'; ?>" value="<?php echo ( ( $instance['max_price'] ) ? $instance['max_price'] : '0' ); ?>" class="br_admin_full_size berocket_aapf_widget_admin_input_price">
                        </div>
                    </div>
                    <div class="br_clearfix"></div>
                </div>
                <div>
                    <label for="<?php echo 'height'; ?>"><?php _e('Height of the Filter Block:', 'BeRocket_AJAX_domain') ?> </label>
                    <input id="<?php echo 'height'; ?>" type="text" name="<?php echo $post_name.'[height]'; ?>" value="<?php echo $instance['height']; ?>" class="berocket_aapf_widget_admin_height_input" />px
                </div>
                <div>
                    <label for="<?php echo 'scroll_theme'; ?>"><?php _e('Scrollbar theme:', 'BeRocket_AJAX_domain') ?> </label>
                    <select id="<?php echo 'scroll_theme'; ?>" name="<?php echo $post_name.'[scroll_theme]'; ?>" class="berocket_aapf_widget_admin_scroll_theme_select br_select_menu_left">
                        <?php
                        $scroll_themes = array("light", "dark", "minimal", "minimal-dark", "light-2", "dark-2", "light-3", "dark-3", "light-thick", "dark-thick", "light-thin",
                            "dark-thin", "inset", "inset-dark", "inset-2", "inset-2-dark", "inset-3", "inset-3-dark", "rounded", "rounded-dark", "rounded-dots",
                            "rounded-dots-dark", "3d", "3d-dark", "3d-thick", "3d-thick-dark");
                        foreach( $scroll_themes as $theme ): ?>
                            <option <?php if ( $instance['scroll_theme'] == $theme ) echo 'selected'; ?>><?php echo $theme; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php do_action( 'berocket_widget_filter_advanced_settings_end', $post_name, $instance); ?>
            </div>
    </div>
    <div class="berocket_aapf_admin_widget_selected_area" <?php if ( $instance['widget_type'] != 'selected_area' or $instance['widget_type'] == 'search_box' ) echo 'style="display: none;"'; ?>>
        <div class="br-line-space"></div>
        <div>
            <label>
                <input type="checkbox" name="<?php echo $post_name.'[selected_area_show]'; ?>" <?php if ( $instance['selected_area_show'] ) echo 'checked'; ?> value="1" />
                <?php _e('Show if nothing is selected', 'BeRocket_AJAX_domain') ?>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" name="<?php echo $post_name.'[hide_selected_arrow]'; ?>" <?php if ( $instance['hide_selected_arrow'] ) echo 'checked'; ?> value="1" />
                <?php _e('Hide minimization arrow?', 'BeRocket_AJAX_domain') ?>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" name="<?php echo $post_name.'[selected_is_hide]'; ?>" <?php if ( $instance['selected_is_hide'] ) echo 'checked'; ?> value="1" />
                <?php _e('Minimize the widget on load?', 'BeRocket_AJAX_domain') ?>
            </label>
        </div>
    </div>
    <?php do_action( 'berocket_widget_filter_post_end', $post_name, $instance); ?>
    <script>
    jQuery('#color_image_block_size').on('change', function() {
        jQuery('.color_image_block_size_').hide();
        jQuery('.color_image_block_size_'+jQuery(this).val()).show();
    });
    jQuery('#color_image_checked').on('change', function() {
        jQuery('.color_image_checked_').hide();
        jQuery('.color_image_checked_'+jQuery(this).val()).show();
    });
    jQuery(document).ready(function() {
        jQuery('.br_colorpicker_field').each(function (i,o){
            jQuery(o).css('backgroundColor', '#'+jQuery(o).data('color'));
            jQuery(o).colpick({
                layout: 'hex',
                submit: 0,
                color: '#'+jQuery(o).data('color'),
                onChange: function(hsb,hex,rgb,el,bySetColor) {
                    jQuery(el).removeClass('colorpicker_removed');
                    jQuery(el).css('backgroundColor', '#'+hex).next().val(hex).trigger('change');
                }
            })
        });
    });
    function berocket_aapf_widget_is_hide () {
        if( jQuery('#widget_collapse_enable').prop('checked') ) {
            jQuery('.berocket_aapf_widget_is_hide, .berocket_aapf_hide_collapse_arrow').show();
        } else {
            jQuery('.berocket_aapf_widget_is_hide, .berocket_aapf_hide_collapse_arrow').hide();
        }
    }
    jQuery(document).on('change', '#widget_collapse_enable', berocket_aapf_widget_is_hide);
    berocket_aapf_widget_is_hide();
    </script>
        <?php $instance['cat_value_limit'] = ( empty($instance['cat_value_limit']) ? '' : urldecode($instance['cat_value_limit']) ); ?>
        <div class="berocket_widget_output_limitation_block"<?php if( ! empty($instance['widget_type']) && $instance['widget_type'] != 'filter' ) echo ' style="display: none";'?>>
            <h3 class="berocket_aapf_admin_section_title "><?php _e('Widget Output Limitations', 'BeRocket_AJAX_domain') ?></h3>
            <div class="br_accordion berocket_product_category_value_limit"<?php if( ! empty($instance['widget_type']) && $instance['widget_type'] != 'filter' ) echo ' style="display: none";'?>>
                <h3><?php _e('Product Category Value Limitation', 'BeRocket_AJAX_domain') ?></h3>
                <div>
                    <ul class="br_admin_150_height">
                        <li>
                            <input type="radio" name="<?php echo $post_name.'[cat_value_limit]'; ?>" <?php if ( ! $instance['cat_value_limit'] ) echo 'checked'; ?> value="0"/>
                            <?php _e('Disable', 'BeRocket_AJAX_domain') ?>
                        </li>
                    <?php
                    foreach( $categories as $category ){
                        $selected_category = false;
                        if( $instance['cat_value_limit'] == $category->slug )
                            $selected_category = true;
                        ?>
                        <li>
                            <?php
                            if ( (int)$category->depth ) for ( $depth_i = 0; $depth_i < $category->depth*3; $depth_i++ ) echo "&nbsp;";
                            ?>
                            <input type="radio" name="<?php echo $post_name.'[cat_value_limit]'; ?>" <?php if ( $selected_category ) echo 'checked'; ?> value="<?php echo $category->slug ?>"/>
                            <?php echo $category->name ?>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
            </div>
            <?php do_action( 'berocket_widget_filter_output_limitation_end', $post_name, $instance); ?>
        </div>
    <div class="berocket_widget_reset_button_block"<?php if( empty($instance['widget_type']) || $instance['widget_type'] != 'reset_button' ) echo ' style="display: none";'?>>
        <label class="br_admin_center"><?php _e('Hide button', 'BeRocket_AJAX_domain') ?></label>
        <select id="<?php echo 'operator'; ?>" name="<?php echo $post_name.'[reset_hide]'; ?>" class="br_select_menu_left">
            <option <?php if ( empty($instance['reset_hide']) ) echo 'selected'; ?> value=""><?php _e('Do not hide', 'BeRocket_AJAX_domain'); ?></option>
            <option <?php if ( $instance['reset_hide'] == 'berocket_no_filters' ) echo 'selected'; ?> value="berocket_no_filters"><?php _e('Hide only when no filters on page', 'BeRocket_AJAX_domain'); ?></option>
            <option <?php if ( $instance['reset_hide'] == 'berocket_no_filters berocket_not_selected' ) echo 'selected'; ?> value="berocket_no_filters berocket_not_selected"><?php _e('Hide when no filters on page or page not filtered', 'BeRocket_AJAX_domain'); ?></option>
        </select>
    </div>
    <div>
        <h3><label class="br_admin_center" style="text-align: left;" for="<?php echo 'css_class'; ?>"><?php _e('CSS Class', 'BeRocket_AJAX_domain') ?> </label></h3>
        <input id="<?php echo 'css_class'; ?>" type="text" name="<?php echo $post_name.'[css_class]'; ?>" value="<?php echo $instance['css_class']; ?>" class="berocket_aapf_widget_admin_css_class_input br_admin_full_size" />
        <small class="br_admin_center" style="font-size: 1em;"><?php _e('(use white space for multiple classes)', 'BeRocket_AJAX_domain') ?></small>
    </div>
    <script>
        if( typeof(br_widget_set) == 'function' )
            br_widget_set();
    </script>
    </div>
</div>
