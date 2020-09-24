<?php

/**
 * BeRocket_AAPF_Widget - main filter widget. One filter for any needs
 */
class BeRocket_AAPF_Widget {

    public static $defaults = array(
        'br_wp_footer'                  => false,
        'widget_type'                   => 'filter',
        'title'                         => '',
        'filter_type'                   => 'attribute',
        'attribute'                     => 'price',
        'custom_taxonomy'               => 'product_cat',
        'type'                          => 'slider',
        'select_first_element_text'     => '',
        'operator'                      => 'OR',
        'order_values_by'               => '',
        'order_values_type'             => '',
        'text_before_price'             => '',
        'text_after_price'              => '',
        'enable_slider_inputs'          => '',
        'parent_product_cat'            => '',
        'depth_count'                   => '0',
        'widget_collapse_enable'        => '0',
        'widget_is_hide'                => '0',
        'show_product_count_per_attr'   => '0',
        'hide_child_attributes'         => '0',
        'hide_collapse_arrow'           => '0',
        'use_value_with_color'          => '0',
        'values_per_row'                => '1',
        'icon_before_title'             => '',
        'icon_after_title'              => '',
        'icon_before_value'             => '',
        'icon_after_value'              => '',
        'price_values'                  => '',
        'description'                   => '',
        'css_class'                     => '',
        'tag_cloud_height'              => '0',
        'tag_cloud_min_font'            => '12',
        'tag_cloud_max_font'            => '14',
        'tag_cloud_tags_count'          => '100',
        'tag_cloud_type'                => 'doe',
        'use_min_price'                 => '0',
        'min_price'                     => '0',
        'use_max_price'                 => '0',
        'max_price'                     => '1',
        'height'                        => 'auto',
        'scroll_theme'                  => 'dark',
        'selected_area_show'            => '0',
        'hide_selected_arrow'           => '0',
        'selected_is_hide'              => '0',
        'slider_default'                => '0',
        'number_style'                  => '0',
        'number_style_thousand_separate'=> '',
        'number_style_decimal_separate' => '.',
        'number_style_decimal_number'   => '2',
        'is_hide_mobile'                => '0',
        'user_can_see'                  => '',
        'cat_propagation'               => '0',
        'product_cat'                   => '',
        'parent_product_cat_current'    => '0',
        'attribute_count'               => '',
        'show_page'                     => array( 'shop', 'product_cat', 'product_tag', 'product_taxonomy' ),
        'cat_value_limit'               => '0',
        'child_parent'                  => '',
        'child_parent_depth'            => '1',
        'child_parent_no_values'        => '',
        'child_parent_previous'         => '',
        'child_parent_no_products'      => '',
        'child_onew_count'              => '1',
        'child_onew_childs'             => array(
            1                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            2                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            3                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            4                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            5                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            6                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            7                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            8                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            9                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
            10                              => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
        ),
        'search_box_link_type'          => 'shop_page',
        'search_box_url'                => '',
        'search_box_category'           => '',
        'search_box_count'              => '1',
        'search_box_attributes'             => array(
            1                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            2                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            3                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            4                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            5                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            6                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            7                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            8                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            9                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
            10                              => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
        ),
        'search_box_style'              => array(
            'position'                      => 'vertical',
            'search_position'               => 'after',
            'search_text'                   => 'Search',
            'background'                    => 'bbbbff',
            'back_opacity'                  => '0',
            'button_background'             => '888800',
            'button_background_over'        => 'aaaa00',
            'text_color'                    => '000000',
            'text_color_over'               => '000000',
        ),
        'ranges'                        => array( 1, 10 ),
        'hide_first_last_ranges'        => '',
        'include_exclude_select'        => '',
        'include_exclude_list'          => array(),
    );

    /**
     * Constructor
     */
    function __construct( $instance, $args = array() ) {
        if( ! empty($args['widget_id']) ) {
            $this->id = $args['widget_id'];
            $this->number = $args['widget_id'];
        }
        if( empty($this->number) || $this->number == -1 ) {
            global $berocket_aapf_shortcode_id;
            if( empty($berocket_aapf_shortcode_id) ) {
                $berocket_aapf_shortcode_id = 1;
            } else {
                $berocket_aapf_shortcode_id++;
            }
            $this->id = 'berocket_aapf_widget-s'.$berocket_aapf_shortcode_id;
            $args['widget_id'] = $this->id;
            $this->number = 's'.$berocket_aapf_shortcode_id;
        }
        $set_query_var_title = array();
        $set_query_var_main = array();
        $set_query_var_footer = array();
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
        if( empty($instance['filter_type']) || ! array_key_exists($instance['filter_type'], $filter_type_array) ) {
            foreach($filter_type_array as $filter_type_key => $filter_type_val) {
                $instance['filter_type'] = $filter_type_key;
                break;
            }
        }
        if( ! empty($instance['filter_type']) && ! empty($filter_type_array[$instance['filter_type']]) && ! empty($filter_type_array[$instance['filter_type']]['sameas']) ) {
            $sameas = $filter_type_array[$instance['filter_type']];
            $instance['filter_type'] = $sameas['sameas'];
            if( ! empty($sameas['attribute']) ) {
                if( $sameas['sameas'] == 'custom_taxonomy' ) {
                    $instance['custom_taxonomy'] = $sameas['attribute'];
                } elseif( $sameas['sameas'] == 'attribute' ) {
                    $instance['attribute'] = $sameas['attribute'];
                }
            }
        }
        //CHECK WIDGET TYPES
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
        $selected = false;
        $first = false;
        foreach($select_options_variants as $select_options_variant) {
            if( ! empty($berocket_admin_filter_types_by_attr[$select_options_variant]) ) {
                if( $instance['type'] == $berocket_admin_filter_types_by_attr[$select_options_variant]['value'] ) {
                    $selected = true;
                    break;
                }
                if( $first === false ) {
                    $first = $berocket_admin_filter_types_by_attr[$select_options_variant]['value'];
                }
            }
        }
        if( ! $selected ) {
            $instance['type'] = $first;
        }
        $widget_type_array = apply_filters( 'berocket_widget_widget_type_array', apply_filters( 'berocket_aapf_display_filter_type_list', array(
            'filter' => __('Filter', 'BeRocket_AJAX_domain'),
        ) ) );
        if( ! array_key_exists($instance['widget_type'], $widget_type_array) ) {
            foreach($widget_type_array as $widget_type_id => $widget_type_name) {
                $instance['widget_type'] = $widget_type_id;
                break;
            }
        }
        $instance['title'] = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );
        $default_language = apply_filters( 'wpml_default_language', NULL );

        global $wp_query, $wp_the_query, $wp, $sitepress, $br_wc_query;
        if( ! isset( BeRocket_AAPF::$error_log['6_widgets'] ) )
        {
            BeRocket_AAPF::$error_log['6_widgets'] = array();
        } 
        $widget_error_log             = array();

        $instance = array_merge( self::$defaults, $instance );
        $instance = apply_filters('aapf_widget_instance', $instance);
        $args = apply_filters('aapf_widget_args', $args);
        if( ( $instance['user_can_see'] == 'logged' && ! is_user_logged_in() ) || ( $instance['user_can_see'] == 'not_logged' && is_user_logged_in() ) ) {
            return false;
        }

        if( BeRocket_AAPF::$debug_mode ) {
            $widget_error_log['wp_query'] = $wp_query;
            $widget_error_log['args']     = $args;
            $widget_error_log['instance'] = $instance;
        }

        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $BeRocket_AAPF::require_all_scripts(true);
        $BeRocket_AAPF::require_all_styles(true);
        if( ! empty($br_options['filters_turn_off']) ) return false;

        if( ! empty($instance['child_parent']) && in_array($instance['child_parent'], array('child', 'parent')) ) {
            $br_options['show_all_values'] = true;
        }

        if ( isset ( $br_wc_query ) ) {
            if( ! is_a($br_wc_query, 'WP_Query') ) {
                $br_wc_query = new WP_Query( $br_wc_query );
            }
            if( class_exists('WC_Query') &&  method_exists('WC_Query', 'product_query') && method_exists('WC_Query', 'get_main_query') ) {
                $wc_query = wc()->query->get_main_query();
            }
            $old_query     = $wp_query;
            $old_the_query = $wp_the_query;
            $wp_query      = $br_wc_query;
            $wp_the_query  = $br_wc_query;
            if( class_exists('WC_Query') &&  method_exists('WC_Query', 'product_query') && method_exists('WC_Query', 'get_main_query') ) {
                wc()->query->product_query($wp_query);
            }
        }

        if ( empty($instance['br_wp_footer']) ) {
            global $br_widget_ids;
            if ( ! isset( $br_widget_ids ) ) {
                $br_widget_ids = array();
            }
            $br_widget_ids[] = array('instance' => $instance, 'args' => $args);
        }

        $text_before_price = br_get_value_from_array($instance, 'text_before_price');
        $text_after_price = br_get_value_from_array($instance, 'text_after_price');
        $text_before_price = apply_filters('aapf_widget_text_before_price', ( isset($text_before_price) ? $text_before_price : '' ) );
        $text_after_price = apply_filters('aapf_widget_text_after_price', ( isset($text_after_price) ? $text_after_price : '' ) );
        if( ! empty($text_before_price) || ! empty($text_after_price) ) {
            $cur_symbol = get_woocommerce_currency_symbol();
            $cur_slug = get_woocommerce_currency();
            if( !empty($text_before_price) ) {
                $text_before_price = str_replace(array('%cur_symbol%', '%cur_slug%'), array($cur_symbol, $cur_slug), $text_before_price);
            }
            if( !empty($text_after_price) ) {
                $text_after_price = str_replace(array('%cur_symbol%', '%cur_slug%'), array($cur_symbol, $cur_slug), $text_after_price);
            }
        }
        $instance['text_before_price'] = $text_before_price;
        $instance['text_after_price'] = $text_after_price;
        extract( $args );
        extract( $instance );

        if( ! empty($style) ) {
            echo 'This Filter cannot be displayed as deprecated';
            return false;
        }

        if ( empty($order_values_by) ) {
            $order_values_by = 'Default';
        }

        if ( ! empty($filter_type) && ( $filter_type == 'product_cat' || $filter_type == '_stock_status' || $filter_type == '_sale' || $filter_type == '_rating' ) ) {
            $attribute   = $filter_type;
            $filter_type = 'attribute';
        }

        if( empty($br_options['ajax_site']) ) {
            do_action('br_footer_script');
        } else {
            echo '<script>jQuery(document).ready(function() {if(typeof(berocket_filters_first_load) == "function") {berocket_filters_first_load();}});</script>';
        }
        if( apply_filters( 'berocket_aapf_widget_display_custom_filter', false, berocket_isset($widget_type), $instance, $args, $this ) ) {
            $this->filter_return($br_wc_query, $wp_the_query, $wp_query, $wc_query, $old_the_query, $old_query, $widget_error_log);
            return '';
        }

        if( ! empty($widget_type) && $custom_type_html = apply_filters('berocket_aapf_display_filter_custom_type', '', $widget_type, array('options' => $instance, 'args' => $args)) ) {
            if( $custom_type_html !== TRUE ) {
                echo berocket_isset($before_widget);
                echo $custom_type_html;
                echo berocket_isset($after_widget);
            }
            $widget_error_log['return'] = $widget_type;
            $this->filter_return($br_wc_query, $wp_the_query, $wp_query, $wc_query, $old_the_query, $old_query, $widget_error_log);
            return '';
        }

        $woocommerce_hide_out_of_stock_items = BeRocket_AAPF_Widget_functions::woocommerce_hide_out_of_stock_items();
        if( $woocommerce_hide_out_of_stock_items == 'yes' && $filter_type == 'attribute' && $attribute == '_stock_status' ) {
            $widget_error_log['return'] = 'stock_status';
            $this->filter_return($br_wc_query, $wp_the_query, $wp_query, $wc_query, $old_the_query, $old_query, $widget_error_log);
            return true;
        }

        if( $type == "slider" ) {
            $operator = 'OR';
        }

        $terms = $sort_terms = $price_range = array();
        list($terms_error_return, $terms_ready, $terms, $type) = apply_filters( 'berocket_widget_attribute_type_terms', array(false, false, $terms, $type), $attribute, $filter_type, $instance );
        if( $terms_ready ) {
            if( $terms_error_return === FALSE ) {
                $set_query_var_title['terms'] = apply_filters( 'berocket_aapf_widget_terms', $terms );
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['terms'] = $terms;
                }
            } else {
                $widget_error_log['terms'] = $terms;
                $widget_error_log['return'] = $terms_error_return;
                $this->filter_return($br_wc_query, $wp_the_query, $wp_query, $wc_query, $old_the_query, $old_query, $widget_error_log);
                return false;
            }
        } else {
            if ( $filter_type == 'attribute' && $attribute == 'price' && $type == 'slider' ) {
                if ( ! empty($price_values) ) {
                    $price_range = explode( ",", $price_values );
                } elseif( $use_min_price && $use_max_price ) {
                    $price_range = array($min_price, $max_price);
                } else {
                    $price_range = BeRocket_AAPF_Widget_functions::get_price_range( ( isset($cat_value_limit) ? $cat_value_limit : null ) );
                    if ( ! $price_range or count( $price_range ) < 2 ) {
                        $widget_error_log['price_range'] = $price_range;
                        $widget_error_log['return'] = 'price_range < 2';
                        $this->filter_return($br_wc_query, $wp_the_query, $wp_query, $wc_query, $old_the_query, $old_query, $widget_error_log);
                        return false;
                    }
                }
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['price_range'] = $price_range;
                }
                if( ! empty($text_before_price) || ! empty($text_after_price) ) {
                    wp_localize_script(
                        'berocket_aapf_widget-script',
                        'br_price_text',
                        array(
                            'before'  => (isset($text_before_price) ? $text_before_price : ''),
                            'after'   => (isset($text_after_price) ? $text_after_price : ''),
                        )
                    );
                }
                $set_query_var_title['text_before_price'] = (isset($text_before_price) ? $text_before_price : null);
                $set_query_var_title['text_after_price'] = (isset($text_after_price) ? $text_after_price : null);
            } elseif ( $filter_type != 'attribute' || $attribute != 'price' ) {
                $get_terms_args = array(
                    'taxonomy' => $attribute,
                    'hide_empty' => true
                );
                $get_terms_advanced = array(
                    'operator'      => $operator,
                    'force_query'   => ! empty($br_wp_footer)
                );
                if( ! empty($cat_value_limit) ) {
                    $get_terms_advanced['additional_tax_query'] = array(
                        'field'             => 'slug',
                        'include_children'  => true,
                        'operator'          => 'IN',
                        'taxonomy'          => 'product_cat',
                        'terms'             => array($cat_value_limit)
                    );
                }
                if ( $attribute == '_rating' ) {
                    $get_terms_args['taxonomy'] = 'product_visibility';
                    $get_terms_args['slug']     = array('rated-1', 'rated-2', 'rated-3', 'rated-4', 'rated-5');
                } elseif( $filter_type == 'tag' ) {
                    $get_terms_args['taxonomy'] = 'product_tag';
                } elseif( $filter_type == 'custom_taxonomy' ) {
                    $get_terms_args['taxonomy'] = $custom_taxonomy;
                } elseif( $filter_type == 'attribute' && $attribute == 'product_cat' ) {
                    $get_terms_advanced['depth'] = intval($depth_count);
                    if( ! empty($parent_product_cat_current) ) {
                        $cate = get_queried_object();
                        if( isset($cate->term_id) ) {
                            $cateID = $cate->term_id;
                        } else {
                            $cateID = 0;
                        }
                        $parent_product_cat = $cateID;
                    }
                    $get_terms_args['child_of'] = intval($parent_product_cat);
                }
                if( ! empty($order_values_by) && $order_values_by == 'Alpha' ) {
                    $get_terms_args['orderby'] = 'name';
                } elseif( ! empty($order_values_by) && $order_values_by == 'Numeric' ) {
                    $get_terms_args['orderby'] = 'name_num';
                }
                if( ! empty($order_values_type) ) {
                    $get_terms_args['order'] = ($order_values_type == 'asc' ? 'ASC' : 'DESC');
                }
                $get_terms_args = apply_filters('berocket_aapf_get_terms_args', $get_terms_args, $instance, $args);
                $get_terms_advanced = apply_filters('berocket_aapf_get_terms_additional', $get_terms_advanced, $instance, $args, $get_terms_args);
                $terms = berocket_aapf_get_terms( $get_terms_args, $get_terms_advanced );
                if ( $attribute == '_rating' ) {
                    if( is_array($terms) && ! is_wp_error($terms) ) {
                        $rating_names = array(
                            'rated-1' => ( $type == 'select' ? __('1 star', 'BeRocket_AJAX_domain') : __('<i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>', 'BeRocket_AJAX_domain') ),
                            'rated-2' => ( $type == 'select' ? __('2 stars', 'BeRocket_AJAX_domain') : __('<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>', 'BeRocket_AJAX_domain') ),
                            'rated-3' => ( $type == 'select' ? __('3 stars', 'BeRocket_AJAX_domain') : __('<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>', 'BeRocket_AJAX_domain') ),
                            'rated-4' => ( $type == 'select' ? __('4 stars', 'BeRocket_AJAX_domain') : __('<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i>', 'BeRocket_AJAX_domain') ),
                            'rated-5' => ( $type == 'select' ? __('5 stars', 'BeRocket_AJAX_domain') : __('<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>', 'BeRocket_AJAX_domain') ),
                        );
                        foreach($terms as &$term) {
                            if( isset($rating_names[$term->slug]) ) {
                                $term->name = $rating_names[$term->slug];
                            }
                        }
                    }
                }
                $terms = apply_filters('berocket_aapf_widget_include_exclude_items', $terms, $instance, $get_terms_args, $get_terms_advanced);
                if ( isset($terms) && is_array($terms) && count( $terms ) < 1 ) {
                    $widget_error_log['terms'] = $terms;
                    $widget_error_log['return'] = 'terms < 1';
                    $this->filter_return($br_wc_query, $wp_the_query, $wp_query, $wc_query, $old_the_query, $old_query, $widget_error_log);
                    return false;
                }
                $set_query_var_title['terms'] = $terms;
            }
        }

        $style = $class = '';
        $style = br_get_value_from_array($args, 'widget_inline_style');
        if( ! empty($height) and $height != 'auto' ){
            $style .= "max-height: {$height}px; overflow: hidden;";
            $class = "berocket_aapf_widget_height_control";
        }

        if( !$scroll_theme ) $scroll_theme = 'dark';
        if( $filter_type == 'custom_taxonomy' )
            $attribute = $custom_taxonomy;
        if( ! isset($attribute_count) || $attribute_count == '' ) {
            $attribute_count = br_get_value_from_array($br_options,'attribute_count');
        }

        if( $type == 'select' || $type == 'slider' ) {
            $values_per_row = 1;
        }

        $set_query_var_title['operator']                    = $operator;
        $set_query_var_title['attribute']                   = $attribute;
        $set_query_var_title['type']                        = $type;
        $set_query_var_title['title']                       = apply_filters( 'berocket_aapf_widget_title', $title );
        $set_query_var_title['class']                       = apply_filters( 'berocket_aapf_widget_class', $class );
        $set_query_var_title['css_class']                   = apply_filters( 'berocket_aapf_widget_css_class', (isset($css_class) ? $css_class : '') );
        $set_query_var_title['style']                       = apply_filters( 'berocket_aapf_widget_style', $style );
        $set_query_var_title['scroll_theme']                = $scroll_theme;
        $set_query_var_title['x']                           = time();
        $set_query_var_title['filter_type']                 = $filter_type;
        $set_query_var_title['uo']                          = br_aapf_converter_styles( (empty($br_options['styles']) ? '' : $br_options['styles']) );
        $set_query_var_title['notuo']                       = (empty($br_options['styles']) ? '' : $br_options['styles']);
        $set_query_var_title['widget_is_hide']              = (! empty($widget_collapse_enable) && ! empty($widget_is_hide));
        $set_query_var_title['widget_collapse_disable']     = empty($widget_collapse_enable);
        $set_query_var_title['is_hide_mobile']              = ! empty($is_hide_mobile);
        $set_query_var_title['show_product_count_per_attr'] = ! empty($show_product_count_per_attr);
        $set_query_var_title['hide_child_attributes']       = ! empty($hide_child_attributes);
        $set_query_var_title['cat_value_limit']             = ( isset($cat_value_limit) ? $cat_value_limit : null );
        $set_query_var_title['select_first_element_text']   = ( empty($select_first_element_text) ? __('Any', 'BeRocket_AJAX_domain') : $select_first_element_text );
        $set_query_var_title['icon_before_title']           = (isset($icon_before_title) ? $icon_before_title : null);
        $set_query_var_title['icon_after_title']            = (isset($icon_after_title) ? $icon_after_title : null);
        $set_query_var_title['hide_o_value']                = ! empty($br_options['hide_value']['o']);
        $set_query_var_title['hide_sel_value']              = ! empty($br_options['hide_value']['sel']);
        $set_query_var_title['hide_empty_value']            = ! empty($br_options['hide_value']['empty']);
        $set_query_var_title['hide_button_value']           = ! empty($br_options['hide_value']['button']);
        $set_query_var_title['attribute_count_show_hide']   = berocket_isset($attribute_count_show_hide);
        $set_query_var_title['attribute_count']             = $attribute_count;
        $set_query_var_title['description']                 = (isset($description) ? $description : null);
        $set_query_var_title['hide_collapse_arrow']         = (empty($widget_collapse_enable) || ! empty($hide_collapse_arrow));
        $set_query_var_title['values_per_row']              = (isset($values_per_row) ? $values_per_row : null);
        $set_query_var_title['child_parent']                = (isset($child_parent) ? $child_parent : null);
        $set_query_var_title['child_parent_depth']          = (isset($child_parent_depth) ? $child_parent_depth : null);
        $set_query_var_title['product_count_style']         = (isset($br_options['styles_input']['product_count']) ? $br_options['styles_input']['product_count'] : '').'pcs '.(isset($br_options['styles_input']['product_count_position']) ? $br_options['styles_input']['product_count_position'] : null).'pcs';
        $set_query_var_title['styles_input']                = (isset($br_options['styles_input']) ? $br_options['styles_input'] : array());
        $set_query_var_title['child_parent_previous']       = (isset($child_parent_previous) ? $child_parent_previous : null);
        $set_query_var_title['child_parent_no_values']      = (isset($child_parent_no_values) ? $child_parent_no_values : null);
        $set_query_var_title['child_parent_no_products']    = (isset($child_parent_no_products) ? $child_parent_no_products : null);
        $set_query_var_title['before_title']                = (isset($before_title) ? $before_title : null);
        $set_query_var_title['after_title']                 = (isset($after_title) ? $after_title : null);
        $set_query_var_title['widget_id']                   = ( $this->id ? $this->id : $widget_id );
        $set_query_var_title['widget_id_number']            = ( $this->number ? $this->number : $widget_id_number );
        $set_query_var_title['slug_urls']                   = ! empty($br_options['slug_urls']);
        $set_query_var_title['first_page_jump'] = ( empty($first_page_jump) ? '' : $first_page_jump );
        $set_query_var_title['icon_before_value'] = (isset($icon_before_value) ? $icon_before_value : null);
        $set_query_var_title['icon_after_value'] = (isset($icon_after_value) ? $icon_after_value : null);
        $set_query_var_title = apply_filters('berocket_aapf_query_var_title_filter', $set_query_var_title, $instance, $br_options);
        set_query_var( 'berocket_query_var_title', $set_query_var_title );

        // widget title and start tag ( <ul> ) can be found in templates/widget_start.php
        echo berocket_isset($before_widget);
        do_action('berocket_aapf_widget_before_start');
        br_get_template_part('old_templates/widget_start');
        do_action('berocket_aapf_widget_after_start');

        if ( $type == 'tag_cloud' ) {
            $tag_script_var = array(
                'height'        => $tag_cloud_height,
                'min_font_size' => $tag_cloud_min_font,
                'max_font_size' => $tag_cloud_max_font,
                'tags_count'    => $tag_cloud_tags_count,
                'tags_type'    => $tag_cloud_type
            );
            $set_query_var_title['tag_script_var'] = $tag_script_var;
        } elseif ( $type == 'color' || $type == 'image' ) {
            $set_query_var_title['use_value_with_color'] = (isset($use_value_with_color) ? $use_value_with_color : null);
            $set_query_var_title['disable_multiple'] = (isset($disable_multiple) ? $disable_multiple : null);
            $set_query_var_title['color_image_block_size'] = berocket_isset($color_image_block_size, false, 'h2em w2em');
            $set_query_var_title['color_image_checked'] = berocket_isset($color_image_checked, false, 'brchecked_default');
            $set_query_var_title['color_image_checked_custom_css'] = berocket_isset($color_image_checked_custom_css);
            $set_query_var_title['color_image_block_size_height'] = berocket_isset($color_image_block_size_height);
            $set_query_var_title['color_image_block_size_width'] = berocket_isset($color_image_block_size_width);
        } elseif( $type == 'select' ) {
            $set_query_var_title['select_multiple'] = ! empty($select_multiple);
        }
        $slider_with_string = false;
        $stringed_is_numeric = true;
        $slider_step = 1;
        if ( $filter_type == 'attribute' && $attribute == 'price' && $type == 'slider' ) {
            $min = $max   = false;
            $main_class   = 'slider';
            $slider_class = 'berocket_filter_slider';

            wp_localize_script(
                'berocket_aapf_widget-script',
                'br_price_text',
                array(
                    'before'  => (isset($text_before_price) ? $text_before_price : ''),
                    'after'   => (isset($text_after_price) ? $text_after_price : ''),
                )
            );
            if ( ! empty($price_values) ) {
                $price_range = explode( ",", $price_values );
            } else {
                $price_range = BeRocket_AAPF_Widget_functions::get_price_range( ( isset($cat_value_limit) ? $cat_value_limit : null ) );
            }
            if ( ! empty($price_values) ) {
                $all_terms_name = $price_range;
                $all_terms_slug = $price_range;
                $stringed_is_numeric = true;
                $min = 0;
                $max = count( $all_terms_name ) - 1;
                $slider_with_string = true;
            } else {
                if( $price_range ) {
                    foreach ( $price_range as $price ) {
                        if ( $min === false or $min > (int) $price ) {
                            $min = $price;
                        }
                        if ( $max === false or $max < (int) $price ) {
                            $max = $price;
                        }
                    }
                }
                if( $use_min_price ) {
                    $min = $min_price;
                }
                if ( $use_max_price ) {
                    $max = $max_price;
                }
            }
            if( ! empty($_POST['price']) ) {
                if ( ! empty($price_values) ) {
                    $slider_value1 = array_search( $_POST['price'][0], $all_terms_name );
                    $slider_value2 = array_search( $_POST['price'][1], $all_terms_name );
                } else {
                    $slider_value1 = apply_filters('berocket_price_filter_widget_min_amount', apply_filters('berocket_price_slider_widget_min_amount', apply_filters('woocommerce_price_filter_widget_min_amount', $_POST['price'][0])), $_POST['price'][0]);
                    $slider_value2 = apply_filters('berocket_price_filter_widget_max_amount', apply_filters('berocket_price_slider_widget_max_amount', apply_filters('woocommerce_price_filter_widget_max_amount', $_POST['price'][1])), $_POST['price'][1]);
                }
            } else {
                $slider_value1 = $min;
                $slider_value2 = $max;
            }
            $id = 'br_price';
            $slider_class .= ' berocket_filter_price_slider';
            $main_class .= ' price';

            $min = floor( $min );
            $max = ceil( $max );

            $wpml_id = preg_replace( '#^pa_#', '', $id );
            $wpml_id = 'pa_'.berocket_wpml_attribute_translate($wpml_id);
            $set_query_var_title['slider_value1'] = $slider_value1;
            $set_query_var_title['slider_value2'] = $slider_value2;
            $set_query_var_title['filter_slider_id'] = $wpml_id;
            $set_query_var_title['main_class'] = $main_class;
            $set_query_var_title['slider_class'] = $slider_class;
            $set_query_var_title['min'] = $min;
            $set_query_var_title['max'] = $max;
            $set_query_var_title['step'] = $slider_step;
            $set_query_var_title['slider_with_string'] = $slider_with_string;
            $set_query_var_title['all_terms_name'] = ( empty($all_terms_name) ? null : $all_terms_name );
            $set_query_var_title['all_terms_slug'] = ( empty($all_terms_slug) ? null : $all_terms_slug );
            $set_query_var_title['text_before_price'] = (isset($text_before_price) ? $text_before_price : null);
            $set_query_var_title['text_after_price'] = (isset($text_after_price) ? $text_after_price : null);
            $set_query_var_title['enable_slider_inputs'] = (isset($enable_slider_inputs) ? $enable_slider_inputs : null);
            if( ! empty($number_style) ) {
                $set_query_var_title['number_style'] = array(
                    ( empty($number_style_thousand_separate) ? '' : $number_style_thousand_separate ), 
                    ( empty($number_style_decimal_separate) ? '' : $number_style_decimal_separate ), 
                    ( empty($number_style_decimal_number) ? '' : $number_style_decimal_number )
                );
            } else {
                $set_query_var_title['number_style'] = '';
            }
        }
        $set_query_var_title = apply_filters('berocket_query_var_title_before_widget_deprecated', $set_query_var_title, $type, $instance, $args, $terms);
        set_query_var( 'berocket_query_var_title', $set_query_var_title);
        br_get_template_part( 'old_templates/'.apply_filters('berocket_widget_load_template_name', $type, $instance, (empty($terms) ? '' : $terms)) );

        do_action('berocket_aapf_widget_before_end');
        br_get_template_part('old_templates/widget_end');
        do_action('berocket_aapf_widget_after_end');
        echo berocket_isset($after_widget);
        if( BeRocket_AAPF::$debug_mode ) {
            $widget_error_log['terms'] = (isset($terms) ? $terms : null);
        }
        $widget_error_log['return'] = 'OK';
        $this->filter_return($br_wc_query, $wp_the_query, $wp_query, $wc_query, $old_the_query, $old_query, $widget_error_log);
    }

    public function filter_return(&$br_wc_query, &$wp_the_query, &$wp_query, &$wc_query, &$old_the_query, &$old_query, $widget_error_log) {
        BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
        if ( isset ( $br_wc_query ) ) {
            if ( isset ( $old_query ) ) {
                $wp_the_query = $old_the_query;
                $wp_query = $old_query;
            }
            if( ! empty($wc_query) && is_a($wc_query, 'WP_Query') && class_exists('WC_Query') &&  method_exists('WC_Query', 'product_query') && method_exists('WC_Query', 'get_main_query') ) {
                wc()->query->product_query($wc_query);
            }
            wc()->query->remove_ordering_args();
        }
        do_action('berocket_aapf_filter_end_generation');
    }
    //DEPRECATED SOON
    function update( $new_instance, $old_instance ) {
        return $old_instance;
    }
    function form( $instance ) {
        include AAPF_TEMPLATE_PATH . "admin.php";
    }
}
