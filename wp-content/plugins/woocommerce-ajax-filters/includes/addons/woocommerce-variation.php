<?php
class BeRocket_AAPF_compat_woocommerce_variation {
    public $limit_post__not_in_where_array = array();
    public $is_init = false;
    function __construct() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( berocket_isset($options['out_of_stock_variable']) == 1 ) {
            $this->add_filter();
        } else {
            add_action('br_aapf_args_converter_after', array($this, 'br_aapf_args_converter_after'));
        }
    }
    function br_aapf_args_converter_after() {
        if( ! $this->is_init ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $options = $BeRocket_AAPF->get_option();
            global $br_url_parser_middle_result;
            $is_woocommerce_variation_enabled = apply_filters(
                'berocket_compat_woocommerce_variation_enabled',
                 (berocket_isset($options['out_of_stock_variable']) == 2
                && ! empty($br_url_parser_middle_result['_stock_status'])
                && is_array($br_url_parser_middle_result['_stock_status'])
                && ( in_array('1', $br_url_parser_middle_result['_stock_status']) || in_array('instock', $br_url_parser_middle_result['_stock_status']) )
                ),
                $options
            );
            if( $is_woocommerce_variation_enabled ) {
                $this->add_filter();
            } else {
                add_action('berocket_term_recount_before_action', array($this, 'start_stock_status'), 10, 2);
                add_action('berocket_term_recount_after_action', array($this, 'stop_stock_status'), 10, 3);
            }
        }
    }
    public function add_filter() {
        if( ! $this->is_init ) {
            $this->is_init = true;
            $this->filter_hooks();
        }
    }
    public function filter_hooks($add = true) {
        $action = ($add ? 'add_filter' : 'remove_filter');
        $action('berocket_filters_query_already_filtered', array(__CLASS__, 'query_already_filtered'), 10, 3);
        $action('berocket_add_out_of_stock_variable', array(__CLASS__, 'out_of_stock_variable'), 10, 3);
        $action('brAAPFcompat_WCvariation_out_of_stock_where', array(__CLASS__, 'out_of_stock_where'), 10, 1);
        $action('berocket_aapf_recount_terms_query', array($this, 'faster_recount_add_data'), 60, 3);
        $action('berocket_query_result_recount', array($this, 'faster_recount_query_result'), 60, 3);
        $action('berocket_recount_cache_key', array($this, 'faster_recount_cache_key'), 60);
    }
    public function start_stock_status($terms, $taxonomy_data) {
        if( ! $this->is_init && $taxonomy_data['taxonomy'] == '_stock_status' ) {
            $this->filter_hooks();
        }
    }
    public function stop_stock_status($terms, $taxonomy_data) {
        if( ! $this->is_init && $taxonomy_data['taxonomy'] == '_stock_status' ) {
            $this->filter_hooks(false);
        }
    }
    public static function query_already_filtered($query, $terms, $limits) {
        $post_not_in = self::out_of_stock_variable(array(), $terms, $limits, $query);
        if( is_array($post_not_in) && count($post_not_in) ) {
            $post__not_in = $query->get('post__not_in');
            $post__not_in = array_merge($post__not_in, $post_not_in);
            $post__in = $query->get('post__in');
            $post__in = array_diff($post__in, $post__not_in);
            $query->set('post__not_in', $post__not_in);
            $query->set('post__in', $post__in);
        }
        return $query;
    }
    public static function out_of_stock_variable($input, $terms, $limits, $query = false) {
        global $wpdb;
        if( $query === false ) {
            $get_queried_object = get_queried_object();
        } else {
            $get_queried_object = $query->get_queried_object();
        }
        if( is_a($get_queried_object, 'WP_Term') && strpos($get_queried_object->taxonomy, 'pa_') !== FALSE ) {
            if( ! is_array($terms) ) {
                $terms = array();
            }
            $terms[] = array(
                $get_queried_object->taxonomy,
                $get_queried_object->term_id,
                'OR',
                $get_queried_object->slug,
                'attribute'
            );
        }
        $outofstock = wc_get_product_visibility_term_ids();
        if( empty($outofstock['outofstock']) ) {
            $outofstock = get_term_by( 'slug', 'outofstock', 'product_visibility' );
            $outofstock = $outofstock->term_taxonomy_id;
        } else {
            $outofstock = $outofstock['outofstock'];
        }
        $current_terms = array();
        $current_attributes = array();
        if( is_array($terms) && count($terms) ) {
            foreach($terms as $term) {
                if( substr( $term[0], 0, 3 ) == 'pa_' ) {
                    $current_attributes[] = sanitize_title('attribute_' . $term[0]);
                    $current_terms[] = sanitize_title($term[3]);
                }
            }
        }
        if( is_array($limits) && count($limits) ) {
            foreach($limits as $attr => $term_ids) {
                if( substr( $attr, 0, 3 ) == 'pa_' ) {
                    $current_attributes[] = sanitize_title('attribute_' . $attr);
                    foreach($term_ids as $term_id) {
                        $term = get_term($term_id);
                        if( ! empty($term) && ! is_wp_error($term) ) {
                            $current_terms[] = $term->slug;
                        }
                    }
                }
            }
        }
        $current_terms = array_unique($current_terms);
        $current_attributes = array_unique($current_attributes);
        $current_terms = implode('","', $current_terms);
        $current_attributes = implode('","', $current_attributes);
        $query_filtered_posts = apply_filters( 'berocket_aapf_wcvariation_filtering_main_query', array(
            'select'    => 'SELECT %1$s.id as var_id, %1$s.post_parent as ID, COUNT(%1$s.id) as meta_count',
            'from'      => 'FROM %1$s',
            'join'      => 'INNER JOIN %2$s AS pf1 ON (%1$s.ID = pf1.post_id)',
            'where'     => 'WHERE %1$s.post_type = "product_variation"',
            'and1'      => 'AND %1$s.post_status != "trash"',
            'and2'      => 'AND pf1.meta_key IN ("%4$s")',
            'and3'      => 'AND pf1.meta_value IN ("%5$s")',
            'group'     => 'GROUP BY %1$s.id'
        ), $input, $terms, $limits, $current_attributes, $current_terms);
        $query = array(
            'select'        => 'SELECT filtered_post.id, filtered_post.out_of_stock, COUNT(filtered_post.ID) as post_count',
            'from_open'     => 'FROM (',
            'subquery'      => array(
                'select'        => 'SELECT filtered_post.*, max_filtered_post.max_meta_count, stock_table.out_of_stock_init as out_of_stock',
                'from_open'     => 'FROM (',
                'subquery_1'    => $query_filtered_posts,
                'from_close'    => ') as filtered_post',
                'join_open_1'   => 'INNER JOIN (',
                'subquery_2'    => array(
                    'select'        => 'SELECT ID, MAX(meta_count) as max_meta_count',
                    'from_open'     => 'FROM (',
                    'subquery'      => $query_filtered_posts,
                    'from_close'    => ') as max_filtered_post',
                    'group'         => 'GROUP BY ID'
                ),
                'join_close_1'  => ') as max_filtered_post ON max_filtered_post.ID = filtered_post.ID AND max_filtered_post.max_meta_count = filtered_post.meta_count',
                'join_open_2'   => 'LEFT JOIN (',
                'subquery_3'    => array(
                    'select'        => 'SELECT %1$s .id as id, IF(%1$s.post_status = "private", 1, COALESCE(stock_table_init.out_of_stock_init1, "0")) as out_of_stock_init',
                    'from'          => 'FROM %1$s',
                    'join_open'     => 'LEFT JOIN (',
                    'subquery'      => array(
                        'select'    => 'SELECT %1$s.id as id, "1" as out_of_stock_init1',
                        'from'      => 'FROM %1$s',
                        'where'     => apply_filters('brAAPFcompat_WCvariation_out_of_stock_where', 'WHERE %1$s.id IN 
                            (
                                SELECT object_id FROM %3$s 
                                WHERE term_taxonomy_id IN ( '.$outofstock.' ) 
                            ) '
                        )
                    ),
                    'join_close'    => ') as stock_table_init on %1$s.id = stock_table_init.id',
                    'group'         => 'GROUP BY id',
                ),
                'join_close_2'  => ') as stock_table ON filtered_post.var_id = stock_table.id',
                'group'         => 'GROUP BY filtered_post.ID, out_of_stock',
            ),
            'from_close'    => ') as filtered_post',
            'group'         => 'GROUP BY filtered_post.ID',
            'having'        => 'HAVING post_count = 1 AND out_of_stock = 1',
        );
        $query = apply_filters('berocket_aapf_wcvariation_filtering_total_query', $query, $input, $terms, $limits, $current_attributes, $current_terms);
        $query = self::implode_recursive($query);
        $query = str_replace(
            array( '%1$s',          '%2$s',             '%3$s',                     '%4$s',                 '%5$s' ),
            array( $wpdb->posts,    $wpdb->postmeta,    $wpdb->term_relationships,  $current_attributes,    $current_terms ),
            $query
        );
        $out_of_stock_variable = br_get_cache(apply_filters('berocket_variation_cache_key', md5($query)), 'berocket_variation');
        if( empty($out_of_stock_variable) ) {
            $out_of_stock_variable = $wpdb->get_results( $query, ARRAY_N );
            br_set_cache(apply_filters('berocket_variation_cache_key', md5($query)), $out_of_stock_variable, 'berocket_variation', MINUTE_IN_SECONDS);
        }
        if( BeRocket_AAPF::$debug_mode ) {
            if( ! isset(BeRocket_AAPF::$error_log['_addons_variations_query']) || ! is_array(BeRocket_AAPF::$error_log['_addons_variations_query']) ) {
                BeRocket_AAPF::$error_log['_addons_variations_query'] = array();
            }
            BeRocket_AAPF::$error_log['_addons_variations_query'][] = array(
                'query'  => $query,
                'result' => $out_of_stock_variable,
                'terms'  => $terms
            );
        }
        $post_not_in = array();
        if( is_array($out_of_stock_variable) && count($out_of_stock_variable) ) {
            foreach($out_of_stock_variable as $out_of_stock) {
                $post_not_in[] = $out_of_stock[0];
            }
        }
        return $post_not_in;
    }
    public static function implode_recursive($array, $glue = ' ') {
        foreach($array as &$element) {
            if( is_array($element) ) {
                $element = self::implode_recursive($element, $glue);
            }
        }
        return implode($glue, $array);
    }
    public static function out_of_stock_where($custom_where) {
        if ( ! empty($_POST['price_ranges']) || ! empty($_POST['price']) ) {
            global $wpdb;
            $custom_where .= ' OR %1$s.id IN (
            SELECT %2$s.post_id FROM %2$s 
            WHERE ';
            if ( ! empty($_POST['price']) ) {
                $min = isset( $_POST['price'][0] ) ? floatval( $_POST['price'][0] ) : 0;
                $max = isset( $_POST['price'][1] ) ? floatval( $_POST['price'][1] ) : 9999999999;
                $custom_where .= ' %2$s.meta_key = "_price" AND %2$s.meta_value NOT BETWEEN '.$min.' AND '.$max;
            } else {
                $custom_where .= ' %2$s.meta_key = "_price" AND (';
                $price_ranges = array();
                foreach ( $_POST['price_ranges'] as $range ) {
                    $range = explode( '*', $range );
                    $min = isset( $range[0] ) ? floatval( ($range[0] - 1) ) : 0;
                    $max = isset( $range[1] ) ? floatval( $range[1] ) : 0;
                    $price_ranges[] = '( %2$s.meta_value NOT BETWEEN '.$min.' AND '.$max.' )';
                }
                $custom_where .= implode(' AND ', $price_ranges);
                $custom_where .= ")";
            }
            $custom_where .= ")";
        }
        return $custom_where;
    }
    public function faster_recount_add_data($query, $taxonomy_data, $terms) {
        global $wpdb;
        extract($taxonomy_data);
        if( ! $use_filters ) return $query;
        $br_options = BeRocket_AAPF::get_aapf_option();
        if( ! empty($br_options['out_of_stock_variable_reload']) ) {
            $new_post_terms = br_get_value_from_array($_POST,'terms');
            $new_post_limits = br_get_value_from_array($_POST,'limits_arr');
            if( ! is_array($new_post_limits) ) $new_post_limits = array();
            if( ! is_array($new_post_terms) ) $new_post_terms = array();
            if( is_array($new_post_terms) && count($new_post_terms) ) {
                foreach($new_post_terms as $new_post_terms_i => $new_post_term) {
                    if( $new_post_term[0] == $taxonomy ) {
                        unset($new_post_terms[$new_post_terms_i]);
                    }
                }
            }
            $limit_post__not_in = array();
            foreach($terms as $term_data) {
                $new_post_limits[$taxonomy] = array($term_data->term_id);
                $limit_post__not_in[$term_data->term_taxonomy_id] = apply_filters('berocket_add_out_of_stock_variable', array(), $new_post_terms, $new_post_limits);
            }
            
            $limit_post__not_in_where_array = array();
            if( is_array($limit_post__not_in) && count($limit_post__not_in) ) {
                $limit_post__term_id_without_product = array();
                foreach($limit_post__not_in as $wp_terms_id => $limit_post) {
                    if( is_array($limit_post) && count($limit_post) ) {
                        $limit_post__not_in_where_array[$wp_terms_id] = "({$wpdb->posts}.ID NOT IN (\"" . implode('","', $limit_post) . "\") AND term_relationships.term_taxonomy_id = {$wp_terms_id})";
                    } else {
                        $limit_post__term_id_without_product[] = $wp_terms_id;
                    }
                }
                if( count($limit_post__term_id_without_product) ) {
                    $limit_post__not_in_where_array[] = "(term_relationships.term_taxonomy_id IN (".implode(', ', $limit_post__term_id_without_product)."))";
                }
                $limit_post__not_in_where = implode(' OR ', $limit_post__not_in_where_array);
            }
            if( empty($br_options['out_of_stock_variable_single']) && ! empty($limit_post__not_in_where) ) {
                $query['where'] = berocket_insert_to_array($query['where'], 'post__not_in', array(
                    'post__not_in_variation' => " AND ({$limit_post__not_in_where})"
                ));
            }
            $this->limit_post__not_in_where_array = $limit_post__not_in_where_array;
        }
        return $query;
    }
    public function faster_recount_query_result($results, $query, $terms) {
        $limit_post__not_in_where_array = $this->limit_post__not_in_where_array;
        $this->limit_post__not_in_where_array = array();
        $br_options = BeRocket_AAPF::get_aapf_option();
        if( ! empty($br_options['out_of_stock_variable_reload']) && ! empty($br_options['out_of_stock_variable_single']) ) {
            if( isset($limit_post__not_in_where_array) && is_array($limit_post__not_in_where_array) && count($limit_post__not_in_where_array) ) {
                global $wpdb;
                foreach($limit_post__not_in_where_array as $term_id => $limit_post) {
                    $query_new = $query;
                    $query_new['where'] .= " AND ({$limit_post})";
                    $query_new          = implode( ' ', $query_new );
                    $result             = $wpdb->get_results( $query_new );
                    if( ! empty($result) && is_array($result) && count($result) ) {
                        foreach($result as $result_i) {
                            foreach($results as &$results_data) {
                                if( $results_data->term_count_id == $result_i->term_count_id ) {
                                    $results_data->term_count = $result_i->term_count;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $results;
    }
    function faster_recount_cache_key($key) {
        $br_options = BeRocket_AAPF::get_aapf_option();
        if( ! empty($br_options['out_of_stock_variable_reload']) ) {
            $key .= 'V';
        }
        if( ! empty($br_options['out_of_stock_variable_reload']) && ! empty($br_options['out_of_stock_variable_single']) ) {
            $key .= 'V';
        }
        return $key;
    }
}
new BeRocket_AAPF_compat_woocommerce_variation();
