<?php
class BeRocket_AAPF_link_parser {
    public $js_parse_result     = false;
    public $js_generate_result  = false;
    public $php_parse_result    = false;
    public $php_generate_result = false;
    public $php_remove_result   = false;
    public $using_slug = false;
    public $taxonomy_changer = array();
    function __construct() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        $this->using_slug = ! empty($option['slug_urls']);
        $this->taxonomy_changer = apply_filters('BR_AAPF_link_parser_taxonomy_changer', array(
            '_stock_status' => array(
                'taxonomy'  => '_stock_status',
                'terms'     => array(
                    0 => '',
                    1 => 'instock',
                    2 => 'outofstock'
                )
            ),
            '_sale'         => array(
                'taxonomy'  => '_sale',
                'terms'     => array(
                    0 => '',
                    1 => 'sale',
                    2 => 'notsale'
                )
            ),
            '_rating'       => array(
                'taxonomy' => 'product_visibility',
            ),
            'price'         => array(
                'taxonomy' => 'price'
            )
        ), $this);
        add_filter('BR_AAPF_link_parser_jsp', array($this, 'js_parse'), 10, 2);
        add_filter('BR_AAPF_link_parser_jsg', array($this, 'js_generate'), 10, 2);
        add_filter('BR_AAPF_link_parser_jsr', array($this, 'js_generate'), 10, 2);
        add_filter('BR_AAPF_link_parser_phpp', array($this, 'php_parse'), 10, 2);
        add_filter('BR_AAPF_link_parser_phpg', array($this, 'php_generate'), 10, 2);
        add_filter('BR_AAPF_link_parser_phpr', array($this, 'php_remove'), 10, 2);
        remove_all_filters('berocket_add_filter_to_link', 100);
        add_filter('berocket_add_filter_to_link', array($this, 'add_filter_to_link'), 10, 2);
    }
    function js_parse($data, $args = array()) {
        if( $this->js_parse_result === false || ! empty($args['force']) ) {
            if( ! is_array($data) ) {
                $data = array();
            }
            $this->js_parse_result = $this->js_parse_inside($data, $args);
        } 
        return $this->js_parse_result;
    }
    function js_generate($data, $args = array()) {
        if( $this->js_generate_result === false || ! empty($args['force']) ) {
            if( ! is_array($data) ) {
                $data = array();
            }
            $this->js_generate_result = $this->js_generate_inside($data, $args);
        } 
        return $this->js_generate_result;
    }
    function php_parse($data, $args = array()) {
        if( $this->php_parse_result === false || ! empty($args['force']) ) {
            if( ! is_array($data) ) {
                $data = array();
            }
            $this->php_parse_result = $this->php_parse_inside($data, $args);
        } 
        return $this->php_parse_result;
    }
    function php_generate($data, $args = array()) {
        if( ! is_array($data) ) {
            $data = array();
        }
        $this->php_generate_result = $this->php_generate_inside($data, $args);
        return $this->php_generate_result;
    }
    function php_remove($data, $args = array()) {
        if( ! is_array($data) ) {
            $data = array();
        }
        $this->php_remove_result = $this->php_remove_inside($data, $args);
        return $this->php_remove_result;
    }
    function js_parse_inside($data, $args = array()) {
        return $data;
    }
    function js_generate_inside($data, $args = array()) {
        return $data;
    }
    function js_remove_filters($data, $args = array()) {
        return $data;
    }
    function php_parse_inside($data, $args = array()) {
        return $data;
    }
    function php_generate_inside($data, $args = array()) {
        return $data;
    }
    function php_remove_inside($data, $args = array()) {
        $link_data = $this->get_query_vars_name();
        return remove_query_arg($link_data['var_names'], $this->get_query_vars_name_link(br_get_value_from_array($args, array('link'), false)));
    }
    function get_query_vars_name_link($link = false) {
        if( $link === false ) {
            $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        return $link;
    }
    function get_query_vars_name($link = false) {
        return array();
    }
    function check_taxonomy($taxonomy) {
        if( taxonomy_exists( 'pa_'.$taxonomy ) ) {
            return 'pa_'.$taxonomy;
        } elseif( taxonomy_exists( $taxonomy ) ) {
            return $taxonomy;
        } elseif ( array_key_exists($taxonomy, $this->taxonomy_changer) ) {
            return $this->taxonomy_changer[$taxonomy]['taxonomy'];
        }
        return false;
    }
    function get_term_taxonomy($taxonomy, $term) {
        if ( array_key_exists($taxonomy, $this->taxonomy_changer) ) {
            $term_data = array(
                'term_group'    => 0,
                'taxonomy'      => $taxonomy,
                'description'   => '',
                'parent'        => 0,
                'count'         => 1
            );
            $terms = $this->taxonomy_changer[$taxonomy]['terms'];
            if( $this->using_slug ) {
                $term_data['slug']      = $term_data['name']                = $term;
                $term_data['term_id']   = $term_data['term_taxonomy_id']    = array_search( $term, $terms );
            } else {
                $term_data['term_id']   = $term_data['term_taxonomy_id']    = $term;
                $term_data['slug']      = $term_data['name']                = $terms[$term];
            }
            $term_data = (object)$term_data;
        } else {
            $term_data = get_term_by( ($this->using_slug ? 'slug' : 'term_id'), $term, $taxonomy, 'OBJECT' );
        }
        return $term_data;
    }
    function add_filter_to_link($current_url = FALSE, $args = array()) {
        if( $current_url === FALSE ) {
            $current_url = $this->get_query_vars_name_link();
        }
        return $current_url;
    }
}
