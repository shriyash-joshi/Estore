<?php
define('BEROCKETAAPF', 'BeRocket_AAPF_Widget');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
class BeRocket_AAPF_Widget_functions {
    function __construct() {
        add_filter('berocket_query_var_title_before_widget', array($this, 'apply_price_slider'), 10, 5);
        add_filter('berocket_aapf_is_filtered_page_check', array($this, 'is_filtered_page_check'), 10, 1);
    }
    function is_filtered_page_check($filtered) {
        if( ! empty($_GET['s']) ) {
            $filtered = true;
        }
        return $filtered;
    }
    public static function br_widget_ajax_set() {
        if ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && br_get_woocommerce_version() >= 2.1 ) {
            add_action( 'wp_ajax_berocket_aapf_color_listener', array( __CLASS__, 'color_listener' ) );
            add_action( 'wp_ajax_br_include_exclude_list', array( __CLASS__, 'ajax_include_exclude_list' ) );
        }
    }
    
    public static function apply_price_slider($set_query_var_title, $type, $instance, $args = false, $terms = false) {
        if($args === false || $terms === false) {
            return $set_query_var_title;
        } 
        extract($instance);
        extract($args);
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
            } elseif( (! empty($min_price) || $min_price == '0') && ! empty($max_price) ) {
                $price_range = array($min_price, $max_price);
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
                $terms = array();
                foreach( $all_terms_name as $term_slug ) {
                    $terms[] = (object)array(
                        'term_id'  => $term_slug,
                        'slug'     => $term_slug,
                        'value'    => $term_slug,
                        'name'     => $term_slug,
                        'count'    => 1,
                        'taxonomy' => 'price',
                        'min'      => $min,
                        'max'      => $max,
                        'step'     => '1',
                    );
                }
                $set_query_var_title['terms'] = $terms;
                $set_query_var_title['slider_display_data'] = 'arr_attr';
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
        return $set_query_var_title;
    }

    public static function remove_pid( $terms ) {

        foreach ( $terms as &$term ) {
            if ( isset( $term ) ) {
                if ( isset( $term->PID ) ) {
                    $term->PID = '';
                }

                if ( is_array( $term ) ) {
                    foreach ( $term as &$subterm ) {
                        if ( isset( $subterm ) and isset( $subterm->PID ) ) {
                            $subterm->PID = '';
                        }
                    }
                }

            }
        }
        return $terms;
    }

    public static function listener_wp_query() {
        global $wp_query, $wp_rewrite;
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );

        $add_to_args = array();
        if ( ! empty($_POST['limits']) && is_array($_POST['limits']) ) {
            foreach ( $_POST['limits'] as $post_key => $t ) {
                if( $t[0] == '_date' ) {
                    $from = $t[1];
                    $to = $t[2];
                    $from = substr($from, 0, 2).'/'.substr($from, 2, 2).'/'.substr($from, 4, 4);
                    $to = substr($to, 0, 2).'/'.substr($to, 2, 2).'/'.substr($to, 4, 4);
                    $from = date('Y-m-d 00:00:00', strtotime($from));
                    $to = date('Y-m-d 23:59:59', strtotime($to));
                    $add_to_args['date_query'] = array(
                        'after' => $from,
                        'before' => $to,
                    );
                    unset($_POST['limits'][$post_key]);
                }
            }
        }
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        if ( ! empty($_POST['terms']) && is_array($_POST['terms']) ) {
            $stop_sale = false;
            $check_sale = $check_notsale = 0;
            foreach ( $_POST['terms'] as $post_key => $t ) {
                if( $t[0] == 'price' ) {
                    if( preg_match( "~\*~", $t[1] ) ) {
                        if( ! isset( $_POST['price_ranges'] ) ) {
                            $_POST['price_ranges'] = array();
                        }
                        $_POST['price_ranges'][] = $t[1];
                        unset( $_POST['terms'][$post_key] );
                    }
                } elseif( $t[0] == '_sale' ) {
                    // if both used do nothing
                    if ( $t[0] == '_sale' and $t[3] == 'sale' ) {
                        $check_sale++;
                    }
                    if ( $t[0] == '_sale' and $t[3] == 'notsale' ) {
                        $check_notsale++;
                    }
                    unset($_POST['terms'][$post_key]);
                } elseif( $t[0] == '_rating' ) {
                    $_POST['terms'][$post_key][0] = 'product_visibility';
                }
            }
            if ( ! empty($br_options['slug_urls']) ) {
                foreach ( $_POST['terms'] as $post_key => $t ) {
                    if( $t[0] == '_stock_status' ) {
                        $_stock_status = array( 'instock' => 1, 'outofstock' => 2);
                        $_POST['terms'][$post_key][1] = (isset($_stock_status[$t[1]]) ? $_stock_status[$t[1]] : $_stock_status['instock']);
                    } else {
                        $t[1] = get_term_by( 'slug', $t[3], $t[0] );
                        $t[1] = $t[1]->term_id;
                        $_POST['terms'][$post_key] = $t;
                    }
                }
            }

            if ( ! ($check_sale and $check_notsale) ) {
                if ( $check_sale ) {
                    $add_to_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
                } elseif( $check_notsale ) {
                    $add_to_args['post__in'] = array_merge( array( 0 ), $BeRocket_AAPF->wc_get_product_ids_not_on_sale() );
                }
            }
        }

        add_filter( 'post_class', array( __CLASS__, 'add_product_class' ) );
        add_filter( 'woocommerce_pagination_args', array( __CLASS__, 'pagination_args' ) );

        $woocommerce_hide_out_of_stock_items = self::woocommerce_hide_out_of_stock_items();

        $meta_query = $BeRocket_AAPF->remove_out_of_stock( array() , true, $woocommerce_hide_out_of_stock_items != 'yes' );

        $args = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );
        foreach($add_to_args as $arg_name => $add_arg) {
            $args[$arg_name] = $add_arg;
        }
        if( ! empty($_POST['limits']) ) {
            $args = apply_filters('berocket_aapf_convert_limits_to_tax_query', $args, $_POST['limits']);
        }
        if( ! isset($args['post__in']) ) {
            $args['post__in'] = array();
        }
        if( $woocommerce_hide_out_of_stock_items == 'yes' ) {
            $args['post__in'] = $BeRocket_AAPF->remove_out_of_stock( $args['post__in'] );
        }
        if( ! br_woocommerce_version_check() ) {
            $args['post__in'] = $BeRocket_AAPF->remove_hidden( $args['post__in'] );
        }
        $args['meta_query'] = $meta_query;

        if( ! empty($_POST['limits']) ) {
            $args = apply_filters('berocket_aapf_convert_limits_to_tax_query', $args, $_POST['limits']);
        }
        if( isset($_POST['price']) && is_array($_POST['price']) ) {
            $_POST['price'] = apply_filters('berocket_min_max_filter', $_POST['price']);
        }
        $min = isset( $_POST['price'][0] ) ? floatval( $_POST['price'][0] ) : 0;
        $max = isset( $_POST['price'][1] ) ? floatval( $_POST['price'][1] ) : 9999999999;

        $args['meta_query'][] = array(
            'key'          => apply_filters('berocket_price_filter_meta_key', '_price', 'widget_2847'),
            'value'        => array( $min, $max ),
            'compare'      => 'BETWEEN',
            'type'         => 'DECIMAL',
            'price_filter' => true,
        );
    $args['post_status']    = 'publish';
        if ( is_user_logged_in() ) {
            $args['post_status'] .= '|private';
        }
        $args['post_type']      = 'product';
        $default_posts_per_page = get_option( 'posts_per_page' );
        $args['posts_per_page'] = apply_filters( 'loop_shop_per_page', $default_posts_per_page );
        if ( ! empty($_POST['price_ranges']) && is_array($_POST['price_ranges']) ) {
            $price_range_query = array( 'relation' => 'OR' );
            foreach ( $_POST['price_ranges'] as $range ) {
                $range = explode( '*', $range );
                $price_range_query[] = array( 'key' => apply_filters('berocket_price_filter_meta_key', '_price', 'widget_2867'), 'compare' => 'BETWEEN', 'type' => 'NUMERIC', 'value' => array( ($range[0] - 1), $range[1] ) );
            }
            $args['meta_query'][] = $price_range_query;
        }
        if ( ! empty($_POST['price']) && is_array($_POST['price']) ) {
            $args['meta_query'][] = array( 'key' => apply_filters('berocket_price_filter_meta_key', '_price', 'widget_2872'), 'compare' => 'BETWEEN', 'type' => 'NUMERIC', 'value' => array( ($_POST['price'][0]), $_POST['price'][1] ) );
        }

        if( isset($_POST['product_taxonomy']) && $_POST['product_taxonomy'] != '-1' && strpos( $_POST['product_taxonomy'], '|' ) !== FALSE ) {
            $product_taxonomy = explode( '|', $_POST['product_taxonomy'] );
            $args['taxonomy'] = $product_taxonomy[0];
            $args['term'] = $product_taxonomy[1];
        }
        if( isset($_POST['s']) && strlen($_POST['s']) > 0 ) {
            $args['s'] = $_POST['s'];
        }

        if( function_exists('wc_get_product_visibility_term_ids') ) {
            $product_visibility_term_ids = wc_get_product_visibility_term_ids();

            $args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'term_taxonomy_id',
                'terms'    => array($product_visibility_term_ids['exclude-from-catalog']),
                'operator' => 'NOT IN'
            );
        }
        $args = array_merge($args, WC()->query->get_catalog_ordering_args());
        $wp_query = new WP_Query( $args );

        // here we get max products to know if current page is not too big
        $is_using_permalinks = $wp_rewrite->using_permalinks();
        $_POST['location'] = (empty($_POST['location']) ? $_GET['location'] : $_POST['location']);
        if ( $is_using_permalinks and preg_match( "~/page/([0-9]+)~", $_POST['location'], $mathces ) or preg_match( "~paged?=([0-9]+)~", $_POST['location'], $mathces ) ) {
            $args['paged'] = min( $mathces[1], $wp_query->max_num_pages );
            
            $wp_query = new WP_Query( $args );
        }
        return apply_filters('berocket_listener_wp_query_return', $wp_query, $args);
    }

    public static function rebuild() {
        add_action('woocommerce_before_shop_loop', array( __CLASS__, 'tags_restore' ), 999999);
    }

    public static function tags_restore() {
		global $wp_query;
        $args = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );
        $tags = ( empty($args['product_tag']) ? '' : $args['product_tag'] );
        if( ! empty($tags) ) {
            $q_vars = $wp_query->query_vars;
            $q_vars['product_tag'] = $tags;
            $q_vars['taxonomy'] = '';
            $q_vars['term'] = '';
            unset( $q_vars['s'] );
            if( isset($q_vars['tax_query']) ) {
                $tax_query_reset = $q_vars['tax_query'];
                unset($q_vars['tax_query']);
            }
            $wp_query = new WP_Query( $q_vars );
            if( isset($tax_query_reset) ) {
                $wp_query->set('tax_query', $tax_query_reset);
                $q_vars['tax_query'] = $tax_query_reset;
                unset($tax_query_reset);
            }
        }
    }

    public static function woocommerce_before_main_content() {
        ?>||EXPLODE||<?php
        self::tags_restore();
    }

    public static function woocommerce_after_main_content() {
        ?>||EXPLODE||<?php
    }

    public static function pre_get_posts() {
        add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'woocommerce_before_main_content' ), 999999 );
        add_action( 'woocommerce_after_shop_loop', array( __CLASS__, 'woocommerce_after_main_content' ), 1 );
    }

    public static function end_clean() {
        global $wp_query, $wp_rewrite;
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );
        if ( $br_options['alternative_load_type'] != 'js' ) {
            $_RESPONSE['products'] = explode('||EXPLODE||', ob_get_contents());
            $_RESPONSE['products'] = $_RESPONSE['products'][1];
            ob_end_clean();

            if ( $_RESPONSE['products'] == null ) {
	            unset( $_RESPONSE['products'] );
	            ob_start();
                wc_no_products_found();
                $_RESPONSE['no_products'] = ob_get_contents();
                ob_end_clean();
            } else {
                $_RESPONSE['products'] = str_replace( 'explode=explode#038;', '', $_RESPONSE['products'] );
                $_RESPONSE['products'] = str_replace( '&#038;explode=explode', '', $_RESPONSE['products'] );
                $_RESPONSE['products'] = str_replace( '?explode=explode', '', $_RESPONSE['products'] );
            }
        }

        if ( braapf_filters_must_be_recounted() ) {
            $_RESPONSE['attributesname'] = array();
            $_RESPONSE['attributes']     = array();

            if ( isset($_POST['attributes']) && is_array( $_POST['attributes'] ) ) {
                $attributes = array_combine ( $_POST['attributes'], $_POST['cat_limit'] );
                foreach ( $attributes as $attribute => $cat_limit ) {
                    if ( $attribute != 'price' ) {
                        $terms = FALSE;
                        if( $attribute == '_stock_status' ) {
                            $terms = array();
                            array_push($terms, (object)array('term_id' => '1', 'term_taxonomy_id' => '1','name' => __('In stock', 'BeRocket_AJAX_domain'), 'slug' => 'instock', 'taxonomy' => '_stock_status', 'count' => 1));
                            array_push($terms, (object)array('term_id' => '2', 'term_taxonomy_id' => '2', 'name' => __('Out of stock', 'BeRocket_AJAX_domain'), 'slug' => 'outofstock', 'taxonomy' => '_stock_status', 'count' => 1));
                        }
                        $_RESPONSE['attributesname'][] = $attribute;
                        $terms                         = BeRocket_AAPF_Widget::get_attribute_values( $attribute, 'id', braapf_filters_must_be_recounted('first'), TRUE, $terms, $cat_limit );
                        $_RESPONSE['attributes'][]     = self::remove_pid( array_values($terms));
                    }
                }
            }
        }
        if( empty($br_options['woocommerce_removes']['ordering']) ) {
            ob_start();
            woocommerce_catalog_ordering();
            $_RESPONSE['catalog_ordering'] = ob_get_contents();
            ob_end_clean();
        }
        if( empty($br_options['woocommerce_removes']['result_count']) ) {
            ob_start();
            woocommerce_result_count();
            $_RESPONSE['result_count'] = ob_get_contents();
            ob_end_clean();
        }
        if( empty($br_options['woocommerce_removes']['pagination']) ) {
            ob_start();
            woocommerce_pagination();
            $_RESPONSE['pagination'] = ob_get_contents();
            $_RESPONSE['pagination'] = str_replace( 'explode=explode#038;', '', $_RESPONSE['pagination'] );
            $_RESPONSE['pagination'] = str_replace( '&#038;explode=explode', '', $_RESPONSE['pagination'] );
            $_RESPONSE['pagination'] = str_replace( '?explode=explode', '', $_RESPONSE['pagination'] );
            ob_end_clean();
        }
        if ( $br_options['alternative_load_type'] == 'js' ) echo '||JSON||';
        $_RESPONSE = apply_filters('berocket_ajax_response_with_fix', $_RESPONSE);
        $_RESPONSE['attributesname'] = array_values($_RESPONSE['attributesname']);
        $_RESPONSE['attributes'] = array_values($_RESPONSE['attributes']);
        foreach($_RESPONSE['attributesname'] as &$attributesname) {
            if( ! is_array($attributesname) ) {
                $attributesname = array();
            }
        }
        foreach($_RESPONSE['attributes'] as &$attributes) {
            if( ! is_array($attributes) ) {
                $attributes = array();
            }
        }
        echo json_encode( $_RESPONSE );
        if ( $br_options['alternative_load_type'] == 'js' ) echo '||JSON||';

        die();
    }

    public static function start_clean() {
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );
        if ( $br_options['alternative_load_type'] != 'js' ) {
            ob_start();
        }
    }

    public static function color_listener() {
        if ( defined('DOING_AJAX') && DOING_AJAX && !isset( $_POST ['tax_color_set'] ) && isset( $_POST ['br_widget_color'] ) ) {
            $_POST ['tax_color_set'] = $_POST ['br_widget_color'];
        }
        if( isset( $_POST ['tax_color_set'] ) ) {
            if ( current_user_can( 'manage_woocommerce' ) ) {
                foreach( $_POST['tax_color_set'] as $key => $value ) {
                    if( $_POST['type'] == 'color' ) {
                        foreach($value as $term_key => $term_val) {
                            if( !empty($term_val) ) {
                                update_metadata( 'berocket_term', $term_key, $key, $term_val );
                            } else {
                                delete_metadata( 'berocket_term', $term_key, $key );
                            }
                        }
                    } else {
                        update_metadata( 'berocket_term', $key, $_POST['type'], $value );
                    }
                }
                unset( $_POST['tax_color_set'] );
            }
        } else {
            self::color_list_view( $_POST['type'], $_POST['tax_color_name'], true );
            wp_die();
        }
    }

    public static function color_list_view( $type, $taxonomy_name, $load_script = false ) {
        $terms = get_terms( $taxonomy_name, array( 'hide_empty' => false ) );
        $set_query_var_color = array();
        $set_query_var_color['terms'] = $terms;
        $set_query_var_color['type'] = $type;
        $set_query_var_color['load_script'] = $load_script;
        set_query_var( 'berocket_query_var_color', $set_query_var_color );
        br_get_template_part( 'color_ajax' );
    }
    
    public static function ajax_include_exclude_list() {
        if( ! empty($_POST['taxonomy_name']) ) {
            echo self::include_exclude_terms_list($_POST['taxonomy_name']);
        }
        wp_die();
    }
    
    public static function include_exclude_terms_list($taxonomy_name = false, $selected = array() ) {
        $terms = get_terms( $taxonomy_name, array( 'hide_empty' => false ) );
        $set_query_var_exclude_list = array();
        $set_query_var_exclude_list['taxonomy'] = $taxonomy_name;
        $set_query_var_exclude_list['terms'] = $terms;
        $set_query_var_exclude_list['selected'] = $selected;
        set_query_var( 'berocket_var_exclude_list', $set_query_var_exclude_list );
        ob_start();
        br_get_template_part( 'include_exclude_list' );
        return ob_get_clean();
    }

    public static function get_product_categories( $current_product_cat = '', $parent = 0, $data = array(), $depth = 0, $max_count = 9, $follow_hierarchy = false ) {
        return br_get_sub_categories( $parent, 'id', array( 'return' => 'hierarchy_objects', 'max_depth' => $max_count ) );
    }

    public static function add_product_class( $classes ) {
        $classes[] = 'product';
        return apply_filters( 'berocket_aapf_add_product_class', $classes );
    }

    public static function pagination_args( $args = array() ) {
        $args['base'] = str_replace( 999999999, '%#%', self::get_pagenum_link( 999999999 ) );
        return $args;
    }

    // 99% copy of WordPress' get_pagenum_link.
    public static function get_pagenum_link( $pagenum = 1, $escape = true ) {
        global $wp_rewrite;

        $pagenum = (int) $pagenum;

        $request = remove_query_arg( 'paged', preg_replace( "~".home_url()."~", "", (isset($_POST['location']) ? $_POST['location'] : '') ) );

        $home_root = parse_url( home_url() );
        $home_root = ( isset( $home_root['path'] ) ) ? $home_root['path'] : '';
        $home_root = preg_quote( $home_root, '|' );

        $request = preg_replace( '|^' . $home_root . '|i', '', $request );
        $request = preg_replace( '|^/+|', '', $request );

        $is_using_permalinks = $wp_rewrite->using_permalinks();
        if ( ! $is_using_permalinks ) {
            $base = trailingslashit( get_bloginfo( 'url' ) );

            if ( $pagenum > 1 ) {
                $result = add_query_arg( 'paged', $pagenum, $base . $request );
            } else {
                $result = $base . $request;
            }
        } else {
            $qs_regex = '|\?.*?$|';
            preg_match( $qs_regex, $request, $qs_match );

            if ( ! empty( $qs_match[0] ) ) {
                $query_string = $qs_match[0];
                $request      = preg_replace( $qs_regex, '', $request );
            } else {
                $query_string = '';
            }

            $request = preg_replace( "|$wp_rewrite->pagination_base/\d+/?$|", '', $request );
            $request = preg_replace( '|^' . preg_quote( $wp_rewrite->index, '|' ) . '|i', '', $request );
            $request = ltrim( $request, '/' );

            $base = trailingslashit( get_bloginfo( 'url' ) );

            $is_using_index_permalinks = $wp_rewrite->using_index_permalinks();
            if ( $is_using_index_permalinks && ( $pagenum > 1 || '' != $request ) )
                $base .= $wp_rewrite->index . '/';

            if ( $pagenum > 1 ) {
                $request = ( ( !empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( $wp_rewrite->pagination_base . "/" . $pagenum, 'paged' );
            }

            $result = $base . $request . $query_string;
        }

        /**
         * Filter the page number link for the current request.
         *
         * @since 2.5.0
         *
         * @param string $result The page number link.
         */
        $result = apply_filters( 'get_pagenum_link', $result );

        if ( $escape )
            return esc_url( $result );
        else
            return esc_url_raw( $result );
    }

    public static function get_terms_child_parent ( $child_parent, $attribute, $current_terms = FALSE, $child_parent_depth = 1 ) {
        if ( isset($child_parent) && $child_parent == 'parent' ) {
            $args_terms = array(
                'orderby'    => 'id',
                'order'      => 'ASC',
                'hide_empty' => false,
                'parent'     => 0,
            );
            if( $attribute == 'product_cat' ) {
                $current_terms = br_get_taxonomy_hierarchy(array(), 0, 1);
            } else {
                $current_terms = get_terms( $attribute, $args_terms );
            }
        }
        if ( isset($child_parent) && $child_parent == 'child' ) {
            $current_terms = array( (object) array( 'depth' => 0, 'child' => 0, 'term_id' => 'R__term_id__R', 'count' => 'R__count__R', 'slug' => 'R__slug__R', 'name' => 'R__name__R', 'taxonomy' => 'R__taxonomy__R' ) );
            $selected_terms = br_get_selected_term( $attribute );
            $selected_terms_id = array();
            if( empty($child_parent_depth) ) {
                $child_parent_depth = 0;
            }
            foreach( $selected_terms as $selected_term ) {
                $ancestors = get_ancestors( $selected_term, $attribute );
                if( count( $ancestors ) >= ( $child_parent_depth - 1 ) ) {
                    if( count( $ancestors ) > ( $child_parent_depth - 1 ) ) {
                        $selected_term = $ancestors[count( $ancestors ) - ( $child_parent_depth )];
                    }
                    if ( ! in_array( $selected_term, $selected_terms_id ) ) {
                        $args_terms = array(
                            'orderby'    => 'id',
                            'order'      => 'ASC',
                            'hide_empty' => false,
                            'parent'     => $selected_term,
                        );
                        $selected_terms_id[] = $selected_term;
                        $additional_terms = get_terms( $attribute, $args_terms );
                        $current_terms = array_merge( $current_terms, $additional_terms );
                    }
                }
            }
        }
        return $current_terms;
    }

    public static function is_parent_selected($attribute, $child_parent_depth = 1) {
        $selected_terms = br_get_selected_term( $attribute );
        $selected_terms_id = array();
        foreach( $selected_terms as $selected_term ) {
            if( empty($child_parent_depth) ) {
                $child_parent_depth = 0;
            }
            $ancestors = get_ancestors( $selected_term, $attribute );
            if( count( $ancestors ) > ( $child_parent_depth - 1 ) ) {
                return true;
            }
        }
        return false;
    }

    public static function old_wc_compatible( $query, $new = false ) {
        return br_filters_old_wc_compatible( $query, $new );
    }

    public static function get_price_range( $product_cat = null ) {
        global $wpdb, $br_wc_query, $wp_query;

        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options = $BeRocket_AAPF->get_option();

        if( br_woocommerce_version_check('3.6') ) {
            $query[ 'select' ] = "SELECT MIN(cast(FLOOR(wc_product_meta_lookup.min_price) as decimal)) as min_price,
                              MAX(cast(CEIL(wc_product_meta_lookup.max_price) as decimal)) as max_price ";
            $query[ 'from' ]   = "FROM {$wpdb->posts}";
            $query[ 'join' ]   = " INNER JOIN {$wpdb->wc_product_meta_lookup} as wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
        } else {
            $query[ 'select' ] = "SELECT MIN(cast(FLOOR(wp_price_check.meta_value) as decimal)) as min_price,
                              MAX(cast(CEIL(wp_price_check.meta_value) as decimal)) as max_price ";
            $query[ 'from' ]   = "FROM {$wpdb->postmeta} as wp_price_check";
            $query[ 'join' ]   = "INNER JOIN {$wpdb->posts} ON ({$wpdb->posts}.ID = wp_price_check.post_id)";
        }
        if( braapf_filters_must_be_recounted() ) {
            $query = br_filters_query( $query, 'price', $product_cat );
        } elseif( braapf_filters_must_be_recounted('first') && ! empty($br_wc_query) ) {
            $query[ 'where' ] = " WHERE {$wpdb->posts}.post_type = 'product' AND " . br_select_post_status();
            $queried_object = get_queried_object();
            if( ! empty($queried_object) && is_a($queried_object, 'WP_Term') ) {
                $tax_query = array(
                    array(
                        'taxonomy' => $queried_object->taxonomy,
                        'field'    => 'id',
                        'terms'    => array($queried_object->term_id),
                        'operator' => 'IN',
                    )
                );
                $tax_query  = new WP_Tax_Query( $tax_query );
                $tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
                if( ! empty($tax_query_sql['where']) ) {
                    $query[ 'where' ] .= $tax_query_sql['where'];
                }
                if( ! empty($tax_query_sql['join']) ) {
                    $query[ 'join' ] .= $tax_query_sql['join'];
                }
            }
        } else {
            $query[ 'where' ] = " WHERE {$wpdb->posts}.post_type = 'product' AND " . br_select_post_status();
        }

        if( !br_woocommerce_version_check('3.6') ) {
            if ( $query[ 'where' ] ) {
                $query[ 'where' ] .= " AND ";
            } else {
                $query[ 'where' ] = " WHERE ";
            }
            $query[ 'where' ] .= "wp_price_check.meta_key = '".apply_filters('berocket_price_filter_meta_key', '_price', 'widget_1243')."' AND wp_price_check.meta_value > ''";
        }

        if ( $post__not_in = apply_filters('berocket_aapf_get_attribute_values_post__not_in_outside', false) ) {
            if ( $query[ 'where' ] ) {
                $query[ 'where' ] .= " AND ";
            } else {
                $query[ 'where' ] = " WHERE ";
            }

            $query[ 'where' ] .= "$wpdb->posts.ID NOT IN(" . implode( ',', $post__not_in ) . ")";
        }
        if ( $post__in = apply_filters('berocket_aapf_get_attribute_values_post__in_outside', false) ) {
            if ( $query[ 'where' ] ) {
                $query[ 'where' ] .= " AND ";
            } else {
                $query[ 'where' ] = " WHERE ";
            }

            $query[ 'where' ] .= "$wpdb->posts.ID IN(" . implode( ',', $post__in ) . ")";
        }


        $query_string = implode( ' ', $query );

        $query_string = $wpdb->get_row( $query_string );

        $price_range = false;
        if ( isset( $query_string->min_price ) && isset( $query_string->max_price ) && $query_string->min_price != $query_string->max_price ) {
            $price_range = array(
                floor(apply_filters('berocket_price_filter_widget_min_amount', apply_filters('berocket_price_slider_widget_min_amount', apply_filters( 'woocommerce_price_filter_widget_min_amount', $query_string->min_price )), $query_string->min_price)),
                ceil(apply_filters('berocket_price_filter_widget_max_amount', apply_filters('berocket_price_slider_widget_max_amount', apply_filters( 'woocommerce_price_filter_widget_max_amount', $query_string->max_price )), $query_string->max_price))
            );
        }
        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['7_price_range'] = array(
                'price_range'   => $price_range,
                'sql'           => $query_string,
            );
        }
        return apply_filters( 'berocket_aapf_get_price_range', $price_range );
    }

    public static function get_attribute_values( $taxonomy = '', $order_by = 'id', $hide_empty = false, $count_filtering = true, $input_terms = FALSE, $product_cat = FALSE, $operator = 'OR' ) {
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );
        if ( ! $taxonomy || $taxonomy == 'price' ) return array();
        if( $taxonomy == '_rating' ) $taxonomy = 'product_visibility';
        $terms = (empty($input_terms) ? FALSE : $input_terms);

        global $wp_query, $br_wc_query, $br_aapf_wc_footer_widget;

        $post__in = ( isset($wp_query->query_vars['post__in']) ? $wp_query->query_vars['post__in'] : array() );
        if (
            ! empty( $br_wc_query ) and
            ! empty( $br_wc_query->query ) and
            isset( $br_wc_query->query['post__in'] ) and
            is_array( $br_wc_query->query['post__in'] )
        ) {
            $post__in = array_merge( $post__in, $br_wc_query->query[ 'post__in' ] );
        }

        if( empty($post__in) || ! is_array($post__in) || count($post__in) == 0 ) {
            $post__in = false;
        }
        $post__not_in = ( isset($wp_query->query_vars['post__not_in']) ? $wp_query->query_vars['post__not_in'] : array() );
        if( empty($post__not_in) || ! is_array($post__not_in) || count($post__not_in) == 0 ) {
            $post__not_in = false;
        }
        global $braapf_not_filtered_data;
        if( isset($braapf_not_filtered_data['post__not_in']) ) {
            $post__not_in = $braapf_not_filtered_data['post__not_in'];
        }
        if( $hide_empty ) {
            $terms = apply_filters('berocket_aapf_recount_terms_apply', $terms, array(
                'taxonomy' => $taxonomy,
                'operator' => $operator,
                'use_filters' => FALSE,
                'post__not_in' => apply_filters('berocket_aapf_get_attribute_values_post__not_in_outside', $post__not_in),
                'post__in'     => apply_filters('berocket_aapf_get_attribute_values_post__in_outside', $post__in)
            ));
        } elseif(empty($terms)) {
            $terms = get_terms( array(
                'taxonomy'     => $taxonomy,
                'hide_empty'   => true,
                'hierarchical' => true,
                'post__not_in' => apply_filters('berocket_aapf_get_attribute_values_post__not_in_outside', false),
                'post__in'     => apply_filters('berocket_aapf_get_attribute_values_post__in_outside', false)
            ) );
        }
        if( empty($terms) || ! is_array($terms) ) {
            $terms = array();
        }
        if( $hide_empty ) {
            foreach($terms as $term_id => $term) {
                if( $term->count == 0 ) {
                    unset($terms[$term_id]);
                }
            }
        }
        if ( 
            (   ! $hide_empty 
                || apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'get_filter_args', $wp_query ) 
                || ( ! empty($br_options['out_of_stock_variable_reload']) && ! empty($br_options['out_of_stock_variable']) )
                || is_filtered()
            ) && $count_filtering 
        ) {
            $terms = apply_filters('berocket_aapf_recount_terms_apply', $terms, array(
                'taxonomy' => $taxonomy,
                'operator' => $operator,
                'use_filters' => TRUE,
                'post__not_in' => apply_filters('berocket_aapf_get_attribute_values_post__not_in_outside', $post__not_in),
                'post__in'     => apply_filters('berocket_aapf_get_attribute_values_post__in_outside', $post__in)
            ));
        }
        return $terms;
    }

    public static function sort_child_parent_hierarchy($terms) {
        $terms_sort = array();
        $new_terms = $terms;
        $terms = array_reverse($terms);
        foreach($terms as $term_id => $term) {
            if(empty($term->parent)) {
                $terms_sort[] = $term->term_id;
                unset($terms[$term_id]);
            }
        }
        $length = 0;
        while(count($terms) && $length < 30) {
            foreach($terms as $term_id => $term) {
                $term_i = array_search($term->parent, $terms_sort);
                if( $term_i !== FALSE ) {
                    array_splice($terms_sort, $term_i, 0, array($term->term_id));
                    unset($terms[$term_id]);
                }
            }
            $length++;
        }
        if( count($terms) ) {
            foreach($terms as $term_id => $term) {
                $terms_sort[] = $term->term_id;
            }
        }
        $sort_array = array();
        foreach($new_terms as $terms) {
            $sort_array[] = array_search($terms->term_id, $terms_sort);
        }
        return $sort_array;
    }

    public static function sort_terms( &$terms, $sort_data ) {
        $sort_array = array();
        if ( ! empty($terms) && is_array( $terms ) && count( $terms ) ) {
            if ( ! empty($sort_data['attribute']) and in_array($sort_data['attribute'], array('product_cat', 'berocket_brand')) and ! empty($sort_data['order_values_by']) and $sort_data['order_values_by'] == 'Default' ) {
                foreach ( $terms as $term ) {
                    $element_of_sort = get_term_meta(  $term->term_id,  'order',  true );
                    if( is_array($element_of_sort) || $element_of_sort === false ) {
                        $sort_array[] = 0;
                    } else {
                        $sort_array[] = $element_of_sort;
                    }
                    if ( ! empty($term->child) ) {
                        self::sort_terms( $term->child, $sort_data );
                    }
                }
                if( BeRocket_AAPF::$debug_mode ) {
                    BeRocket_AAPF::$error_log[$sort_data['attribute'].'_sort'] = array('array' => $sort_array, 'sort' => $terms, 'data' => $sort_data );
                }
                @ array_multisort( $sort_array, $sort_data['order_values_type'], SORT_NUMERIC, $terms );
            } elseif ( ! empty($sort_data['wc_order_by']) or ! empty($sort_data['order_values_by']) ) {
                if ( ! empty($sort_data['order_values_by']) and $sort_data['order_values_by'] == 'Numeric' ) {
                    foreach ( $terms as $term ) {
                        $sort_array[] = (float)preg_replace('/\s+/', '', str_replace(',', '.', $term->name));
                        if ( ! empty($term->child) ) {
                            self::sort_terms( $term->child, $sort_data );
                        }
                    }
                    @ array_multisort( $sort_array, $sort_data['order_values_type'], SORT_NUMERIC, $terms );
                } else {
                    $get_terms_args = array( 'hide_empty' => '0', 'fields' => 'ids' );

                    if ( ! empty($sort_data['order_values_by']) and $sort_data['order_values_by'] == 'Alpha' ) {
                        $orderby = 'name';
                    } else {
                        $orderby = 'name';
                        foreach($terms as $term_sort) {
                            $orderby = wc_attribute_orderby( $term_sort->taxonomy );
                            break;
                        }
                    }

                    switch ( $orderby ) {
                        case 'name':
                            $get_terms_args['orderby']    = 'name';
                            $get_terms_args['menu_order'] = false;
                            break;
                        case 'id':
                            $get_terms_args['orderby']    = 'id';
                            $get_terms_args['order']      = 'ASC';
                            $get_terms_args['menu_order'] = false;
                            break;
                        case 'menu_order':
                            $get_terms_args['menu_order'] = 'ASC';
                            break;
                        default:
                            break;
                    }

                    if( count($terms) ) {
                        $terms_first = reset($terms);
                        $terms2 = get_terms( $terms_first->taxonomy, $get_terms_args );
                        foreach ( $terms as $term ) {
                            $sort_array[] = array_search($term->term_id, $terms2);
                            if ( ! empty($term->child) ) {
                                self::sort_terms( $term->child, $sort_data );
                            }
                        }
                        @ array_multisort( $sort_array, $sort_data['order_values_type'], SORT_NUMERIC, $terms );
                    }
                }
                $sort_array = self::sort_child_parent_hierarchy($terms);
                @ array_multisort( $sort_array, SORT_DESC, SORT_NUMERIC, $terms );
            }
        }
    }

    public static function set_terms_on_same_level( $terms, $return_array = array(), $add_spaces = true ) {
        if ( ! empty($terms) && is_array( $terms ) && count( $terms ) ) {
            foreach ( $terms as $term ) {
                if ( $add_spaces ) {
                    for ( $i = 0; $i < $term->depth; $i++ ) {
                        $term->name = "&nbsp;&nbsp;" . $term->name;
                    }
                }

                if( ! empty($term->child) ) {
                    $child = $term->child;
                    unset( $term->child );
                }

                $return_array[] = $term;

                if ( ! empty($child) ) {
                    $return_array = self::set_terms_on_same_level( $child, $return_array, $add_spaces );
                    unset($child);
                }
            }
        } else {
            $return_array = $terms;
        }
        return $return_array;
    }

    public static function get_filter_products( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items, $use_filters = true ) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        global $wp_query, $wp_rewrite;
        $_POST['product_cat'] = $wp_query_product_cat;

        $old_post_terms = (isset($_POST['terms']) ? $_POST['terms'] : null);

        add_filter( 'woocommerce_pagination_args', array( __CLASS__, 'pagination_args' ) );

        $args = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );
        $tags = (isset($args['product_tag']) ? $args['product_tag'] : null);
        $meta_query = $BeRocket_AAPF->remove_out_of_stock( array() , true, $woocommerce_hide_out_of_stock_items != 'yes' );
        $args['post__in'] = array();

        if( $woocommerce_hide_out_of_stock_items == 'yes' ) {
            $args['post__in'] = $BeRocket_AAPF->remove_out_of_stock( $args['post__in'] );
        }
        if ( $use_filters ) {
            $args['post__in'] = $BeRocket_AAPF->limits_filter( $args['post__in'] );
            $args['post__in'] = $BeRocket_AAPF->price_filter( $args['post__in'] );
            $args['post__in'] = $BeRocket_AAPF->add_terms( $args['post__in'] );
        } else {
            $args = array( 'posts_per_page' => -1 );
            if ( ! empty($_POST['product_cat']) and $_POST['product_cat'] != '-1' ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => strip_tags( $_POST['product_cat'] ),
                    'operator' => 'IN'
                );
            }
        }

        $args['post_status'] = 'publish';
        $args['post_type'] = 'product';

        if( isset($args['tax_query']) ) {
            $tax_query_reset = $args['tax_query'];
            unset($args['tax_query']);
        }
        $wp_query = new WP_Query( $args );
        if( isset($tax_query_reset) ) {
            $wp_query->set('tax_query', $tax_query_reset);
            $args['tax_query'] = $tax_query_reset;
            unset($tax_query_reset);
        }

        // here we get max products to know if current page is not too big
        if( ! isset($_POST['location']) ) {
            $_POST['location'] = '';
        }
        if ( $wp_rewrite->using_permalinks() and preg_match( "~/page/([0-9]+)~", $_POST['location'], $mathces ) or preg_match( "~paged?=([0-9]+)~", $_POST['location'], $mathces ) ) {
            $args['paged'] = min( $mathces[1], $wp_query->max_num_pages );
            if( isset($args['tax_query']) ) {
                $tax_query_reset = $args['tax_query'];
                unset($args['tax_query']);
            }
            $wp_query = new WP_Query( $args );
            if( isset($tax_query_reset) ) {
                $wp_query->set('tax_query', $tax_query_reset);
                $args['tax_query'] = $tax_query_reset;
                unset($tax_query_reset);
            }
        }
        if ( $wp_query->found_posts <= 1 ) {
            $args['paged'] = 0;
            if( isset($args['tax_query']) ) {
                $tax_query_reset = $args['tax_query'];
                unset($args['tax_query']);
            }
            $wp_query = new WP_Query( $args );
            if( isset($tax_query_reset) ) {
                $wp_query->set('tax_query', $tax_query_reset);
                $args['tax_query'] = $tax_query_reset;
                unset($tax_query_reset);
            }
        }

        $products = array();
        if ( $wp_query->have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $products[] = get_the_ID();
            }
        }

        wp_reset_query();
        if( isset($meta_query) && is_array( $meta_query ) && count( $meta_query ) > 0 ) {
            $q_vars = $wp_query->query_vars;
            foreach( $q_vars['meta_query'] as $key_meta => $val_meta ) {
                if( $key_meta != 'relation' && $val_meta['key'] == '_stock_status') {
                    unset( $q_vars['meta_query'][$key_meta] );
                }
            }
            $q_vars['meta_query'] = array_merge( $q_vars['meta_query'], $meta_query );
            $wp_query->set('meta_query', $q_vars['meta_query']);
        }
        if( ! empty($tags) ) {
            $q_vars = $wp_query->query_vars;
            $q_vars['product_tag'] = $tags;
            unset($q_vars['s']);
            if( isset($q_vars['tax_query']) ) {
                $tax_query_reset = $q_vars['tax_query'];
                unset($q_vars['tax_query']);
            }
            $wp_query = new WP_Query( $q_vars );
            if( isset($tax_query_reset) ) {
                $wp_query->set('tax_query', $tax_query_reset);
                $q_vars['tax_query'] = $tax_query_reset;
                unset($tax_query_reset);
            }
        }

        $_POST['terms'] = $old_post_terms;
        return $products;
    }

    public static function woocommerce_hide_out_of_stock_items(){
        $hide = get_option( 'woocommerce_hide_out_of_stock_items', null );

        if ( is_array( $hide ) ) {
            $hide = array_map( 'stripslashes', $hide );
        } elseif ( ! is_null( $hide ) ) {
            $hide = stripslashes( $hide );
        }

        return apply_filters( 'berocket_aapf_hide_out_of_stock_items', $hide );
    }

    public static function price_range_count($term, $from, $to) {
        if( class_exists('WP_Meta_Query') && class_exists('WP_Tax_Query') ) {
            global $wpdb, $wp_query;
            $old_join_posts = '';
            $has_new_function = method_exists('WC_Query', 'get_main_query') && method_exists('WC_Query', 'get_main_meta_query') && method_exists('WC_Query', 'get_main_tax_query');
            if( $has_new_function ) {
                $WC_Query_get_main_query = WC_Query::get_main_query();
                $has_new_function = ! empty($WC_Query_get_main_query);
            }
            if( ! $has_new_function ) {
                $old_query_vars = self::old_wc_compatible($wp_query);
                $old_meta_query = (empty( $old_query_vars[ 'meta_query' ] ) || ! is_array($old_query_vars[ 'meta_query' ]) ? array() : $old_query_vars['meta_query']);
                $old_tax_query = (empty($old_query_vars['tax_query']) || ! is_array($old_query_vars[ 'tax_query' ]) ? array() : $old_query_vars['tax_query']);
            } else {
                $old_query_vars = self::old_wc_compatible($wp_query, true);
            }
            if( ! empty( $old_query_vars['posts__in'] ) ) {
                $old_join_posts = " AND {$wpdb->posts}.ID IN (".implode(',', $old_query_vars['posts__in']).") ";
            }
            if( $has_new_function ) {
                $tax_query  = WC_Query::get_main_tax_query();
            } else {
                $tax_query = $old_tax_query;
            }
            if( $has_new_function ) {
                $meta_query  = WC_Query::get_main_meta_query();
            } else {
                $meta_query = $old_meta_query;
            }
            foreach( $meta_query as $key => $val ) {
                if( is_array($val) ) {
                    if ( ! empty( $val['price_filter'] ) || ! empty( $val['rating_filter'] ) ) {
                        unset( $meta_query[ $key ] );
                    }
                    if( isset( $val['relation']) ) {
                        unset($val['relation']);
                        foreach( $val as $key2 => $val2 ) {
                            if ( isset( $val2['key'] ) && $val2['key'] == apply_filters('berocket_price_filter_meta_key', '_price', 'widget_1162') ) {
                                if ( isset( $meta_query[ $key ][ $key2 ] ) ) unset( $meta_query[ $key ][ $key2 ] );
                            }
                        }
                        if( count($meta_query[ $key ]) <= 1 ) {
                            unset( $meta_query[ $key ] );
                        }
                    } else {
                        if ( isset( $val['key'] ) && $val['key'] == apply_filters('berocket_price_filter_meta_key', '_price', 'widget_1170') ) {
                            if ( isset( $meta_query[ $key ] ) ) unset( $meta_query[ $key ] );
                        }
                    }
                }
            }
            $queried_object = $wp_query->get_queried_object_id();
            if( ! empty($queried_object) ) {
                $query_object = $wp_query->get_queried_object();
                if( ! empty($query_object->taxonomy) && ! empty($query_object->slug) ) {
                    $tax_query[ $query_object->taxonomy ] = array(
                        'taxonomy' => $query_object->taxonomy,
                        'terms'    => array( $query_object->slug ),
                        'field'    => 'slug',
                    );
                }
            }
            $meta_query      = new WP_Meta_Query( $meta_query );
            $tax_query       = new WP_Tax_Query( $tax_query );
            $meta_query_sql  = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
            $tax_query_sql   = $tax_query->get_sql( $wpdb->posts, 'ID' );

            // Generate query
            $query           = array();
            $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as range_count";
            $query['from']   = "FROM {$wpdb->posts}";
            $query['join']   = "
                INNER JOIN {$wpdb->postmeta} AS price_term ON {$wpdb->posts}.ID = price_term.post_id
                " . $tax_query_sql['join'] . $meta_query_sql['join'];
            $query['where']   = "
                WHERE {$wpdb->posts}.post_type IN ( 'product' )
                AND " . br_select_post_status() . "
                " . $tax_query_sql['where'] . $meta_query_sql['where'] . "
                AND price_term.meta_key = '".apply_filters('berocket_price_filter_meta_key', '_price', 'widget_1203')."' 
                AND price_term.meta_value >= {$from} AND price_term.meta_value <= {$to}
            ";
            if( defined( 'WCML_VERSION' ) && defined('ICL_LANGUAGE_CODE') ) {
                $query['join'] = $query['join']." INNER JOIN {$wpdb->prefix}icl_translations as wpml_lang ON ( {$wpdb->posts}.ID = wpml_lang.element_id )";
                $query['where'] = $query['where']." AND wpml_lang.language_code = '".ICL_LANGUAGE_CODE."' AND wpml_lang.element_type = 'post_product'";
            }
            br_where_search( $query );
            $query['where'] .= $old_join_posts;
            $query             = apply_filters( 'woocommerce_get_filtered_ranges_product_counts_query', $query );
            $query             = implode( ' ', $query );

            $results           = $wpdb->get_results( $query );
            if( isset( $results[0]->range_count ) ) {
                $term->count = $results[0]->range_count;
            }
        }
        return $term;
    }
}
new BeRocket_AAPF_Widget_functions();
