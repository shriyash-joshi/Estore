<?php
class BeRocket_aapf_variations_tables {
    function __construct() {
        add_filter('berocket_aapf_wcvariation_filtering_total_query', array($this, 'wcvariation_filtering_total_query'), 10, 4);
        add_filter('berocket_aapf_wcvariation_filtering_main_query', array($this, 'wcvariation_filtering_main_query'), 10, 4);
        add_action( 'woocommerce_variation_set_stock_status', array($this, 'set_stock_status'), 10, 3 );
        add_action( 'woocommerce_product_set_stock_status', array($this, 'set_stock_status'), 10, 3 );
        add_action( 'delete_post', array($this, 'delete_post'), 10, 1 );
        add_action( 'woocommerce_after_product_object_save', array($this, 'variation_object_save'), 10, 1 );
        //hierarhical recount custom table
        add_action('berocket_aapf_recount_terms_initialized', array($this, 'recount_terms_initialized'), 10, 1);
    }
    function wcvariation_filtering_main_query($query, $input, $terms, $limits) {
        $current_terms = array(0);
        if( is_array($terms) && count($terms) ) {
            foreach($terms as $term) {
                if( substr( $term[0], 0, 3 ) == 'pa_' ) {
                    $current_terms[] = $term[1];
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
                            $current_terms[] = $term->term_id;
                        }
                    }
                }
            }
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'braapf_product_variation_attributes';
        $query = array(
            'select'    => 'SELECT '.$table_name.'.post_id as var_id, '.$table_name.'.parent_id as ID, COUNT('.$table_name.'.post_id) as meta_count',
            'from'      => 'FROM '.$table_name,
            'where'     => 'WHERE '.$table_name.'.meta_value_id IN ('.implode(',', $current_terms).')',
            'group'     => 'GROUP BY '.$table_name.'.post_id'
        );
        return $query;
    }
    function wcvariation_filtering_total_query($query, $input, $terms, $limits) {
        $current_attributes = array();
        if( is_array($terms) && count($terms) ) {
            foreach($terms as $term) {
                if( substr( $term[0], 0, 3 ) == 'pa_' ) {
                    $current_attributes[] = sanitize_title($term[0]);
                }
            }
        }
        if( is_array($limits) && count($limits) ) {
            foreach($limits as $attr => $term_ids) {
                if( substr( $attr, 0, 3 ) == 'pa_' ) {
                    $current_attributes[] = sanitize_title($attr);
                }
            }
        }
        $current_attributes = array_unique($current_attributes);
        global $wpdb;
        $query_custom = array(
            'select'    => "SELECT {$wpdb->prefix}braapf_product_stock_status_parent.post_id as id, IF({$wpdb->prefix}braapf_product_stock_status_parent.stock_status = 1, 0, 1) as out_of_stock_init",
            'from'      => "FROM {$wpdb->prefix}braapf_product_stock_status_parent",
        );
        $query['subquery']['subquery_2'] = array(
            'select' => 'SELECT post_id as ID, COUNT(post_id) as max_meta_count',
            'from'   => "FROM {$wpdb->prefix}braapf_variation_attributes",
            'where'  => "WHERE taxonomy IN ('".implode("','", $current_attributes)."')",
            'group'  => 'GROUP BY post_id',
        );
        $query['subquery']['join_close_1'] = ') as max_filtered_post ON max_filtered_post.ID = filtered_post.ID';
        $query['subquery']['select'] = 'SELECT filtered_post.*, max_filtered_post.max_meta_count, IF(max_filtered_post.max_meta_count != filtered_post.meta_count OR stock_table.out_of_stock_init = 1, 1, 0) as out_of_stock';
        /*if ( ! empty($_POST['price_ranges']) || ! empty($_POST['price']) ) {
            $query_custom['join'] = "JOIN {$wpdb->prefix}wc_product_meta_lookup as wc_product_meta_lookup ON wc_product_meta_lookup.product_id = {$wpdb->prefix}braapf_product_stock_status_parent.post_id";
            $query_custom['where_open'] = 'WHERE';
            if ( ! empty($_POST['price']) ) {
                $min = isset( $_POST['price'][0] ) ? floatval( $_POST['price'][0] ) : 0;
                $max = isset( $_POST['price'][1] ) ? floatval( $_POST['price'][1] ) : 9999999999;
                $query_custom['where_1'] = $wpdb->prepare(
                    'wc_product_meta_lookup.min_price < %f AND wc_product_meta_lookup.max_price > %f ',
                    $min,
                    $max
                );
            } else {
                $price_ranges = array();
                foreach ( $_POST['price_ranges'] as $range ) {
                    $range = explode( '*', $range );
                    $min = isset( $range[0] ) ? floatval( ($range[0] - 1) ) : 0;
                    $max = isset( $range[1] ) ? floatval( $range[1] ) : 0;
                    $price_ranges[] = $wpdb->prepare(
                        'wc_product_meta_lookup.min_price < %f AND wc_product_meta_lookup.max_price > %f ',
                        $min,
                        $max
                    );
                }
                $query_custom['where_1'] = implode(' AND ', $price_ranges);
            }
        }*/
        $query_custom['group'] = 'GROUP BY id';
        $query['subquery']['subquery_3'] = $query_custom;
        return $query;
    }
    function delete_post($product_id) {
        global $wpdb;
        $sql = "DELETE FROM {$wpdb->prefix}braapf_product_stock_status_parent WHERE post_id={$product_id};";
        $wpdb->query($sql);
        $sql = "DELETE FROM {$wpdb->prefix}braapf_product_stock_status_parent WHERE parent_id={$product_id};";
        $wpdb->query($sql);
        $sql = "DELETE FROM {$wpdb->prefix}braapf_product_variation_attributes WHERE post_id={$product_id};";
        $wpdb->query($sql);
        $sql = "DELETE FROM {$wpdb->prefix}braapf_product_variation_attributes WHERE parent_id={$product_id};";
        $wpdb->query($sql);
        $sql = "DELETE FROM {$wpdb->prefix}braapf_variation_attributes WHERE post_id={$product_id};";
        $wpdb->query($sql);
    }
    function set_stock_status($product_id, $stock_status, $product) {
        global $wpdb;
        $parent = wp_get_post_parent_id($product_id);
        $stock_status_int = ($stock_status == 'instock' ? 1 : 0);
        $sql = "INSERT IGNORE INTO {$wpdb->prefix}braapf_product_stock_status_parent (post_id, parent_id, stock_status) VALUES({$product_id}, {$parent}, {$stock_status_int}) ON DUPLICATE KEY UPDATE stock_status={$stock_status_int}";
        $wpdb->query($sql);
        
        if ( $product->get_manage_stock() ) {
            $children = $product->get_children();
            if ( $children ) {
                $status           = $product->get_stock_status();
                $format           = array_fill( 0, count( $children ), '%d' );
                $query_in         = '(' . implode( ',', $format ) . ')';
                $managed_children = array_unique( $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_manage_stock' AND meta_value != 'yes' AND post_id IN {$query_in}", $children ) ) );
                foreach ( $managed_children as $managed_child ) {
                    $sql = "INSERT IGNORE INTO {$wpdb->prefix}braapf_product_stock_status_parent (post_id, parent_id, stock_status) VALUES({$managed_child}, {$product_id}, {$stock_status_int}) ON DUPLICATE KEY UPDATE stock_status={$stock_status_int}";
                    $wpdb->query($sql);
                }
            }
        }
    }
    function variation_object_save($product) {
        $product_id = $product->get_id();
        $product_type = $product->get_type();
        if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
            $current_language = apply_filters( 'wpml_current_language', NULL );
            $language_code = apply_filters('wpml_element_language_code', NULL, array('element_id' => $product_id, 'element_type' => ( $product_type == 'variation' ? 'product_variation' : 'product' ) ));
            do_action( 'wpml_switch_language', $language_code );
        }
        if( $product_type == 'variation' ) {
            global $wpdb;
            $parent_id = $product->get_parent_id();
            $product_attributes = $product->get_variation_attributes();
            $parent_product = wc_get_product($parent_id);
            $sql = "DELETE FROM {$wpdb->prefix}braapf_product_variation_attributes WHERE post_id={$product_id};";
            $wpdb->query($sql);
            foreach($product_attributes as $taxonomy => $attributes) {
                $taxonomy = str_replace('attribute_', '', $taxonomy);
                if( empty($attributes) ) {
                    $attributes = $parent_product->get_variation_attributes();
                    if( isset($attributes[$taxonomy]) ) {
                        $attributes = $attributes[$taxonomy];
                    } else {
                        $attributes = array();
                    }
                } elseif( ! is_array($attributes) ) {
                    $attributes = array($attributes);
                }
                foreach($attributes as $attribute) {
                    $term = get_term_by('slug', $attribute, $taxonomy);
                    if( $term !== false ) {
                        $sql = "INSERT IGNORE INTO {$wpdb->prefix}braapf_product_variation_attributes (post_id, parent_id, meta_key, meta_value_id) VALUES({$product_id}, {$parent_id}, '{$taxonomy}', {$term->term_id})";
                        $wpdb->query($sql);
                    }
                }
            }
        } elseif( $product_type == 'variable' ) {
            foreach ( $product->get_children() as $child_id ) {
                $variation = wc_get_product( $child_id );
                if ( ! $variation || ! $variation->exists() || $variation->get_type() != 'variation' ) {
                    continue;
                }
                $this->variation_object_save($variation);
            }
            global $wpdb;
            $sql = "DELETE FROM {$wpdb->prefix}braapf_variation_attributes WHERE post_id={$product_id};";
            $wpdb->query($sql);
            $sql = "INSERT IGNORE INTO {$wpdb->prefix}braapf_variation_attributes
            SELECT parent_id as post_id, meta_key as taxonomy
            FROM {$wpdb->prefix}braapf_product_variation_attributes
            WHERE parent_id={$product_id}
            GROUP BY meta_key, parent_id";
            $wpdb->query($sql);
        }
        if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
            do_action( 'wpml_switch_language', $current_language );
        }
    }
    function recount_terms_initialized($recount_object) {
        remove_filter('berocket_aapf_recount_terms_query', array($recount_object, 'child_include'), 50, 3);
        add_filter('berocket_aapf_recount_terms_query', array($this, 'child_include'), 50, 3);
    }
    function child_include($query, $taxonomy_data, $terms) {
        global $wpdb;
        extract($taxonomy_data);
        if( $include_child ) {
            $taxonomy_object = get_taxonomy($taxonomy);
            if( ! empty($taxonomy_object->hierarchical) ) {
                $this->set_hierarhical_data_to_table($taxonomy);
                $table_name = $wpdb->prefix . 'braapf_term_taxonomy_hierarchical';
                $join_query = "INNER JOIN (SELECT object_id,term_taxonomy.term_taxonomy_id as term_taxonomy_id, term_order FROM {$wpdb->term_relationships}
                JOIN $table_name as term_taxonomy 
                ON {$wpdb->term_relationships}.term_taxonomy_id = term_taxonomy.term_taxonomy_child_id ) as term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id";
                $query['join']['term_relationships'] = $join_query;
            }
        }
        return $query;
    }
    function set_hierarhical_data_to_table($taxonomy) {
        global $wpdb;
        $newmd5 = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MD5(GROUP_CONCAT(CONCAT(tt.term_taxonomy_id, tt.term_id, tt.parent, tt.count))) FROM $wpdb->term_taxonomy AS tt 
                WHERE tt.taxonomy IN (%s)",
                $taxonomy
            )
        );
        $newmd5 = apply_filters('BRaapf_cache_check_md5', $newmd5, 'br_generate_child_relation', $taxonomy);
        $md5 = get_option(apply_filters('br_aapf_md5_cache_text', 'br_custom_table_hierarhical_'.$taxonomy));
        if($md5 != $newmd5) {
            $categories_ids = $this->get_terms_all(array('taxonomy' => $taxonomy, 'hide_empty' => false, 'suppress_filter' => true));
            if( empty($categories_ids) || is_wp_error($categories_ids) ) {
                return;
            }
            $hierarchy = $this->taxonomy_hierarchical_get($taxonomy);
            $new_categories = array();
            foreach($categories_ids as $categories_id) {
                $new_categories[$categories_id->term_id] = $categories_id;
            }
            unset($categories_ids);
            $table_name = $wpdb->prefix . 'braapf_term_taxonomy_hierarchical';
            $wpdb->query("DELETE FROM $table_name WHERE taxonomy = '$taxonomy';");
            $join_query = "INSERT IGNORE INTO $table_name VALUES ";
            $join_list = array();
            $drop_query = array();
            $count = 0;
            foreach($hierarchy as $term_id => $term_childs) {
                foreach($term_childs as $term_child) {
                    if( ! empty($new_categories[$term_id]) && ! empty($new_categories[$term_child]) ) {
                        $drop_query[] = sprintf("(%d,%d,%d,%d,'%s')", 
                            $new_categories[$term_child]->term_taxonomy_id,
                            $new_categories[$term_child]->term_id,
                            $new_categories[$term_id]->term_taxonomy_id,
                            $new_categories[$term_id]->term_id,
                            $new_categories[$term_id]->taxonomy);
                        $count++;
                        if( $count > 100 ) {
                            $join_list[] = $drop_query;
                            $drop_query = array();
                            $count = 0;
                        }
                    }
                }
            }
            $join_list[] = $drop_query;
            unset($drop_query, $count);
            foreach($join_list as $drop_query) {
                if( empty($drop_query) ) continue;
                $drop_query = implode(',', $drop_query);
                $drop_query = $join_query . $drop_query;
                $wpdb->query($drop_query);
            }
            update_option(apply_filters('br_aapf_md5_cache_text', 'br_custom_table_hierarhical_'.$taxonomy), $newmd5);
        }
    }
    function taxonomy_hierarchical_get($taxonomy) {
        $terms = $this->get_terms_all(array(
            'hide_empty'        => false,
            'taxonomy'          => $taxonomy,
            'suppress_filter'   => true
        ));
        $term_id_terms = array();
        foreach($terms as $term) {
            $term_id_terms[$term->term_id] = $term;
        }
        unset($terms);
        foreach($term_id_terms as $term_id => $term) {
            $term_id_terms = $this->find_all_parent($term_id_terms, $term_id);
        }
        foreach($term_id_terms as $term_id => $term) {
            $term_id_terms[$term_id] = $term->all_parents;
        }
        return $term_id_terms;
    }
    function find_all_parent($terms, $i) {
        if( ! empty($terms[$i]->all_parents) ) {
            return $terms;
        }
        $ids = array();
        $ids[] = $terms[$i]->term_id;
        if( $terms[$i]->parent != 0 && isset($terms[$terms[$i]->parent]) ) {
            if( empty($terms[$terms[$i]->parent]->all_parents) ) {
                $terms = $this->find_all_parent($terms, $terms[$i]->parent);
            }
            $ids = array_merge($ids, $terms[$terms[$i]->parent]->all_parents);
        }
        $terms[$i]->all_parents = $ids;
        return $terms;
    }
    function get_terms_all($args) {
        //WPML Compatibility Part
        $languages = apply_filters('wpml_active_languages', array());
        $wpml_active_languages = apply_filters('wpml_current_language', NULL);
        if( is_array($languages) && count($languages) && $wpml_active_languages != NULL ) {
            $terms = array();
            foreach($languages as $language_code => $language) {
                do_action( 'wpml_switch_language', $language_code );
                $single_lang_terms = get_terms($args);
                if( is_array($single_lang_terms) ) {
                    $terms = array_merge($terms, $single_lang_terms);
                }
            }
            do_action( 'wpml_switch_language', $wpml_active_languages );
        } elseif( function_exists('pll_current_language') ) {
            //Polylang Compatibility Part
            $args['lang'] = '';
            $terms = get_terms($args);
        } else {
            $terms = get_terms($args);
        }
        return $terms;
    }
}
new BeRocket_aapf_variations_tables();
