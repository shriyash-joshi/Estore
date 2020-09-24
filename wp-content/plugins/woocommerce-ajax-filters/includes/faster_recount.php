<?php
class BeRocket_AAPF_faster_attribute_recount {
    function __construct() {
        add_filter('berocket_aapf_recount_terms_apply', array(__CLASS__, 'recount_terms'), 10, 2);
        add_filter('berocket_aapf_recount_terms_query', array(__CLASS__, 'search_query'), 50, 3);
        add_filter('berocket_aapf_recount_terms_query', array(__CLASS__, 'date_query'), 60, 3);
        add_filter('berocket_aapf_recount_terms_query', array(__CLASS__, 'wpml_query'), 70, 3);
        //Child terms include for hierarchical taxonomy
        add_filter('berocket_aapf_recount_terms_query', array(__CLASS__, 'child_include'), 50, 3);
        //Stock Status custom recount
        add_filter('berocket_aapf_recount_terms_query', array(__CLASS__, 'stock_status_query'), 20, 3);
        //Sale Status custom recount
        add_filter('berocket_aapf_recount_terms_query', array(__CLASS__, 'onsale_query'), 20, 3);
        add_action('plugins_loaded', array(__CLASS__, 'plugins_loaded'));
    }
    static function plugins_loaded() {
        do_action('berocket_aapf_recount_terms_initialized', __CLASS__);
    }
    static function recount_terms($terms = FALSE, $taxonomy_data = array()) {
        $taxonomy_data = apply_filters('berocket_recount_taxonomy_data', array_merge(array(
            'taxonomy'      => '',
            'operator'      => 'OR',
            'use_filters'   => TRUE,
            'tax_query'     => FALSE,
            'meta_query'    => FALSE,
            'post__not_in'  => array(),
            'post__in'      => array(),
            'include_child' => TRUE,
            'additional_tax_query' => FALSE
        ), $taxonomy_data), $terms);
        global $braapf_recount_taxonomy_data;
        $braapf_recount_taxonomy_data = $taxonomy_data;
        do_action('berocket_term_recount_before_action', $terms, $taxonomy_data);
        $result = self::recount_terms_without_prepare($terms, $taxonomy_data);
        do_action('berocket_term_recount_after_action', $terms, $taxonomy_data);
        $braapf_recount_taxonomy_data = FALSE;
        return $result;
    }
    static function recount_terms_without_prepare($terms = FALSE, $taxonomy_data = array()) {
        if( BeRocket_AAPF::$debug_mode ) {
            if( empty(BeRocket_AAPF::$error_log['faster_recount_sql']) || ! is_array(BeRocket_AAPF::$error_log['faster_recount_sql']) ) {
                BeRocket_AAPF::$error_log['faster_recount_sql'] = array();
            }
        }
        extract($taxonomy_data);
        global $wpdb;
        if( $terms === FALSE ) {
            $terms = self::get_terms($taxonomy);
        }
        if( empty($terms) || is_wp_error($terms) ) {
            if( BeRocket_AAPF::$debug_mode ) {
                $taxonomy_data['error'] = 'Empty terms';
                BeRocket_AAPF::$error_log['faster_recount_sql'][] = $taxonomy_data;
            }
            return array();
        }
        $wc_main_query = WC_Query::get_main_query();
        $author = false;
        if( ! empty($wc_main_query) ) {
            if( $tax_query === FALSE ) {
                $tax_query  = WC_Query::get_main_tax_query();
            }
            if( $meta_query === FALSE ) {
                $meta_query = WC_Query::get_main_meta_query();
            }
            $author = $wc_main_query->get('author');
            if( empty($author) ) {
                $author = false;
            }
        }
        if( strtoupper($operator) == 'OR' || ! $use_filters ) {
            $tax_query = apply_filters(
                'berocket_aapf_recount_remove_all_berocket_tax_query', 
                self::remove_all_berocket_tax_query($tax_query, ($use_filters ? $taxonomy : FALSE)),
                $terms,
                $taxonomy_data,
                $tax_query
            );
            $meta_query = apply_filters(
                'berocket_aapf_recount_remove_all_berocket_meta_query', 
                $meta_query,
                $terms,
                $taxonomy_data,
                $meta_query
            );
        }
        if( ! empty($taxonomy_data['additional_tax_query']) ) {
            if( empty($tax_query) ) {
                $tax_query = array(
                    'relation' => 'AND',
                );
            }
            $tax_query['additional_tax_query'] = $taxonomy_data['additional_tax_query'];
        }

        $taxonomy_data['meta_query_ready']  = $meta_query           = new WP_Meta_Query( $meta_query );
        $taxonomy_data['tax_query_ready']   = $tax_query            = new WP_Tax_Query( $tax_query );
        $taxonomy_data['meta_query_sql']    = $meta_query_sql       = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
        $taxonomy_data['tax_query_sql']     = $tax_query_sql        = $tax_query->get_sql( $wpdb->posts, 'ID' );
        $taxonomy_data['term_taxonomy_ids'] = $term_taxonomy_ids    = wp_list_pluck($terms, 'term_taxonomy_id', 'term_id');
        if( $return_terms = apply_filters('berocket_recount_extension_enabled', false, $terms, $taxonomy_data) ) {
            if( BeRocket_AAPF::$debug_mode ) {
                $taxonomy_data['error'] = 'extension_enabled';
                $taxonomy_data['return_terms'] = $return_terms;
                BeRocket_AAPF::$error_log['faster_recount_sql'][] = $taxonomy_data;
            }
            return $return_terms;
        }

        // Generate query.
        $query = array(
            'select' => array(
                'select'    => "SELECT", 
                'elements'  => array(
                    'term_count'    => "COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count",
                    'term_count_id' => "MAX(term_relationships.term_taxonomy_id) as term_count_id",
                ),
            ),
            'from'  => "FROM {$wpdb->posts}",
            'join'  => array(
                'term_relationships' => "INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id",
                'tax_query' => $tax_query_sql['join'],
                'meta_query' => $meta_query_sql['join'],
            ),
            'where' => array(
                'where_main'        => "WHERE {$wpdb->posts}.post_type IN ( 'product' ) AND {$wpdb->posts}.post_status = 'publish'",
                'tax_query'         => $tax_query_sql['where'],
                'meta_query'        => $meta_query_sql['where'],
                'term_taxonomy_id'  => 'AND term_relationships.term_taxonomy_id IN (' . implode( ',', array_map( 'absint', $term_taxonomy_ids ) ) . ')',
                'post__not_in'      => (empty($post__not_in) ? '' : "AND {$wpdb->posts}.ID NOT IN (\"" . implode('","', $post__not_in) . "\")"),
                'post__in'          => (empty($post__in) ? '' : "AND {$wpdb->posts}.ID IN (\"" . implode('","', $post__in) . "\")"),
            ),
            'group_by' => 'GROUP BY term_relationships.term_taxonomy_id',
        );
        if( $author != false ) {
            $query['where']['author'] = "AND {$wpdb->posts}.post_author IN ({$author})";
        }
        $query             = apply_filters('berocket_aapf_recount_terms_query', $query, $taxonomy_data, $terms);
        $query['select']['elements']= implode(', ', $query['select']['elements']);
        $query['select']   = implode(' ', $query['select']);
        $query['join']     = implode(' ', $query['join']);
        $query['where']    = implode(' ', $query['where']);
        $query             = apply_filters('woocommerce_get_filtered_term_product_counts_query', $query);
        if( $use_filters ) {
            $query             = apply_filters( 'berocket_posts_clauses_recount', $query );
        }
        $query_imploded    = implode( ' ', $query );
        if( apply_filters('berocket_recount_cache_use', (! $use_filters), $taxonomy_data) ) {
            $terms_cache = br_get_cache(apply_filters('berocket_recount_cache_key', md5($query_imploded), $taxonomy_data), 'berocket_recount');
        }
        if( empty($terms_cache) ) {
            $result            = $wpdb->get_results( $query_imploded );
            $result            = apply_filters('berocket_query_result_recount', $result, $query, $terms);
            $result            = wp_list_pluck($result, 'term_count', 'term_count_id');
            foreach($terms as &$term) {
                $term->count   = (isset($result[$term->term_taxonomy_id]) ? $result[$term->term_taxonomy_id] : 0);
            }
            $terms             = apply_filters('berocket_terms_after_recount', $terms, $query, $result);
            if( apply_filters('berocket_recount_cache_use', (! $use_filters), $taxonomy_data) ) {
                br_set_cache(md5(json_encode($query_imploded)), $terms, 'berocket_recount', DAY_IN_SECONDS);
            }
        } else {
            $terms = $terms_cache;
        }
        if( BeRocket_AAPF::$debug_mode ) {
            $taxonomy_data['query_imploded']    = $query_imploded;
            $taxonomy_data['return_terms']      = $return_terms;
            $taxonomy_data['result']            = $result;
            BeRocket_AAPF::$error_log['faster_recount_sql'][] = $taxonomy_data;
        }
        return apply_filters('berocket_terms_recount_return', $terms, $taxonomy_data, $query_imploded);
    }
    static function child_include($query, $taxonomy_data, $terms) {
        global $wpdb;
        extract($taxonomy_data);
        if( $include_child ) {
            $taxonomy_object = get_taxonomy($taxonomy);
            if( ! empty($taxonomy_object->hierarchical) ) {
                $hierarchy = br_get_taxonomy_hierarchy(array('taxonomy' => $taxonomy, 'return' => 'child'));
                $join_query = "INNER JOIN (SELECT object_id,tt1id as term_taxonomy_id, term_order FROM {$wpdb->term_relationships}
                JOIN (
                    SELECT tt1.term_taxonomy_id as tt1id, tt2.term_taxonomy_id as tt2id FROM {$wpdb->term_taxonomy} as tt1
                    JOIN {$wpdb->term_taxonomy} as tt2 ON (";
                $join_list = array();
                foreach($hierarchy as $term_id => $term_child) {
                    $join_list[] = "(tt1.term_id = '{$term_id}' AND tt2.term_id IN('".implode("','", $term_child)."'))";
                }
                $join_query .= implode('
                 OR 
                 ', $join_list);
                $join_query .= ") ) as term_taxonomy 
                ON {$wpdb->term_relationships}.term_taxonomy_id = term_taxonomy.tt2id ) as term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id";
                $query['join']['term_relationships'] = $join_query;
            }
        }
        return $query;
    }
    static function search_query($query, $taxonomy_data, $terms) {
        extract($taxonomy_data);
        if( ! empty($use_filters) ) {
            $wc_main_query = WC_Query::get_main_query();
            if( ! empty($wc_main_query) ) {
                $search = WC_Query::get_main_search_query_sql();
                if ( $search ) {
                    $query['where']['search'] = 'AND ' . $search;
                }
            }
        }
        return $query;
    }
    static function date_query($query, $taxonomy_data, $terms) {
        global $wpdb;
        extract($taxonomy_data);
        if( ! empty($use_filters) ) {
            if( ! empty($_POST['limits']) && is_array($_POST['limits']) && count($_POST['limits']) ) {
                foreach($_POST['limits'] as $limit) {
                    if($limit[0] == '_date') {
                        $from = $limit[1];
                        $to = $limit[2];
                        $from = date('Y-m-d 00:00:00', strtotime($from));
                        $to = date('Y-m-d 23:59:59', strtotime($to));
                        $date_query_data = array(
                            'after' => $from,
                            'before' => $to,
                        );
                        $date_query = new WP_Date_Query( $date_query_data, 'post_date' );
                        $query['where']['date'] = $date_query->get_sql();
                        break;
                    }
                }
            }
        }
        return $query;
    }
    static function wpml_query($query, $taxonomy_data, $terms) {
        global $wpdb;
        extract($taxonomy_data);
        if( defined( 'WCML_VERSION' ) && defined('ICL_LANGUAGE_CODE') ) {
            $query['join']['wpml']  = " INNER JOIN {$wpdb->prefix}icl_translations as wpml_lang ON ( {$wpdb->posts}.ID = wpml_lang.element_id )";
            $query['where']['wpml'] = " AND wpml_lang.language_code = '".ICL_LANGUAGE_CODE."' AND wpml_lang.element_type = 'post_product'";
        }
        return $query;
    }
    static function remove_all_berocket_tax_query($tax_query, $taxonomy = FALSE, $inside = FALSE ) {
        global $wpdb;
        if( is_array($tax_query) ) {
            $md5_exist = array();
            foreach($tax_query as $key => $value) {
                if( $key === 'relation' ) continue;
                if( ! $inside ) {
                    if( in_array(md5(json_encode($value)), $md5_exist) ) {
                        unset($tax_query[$key]);
                        continue;
                    }
                    $md5_exist[] = md5(json_encode($value));
                }
                if( array_key_exists('relation', $value) ) {
                    $value = self::remove_all_berocket_tax_query($value, $taxonomy, true);
                    if( $value === FALSE ) {
                        unset($tax_query[$key]);
                    } else {
                        $tax_query[$key] = $value;
                    }
                } elseif( ! empty($value['is_berocket']) && isset($value['taxonomy']) && ($taxonomy === FALSE || $taxonomy == $value['taxonomy']) ) {
                    unset($tax_query[$key]);
                }
            }
            if( count($tax_query) == 1 && isset($tax_query['relation']) ) {
                $tax_query = ( $inside ? FALSE : array() );
            }
        }
        return $tax_query;
    }
    static function get_all_taxonomies($taxonomy = FALSE) {
        if( empty($taxonomy) ) {
            $attributes = wc_get_attribute_taxonomies();
            $taxonomy = array();
            foreach($attributes as $attribute) {
                $taxonomy[] = 'pa_'.$attribute->attribute_name;
            }
        } elseif( ! is_array($taxonomy) ) {
            $taxonomy = array($taxonomy);
        }
        return $taxonomy;
    }
    static function get_terms($taxonomy) {
        if( ! empty($taxonomy) ) {
            $terms = get_terms(array('taxonomy' => $taxonomy) );
        } else {
            $taxonomy = self::get_all_taxonomies();
            $terms = get_terms(array('taxonomy' => $taxonomy) );
        }
        return $terms;
    }
    static function stock_status_query($query, $taxonomy_data, $terms) {
        global $wpdb;
        extract($taxonomy_data);
        if( $taxonomy == '_stock_status' ) {
            $outofstock = wc_get_product_visibility_term_ids();
            if( empty($outofstock['outofstock']) ) {
                $outofstock = get_term_by( 'slug', 'outofstock', 'product_visibility' );
                $outofstock = $outofstock->term_taxonomy_id;
            } else {
                $outofstock = $outofstock['outofstock'];
            }
            $join_query = "INNER JOIN (SELECT {$wpdb->posts}.ID as object_id, IF({$wpdb->term_relationships}.term_taxonomy_id = {$outofstock}, 2, 1) as term_taxonomy_id, 0 as term_order FROM {$wpdb->posts}
            LEFT JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id AND {$wpdb->term_relationships}.term_taxonomy_id = {$outofstock}
            WHERE {$wpdb->posts}.post_type = 'product') as term_relationships
            ON {$wpdb->posts}.ID = term_relationships.object_id";
            $query['join']['term_relationships'] = $join_query;
        }
        return $query;
    }
    static function onsale_query($query, $taxonomy_data, $terms) {
        global $wpdb;
        extract($taxonomy_data);
        if( $taxonomy == '_sale' ) {
            $join_query = "INNER JOIN (";
            /*if( ! empty($wpdb->wc_product_meta_lookup) ) {
                $join_query .= "SELECT {$wpdb->wc_product_meta_lookup}.product_id as object_id, IF({$wpdb->wc_product_meta_lookup}.onsale = 1, 1, 2) as term_taxonomy_id, 0 as term_order FROM {$wpdb->wc_product_meta_lookup}";
            } else {*/
                $products_id = wc_get_product_ids_on_sale();
                $products_id[] = 0;
                $join_query .= "SELECT {$wpdb->posts}.ID as object_id, IF({$wpdb->posts}.ID IN (".implode(',', $products_id)."), 1, 2) as term_taxonomy_id, 0 as term_order FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = 'product'";
            //}
            $join_query .= ") as term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id";
            $query['join']['term_relationships'] = $join_query;
        }
        return $query;
    }
}
new BeRocket_AAPF_faster_attribute_recount();
