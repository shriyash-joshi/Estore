<?php
$attributes        = br_aapf_get_attributes();
$categories        = BeRocket_AAPF_Widget_functions::get_product_categories( '' );
$categories        = BeRocket_AAPF_Widget_functions::set_terms_on_same_level( $categories );
$tags              = get_terms( 'product_tag' );
$custom_taxonomies = get_object_taxonomies( 'product' );
$custom_taxonomies = array_combine($custom_taxonomies, $custom_taxonomies);
?>
<div class="widget-liquid-right tab-item  current">
<div>
    <label class="br_admin_center"><?php _e('Title', 'BeRocket_AJAX_domain'); ?></label>
    <input class="br_admin_full_size" name="title" value="">
</div>
<?php if( empty($instance['filter_type']) ) $instance['filter_type'] = ''; ?>
<div class="berocket_aapf_admin_filter_widget_content" <?php if ( $instance['widget_type'] == 'update_button' or $instance['widget_type'] == 'reset_button' or $instance['widget_type'] == 'selected_area' or $instance['widget_type'] == 'search_box'  ) echo 'style="display: none;"'; ?>>
    <div class="berocketwizard_attribute">
    <div class="br_admin_half_size_left">
        <label class="br_admin_center"><?php _e('Filter By', 'BeRocket_AJAX_domain') ?></label>
        <select id="<?php echo 'filter_type'; ?>" name="<?php echo $post_name.'[filter_type]'; ?>" class="berocket_aapf_widget_admin_filter_type_select br_select_menu_left">
            <?php
            $filter_type_array = braapf_single_filter_edit_elements::get_all_filter_type_array(array());
            if( ! array_key_exists($instance['filter_type'], $filter_type_array) ) {
                foreach($filter_type_array as $filter_type_key => $filter_type_val) {
                    $instance['filter_type'] = $filter_type_key;
                    break;
                }
            }
            foreach($filter_type_array as $filter_type_key => $filter_type_val) {
                echo '<option';
                foreach($filter_type_val as $data_key => $data_val) {
                    if( ! empty($data_val) ) {
                        echo ' data-'.$data_key.'="'.$data_val.'"';
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
    <div class="br_admin_half_size_left br_type_select_block"<?php if( $instance['filter_type'] == 'date' ) echo 'style="display: none;"'; ?>>
        <label class="br_admin_center"><?php _e('Type', 'BeRocket_AJAX_domain') ?></label>
        <?php
        list($berocket_admin_filter_types, $berocket_admin_filter_types_by_attr) = berocket_aapf_get_filter_types('simple');
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
    <div class="br_admin_half_size_right" <?php if ( ( ! $instance['filter_type'] or $instance['filter_type'] == 'attribute' ) and  $instance['attribute'] == 'price' or $instance['type'] == 'slider' or $instance['filter_type'] == 'date' or $instance['filter_type'] == '_sale' or $instance['filter_type'] == '_rating' ) echo " style='display: none;'"; ?> >
        <label class="br_admin_center"><?php _e('Operator', 'BeRocket_AJAX_domain') ?></label>
        <select id="<?php echo 'operator'; ?>" name="<?php echo $post_name.'[operator]'; ?>" class="berocket_aapf_widget_admin_operator_select br_select_menu_left">
            <option <?php if ( $instance['operator'] == 'AND' ) echo 'selected'; ?> value="AND">AND</option>
            <option <?php if ( $instance['operator'] == 'OR' ) echo 'selected'; ?> value="OR">OR</option>
        </select>
    </div>
</div>
<div style="clear:both;"></div>
<p>
    <?php 
    _e('Need more options? Create it on ', 'BeRocket_AJAX_domain');
    echo '<a href="' . admin_url('edit.php?post_type=br_product_filter') . '">' . __('Manage filters', 'BeRocket_AJAX_domain') . '</a>';
    _e(' page', 'BeRocket_AJAX_domain');
    ?>
</p>
<script>
    if( typeof(br_widget_set) == 'function' )
        br_widget_set();
</script>
</div>
