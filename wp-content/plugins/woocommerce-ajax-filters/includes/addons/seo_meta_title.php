<?php
if( ! function_exists('BeRocket_AAPF_wcseo_title_visual1') ) {
    function BeRocket_AAPF_wcseo_title_visual1($filters, $text, $section, $terms_filtered) {
        $filters = array();
        foreach($terms_filtered as $filter_attr => $filter_values) {
            $last_item = '';
            if( count($filter_values['values']) > 1 ) {
                $last_item = array_pop($filter_values['values']);
            }
            $filters[] =  ( empty($filter_values['name']) ? '' : $filter_values['name'] . ' ' )
                . implode(', ', $filter_values['values'])
                . ( empty($last_item) ? '' :
                    (isset($filter_values['operator']) && $filter_values['operator'] == 'AND' 
                        ? __(' and ', 'BeRocket_AJAX_domain') 
                        : __(' or ', 'BeRocket_AJAX_domain')
                    ) . $last_item
                );
        }
        $filters = implode(__(' and ', 'BeRocket_AJAX_domain') , $filters);
        $text_return = $text;
        if( ! empty($filters) ) {
            $text_return .= __(' with ', 'BeRocket_AJAX_domain') . $filters;
        }
        return $text_return;
    }
}
if( ! function_exists('BeRocket_AAPF_wcseo_title_visual2') ) {
    function BeRocket_AAPF_wcseo_title_visual2($filters, $text, $section, $terms_filtered) {
        $filters = array();
        foreach($terms_filtered as $filter_attr => $filter_values) {
            $filters[] =  ( ! empty($filter_values['name']) ? $filter_values['name'] . ': ' : ''). implode(', ', $filter_values['values']);
        }
        $filters = implode('; ', $filters);
        $text_return = $text;
        if( ! empty($filters) ) {
            $text_return .= ' '. $filters;
        }
        return $text_return;
    }
}
if( ! function_exists('BeRocket_AAPF_wcseo_title_visual3') ) {
    function BeRocket_AAPF_wcseo_title_visual3($filters, $text, $section, $terms_filtered) {
        $filters = array();
        $first_attribute = '';
        foreach($terms_filtered as $filter_attr => $filter_values) {
            $last_item = '';
            if( count($filter_values['values']) > 1 ) {
                $last_item = array_pop($filter_values['values']);
            }
            $attributes_text = implode(', ', $filter_values['values'])
            . ( empty($last_item) ? '' :
                (isset($filter_values['operator']) && $filter_values['operator'] == 'AND' 
                    ? __(' and ', 'BeRocket_AJAX_domain') 
                    : __(' or ', 'BeRocket_AJAX_domain')
                ) . $last_item
            );
            if( empty($first_attribute) && empty($filter_values['has_slider']) && empty($filter_values['is_price']) ) {
                $first_attribute = $attributes_text;
            } else {
                $filters[] =  ( empty($filter_values['name']) ? '' : $filter_values['name'] . ' ' ) . $attributes_text;
            }
        }
        $filters = implode(__(' and ', 'BeRocket_AJAX_domain') , $filters);
        $text_return = $text;
        if( ! empty($first_attribute) ) {
            $text_return = $first_attribute . ' ' . $text_return;
        }
        if( ! empty($filters) ) {
            $text_return .= __(' with ', 'BeRocket_AJAX_domain') . $filters;
        }
        return $text_return;
    }
}
if( ! function_exists('BeRocket_AAPF_wcseo_title_visual4') ) {
    function BeRocket_AAPF_wcseo_title_visual4($filters, $text, $section, $terms_filtered) {
        $filters = array();
        foreach($terms_filtered as $filter_attr => $filter_values) {
            $last_item = '';
            if( count($filter_values['values']) > 1 ) {
                $last_item = array_pop($filter_values['values']);
            }
            $filters[] = implode(', ', $filter_values['values'])
                . ( empty($last_item) ? '' :
                    (isset($filter_values['operator']) && $filter_values['operator'] == 'AND' 
                        ? __(' and ', 'BeRocket_AJAX_domain') 
                        : __(' or ', 'BeRocket_AJAX_domain')
                    ) . $last_item
                );
        }
        $filters = implode(__(' / ', 'BeRocket_AJAX_domain') , $filters);
        $text_return = $text;
        if( ! empty($filters) ) {
            $text_return .= __(' - ', 'BeRocket_AJAX_domain') . $filters;
        }
        return $text_return;
    }
}
if( ! function_exists('BeRocket_AAPF_wcseo_title_visual5') ) {
    function BeRocket_AAPF_wcseo_title_visual5($filters, $text, $section, $terms_filtered) {
        $filters = array();
        foreach($terms_filtered as $filter_attr => $filter_values) {
            $filters[] =  ( ! empty($filter_values['name']) ? $filter_values['name'] . ': ' : ''). implode(', ', $filter_values['values']);
        }
        $filters = implode('; ', $filters);
        $text_return = $text;
        if( ! empty($filters) ) {
            $text_return = $filters . ' - ' . $text_return;
        }
        return $text_return;
    }
}
if( ! class_exists('BeRocket_AAPF_addon_woocommerce_seo_title') ) {
    class BeRocket_AAPF_addon_woocommerce_seo_title {
        public $terms_filtered = array();
        public $page_title = '';
        public $ready_elements =  array('title' => false, 'description' => false, 'header' => false);
        function __construct() {
            if( ! is_admin() ) {
                add_action('wp', array($this, 'plugins_loaded'), 99999999);
            }
        }
        function plugins_loaded() {
            add_action('get_header', array($this, 'get_header'));
            add_filter('document_title_parts', array($this, 'document_title_parts'));
            add_filter('wpseo_title', array($this, 'wpseo_title'), 10, 1);
            do_action('braapf_seo_meta_title', $this);
            $options = $this->get_options();
            if( ! empty($options['seo_element_header']) ) {
                add_filter('the_title', array($this, 'the_title'), 10, 2);
                add_filter('woocommerce_page_title', array($this, 'woocommerce_page_title'), 10, 2);
                do_action('braapf_seo_meta_header', $this);
            }
            if( ! empty($options['seo_element_description']) ) {
                add_filter('wpseo_metadesc', array($this, 'meta_description'));
                add_filter('aioseop_description_full', array($this, 'meta_description'));
                add_action('wp_head', array($this, 'wp_head_description'), 9000);
                do_action('braapf_seo_meta_description', $this);
            }
            if( ! function_exists($options['seo_meta_title_visual']) ) {
                $options['seo_meta_title_visual'] = 'BeRocket_AAPF_wcseo_title_visual1';
            }
            add_filter('berocket_aapf_seo_meta_filters_text_before', $options['seo_meta_title_visual'], 5, 4);
        }
        function get_header() {
            global $wp_query;
            if ( apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'get_filter_args', $wp_query ) ) {
                br_aapf_args_converter($wp_query);
                $terms_name = array();
                if( isset($_POST['terms']) && is_array($_POST['terms']) ) {
                    foreach($_POST['terms'] as $term_parsed) {
                        if( apply_filters('berocket_aapf_seo_meta_filtered_term_continue', false, $term_parsed) ) continue;
                        $taxonomy = get_taxonomy($term_parsed[0]);
                        if( ! empty($taxonomy->labels->singular_name) ) {
                            $taxonomy_label = $taxonomy->labels->singular_name;
                        } else {
                            $taxonomy_label = $taxonomy->label;
                        }
                        $taxonomy_label = apply_filters('wpml_translate_single_string', $taxonomy_label, 'WordPress', sprintf( 'taxonomy singular name: %s', $taxonomy_label ) );
                        $term = get_term($term_parsed[1], $term_parsed[0]);
                        if( ! isset($terms_name[$taxonomy->name]) ) {
                            $terms_name[$taxonomy->name] = array(
                                'name' => apply_filters('berocket_aapf_seo_meta_filtered_taxonomy_label', $taxonomy_label, $taxonomy, $term, $term_parsed), 
                                'values' => array(),
                                'operator' => $term_parsed[2],
                                'type'      => $term_parsed[4]
                            );
                        }
                        $terms_name[$taxonomy->name]['values'][$term->slug] = apply_filters('berocket_aapf_seo_meta_filtered_term_label', $term->name, $term, $taxonomy, $term_parsed);
                    }
                }
                if( isset($_POST['price']) && is_array($_POST['price']) && count($_POST['price']) > 1 ) {
                    $min_price = $this->wc_price($_POST['price'][0]);
                    $max_price = $this->wc_price($_POST['price'][1]);
                    $terms_name['wc_price'] = array(
                        'name' => apply_filters('berocket_aapf_seo_meta_filtered_taxonomy_price_label', __('Price', 'woocommerce')),
                        'values' => array(
                            'price' => apply_filters('berocket_aapf_seo_meta_filtered_price_label', wc_format_price_range($min_price, $max_price), $_POST['price'], array($min_price, $max_price))
                        ),
                        'is_price' => TRUE
                    );
                }
                if( isset($_POST['limits']) && is_array($_POST['limits']) ) {
                    foreach($_POST['limits'] as $term_parsed) {
                        if( apply_filters('berocket_aapf_seo_meta_filtered_term_continue', false, $term_parsed) ) continue;
                        $taxonomy = get_taxonomy($term_parsed[0]);
                        if( ! empty($taxonomy->labels->singular_name) ) {
                            $taxonomy_label = $taxonomy->labels->singular_name;
                        } else {
                            $taxonomy_label = $taxonomy->label;
                        }
                        $taxonomy_label = apply_filters('wpml_translate_single_string', $taxonomy_label, 'WordPress', sprintf( 'taxonomy singular name: %s', $taxonomy_label ) );
                        $term1 = get_term_by('slug', $term_parsed[1], $term_parsed[0]);
                        $term2 = get_term_by('slug', $term_parsed[2], $term_parsed[0]);
                        if( ! isset($terms_name[$taxonomy->name]) ) {
                            $terms_name[$taxonomy->name] = array(
                                'name' => apply_filters('berocket_aapf_seo_meta_filtered_taxonomy_label', $taxonomy_label, $taxonomy, array($term1, $term2), $term_parsed), 
                                'values' => array()
                            );
                        }
                        if( ! $term1 || ! $term2 ) {
                            $terms_name[$taxonomy->name]['values'][$term_parsed[1].'_'.$term_parsed[2]] = apply_filters('berocket_aapf_seo_meta_filtered_term_label', sprintf( _x( '%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce' ), $term_parsed[1], $term_parsed[2] ), array($term1, $term2), $taxonomy, $term_parsed);
                        } else {
                            $terms_name[$taxonomy->name]['values'][$term1->slug.'_'.$term2->slug] = apply_filters('berocket_aapf_seo_meta_filtered_term_label', sprintf( _x( '%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce' ), $term1->name, $term2->name ), array($term1, $term2), $taxonomy, $term_parsed);
                        }
                        $terms_name[$taxonomy->name]['has_slider'] = TRUE;
                    }
                }
                $this->terms_filtered = apply_filters('berocket_aapf_seo_meta_filtered_terms', $terms_name);
            }
        }
        public static function wc_price($price) {
            $decimal_separator  = wc_get_price_decimal_separator();
            $thousand_separator = wc_get_price_thousand_separator();
            $decimals           = wc_get_price_decimals();
            $price_format       = get_woocommerce_price_format();
            $currency           = get_woocommerce_currency_symbol();
            $price = number_format( $price, $decimals, $decimal_separator, $thousand_separator );
            return sprintf($price_format, $currency, $price);
        }
        function get_filters_string($text, $section = 'title') {
            if( empty($this->terms_filtered) ) {
                return $text;
            }
            $filters = apply_filters('berocket_aapf_seo_meta_filters_text_before', '', $text, $section, $this->terms_filtered);
            if( empty($filters) ) {
                $filters = $text;
            } else {
                return $filters;
            }
            return apply_filters('berocket_aapf_seo_meta_filters_text_return', $filters, $text, $section, $this->terms_filtered);
        }
        function the_title($title, $id = 0) {
            if( get_queried_object_id() === $id ) {
                $title = $this->get_filters_string($title, 'header');
                remove_filter('the_title', array($this, 'the_title'), 10, 2);
                remove_filter('woocommerce_page_title', array($this, 'woocommerce_page_title'), 10, 2);
                $this->ready_elements['header'] = true;
            }
            return $title;
        }
        function woocommerce_page_title($title) {
            $title = $this->get_filters_string($title, 'header');
            remove_filter('the_title', array($this, 'the_title'), 10, 2);
            remove_filter('woocommerce_page_title', array($this, 'woocommerce_page_title'), 10, 2);
            $this->ready_elements['header'] = true;
            return $title;
        }
        function document_title_parts($title) {
            $options = $this->get_options();
            if( isset($title['title']) ) {
                $this->page_title = $title['title'];
            }
            if( ! empty($options['seo_element_title']) ) {
                $title['title'] = $this->get_filters_string($title['title'], 'title');
                $this->ready_elements['title'] = true;
            }
            return $title;
        }
        function wpseo_title($title) {
            $options = $this->get_options();
            $this->page_title = $title;
            if( ! empty($options['seo_element_title']) ) {
                $title = $this->get_filters_string($title, 'title');
            }
            $this->ready_elements['title'] = true;
            return $title;
        }
        function meta_description($description) {
            remove_action('wp_head', array($this, 'wp_head_description'));
            $description = $this->get_filters_string($description, 'description');
            $this->ready_elements['description'] = true;
            return trim($description);
        }
        function wp_head_description() {
            if( ! $this->ready_elements['description'] ) {
                $description = $this->page_title;
                $description = trim($this->get_filters_string($description, 'description'));
                if( ! empty($description) ) {
                    echo '<meta name="description" content="'.$description.'">';
                }
            }
        }
        function get_options() {
            return BeRocket_AAPF::get_aapf_option();
        }
    }
    new BeRocket_AAPF_addon_woocommerce_seo_title();
}
