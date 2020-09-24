<?php
class BeRocket_AAPF_lp_separate_vars extends BeRocket_AAPF_link_parser {
    function __construct() {
        parent::__construct();
        if( ! is_admin() ) {
            add_filter('brapf_args_converter_get_string', array($this, 'php_parse_inside_filters'), 9000000);
            add_filter('berocket_aapf_is_filtered_page_check', array($this, 'php_parse_inside_test'), 9000000);
            add_action('wp_footer', array($this, 'js_footer_new_func'));
            add_action( 'braapf_wp_enqueue_script_after', array($this, 'js_generate_new'), 10, 1 );
        }
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        add_filter('brfr_data_ajax_filters', array($this, 'brfr_data'), 50, 1);
        if( ! empty( $option['use_links_filters'] ) ) {
            add_action( 'current_screen', array( $this, 'register_permalink_option' ), 50 );
        }
        add_filter('aapf_localize_widget_script', array($this, 'localize_widget_script'), 900);
    }
    function register_permalink_option() {
        global $wp_settings_sections;
        if( isset($wp_settings_sections[ 'permalink' ][ 'berocket_permalinks' ]) ) {
            unset($wp_settings_sections[ 'permalink' ][ 'berocket_permalinks' ]);
        }
    }
    function brfr_data($data) {
        if( isset($data['SEO']['nice_urls']) ) {
            unset($data['SEO']['nice_urls']);
        }
        if( isset($data['SEO']['seo_uri_decode']) ) {
            unset($data['SEO']['seo_uri_decode']);
        }
        $data['SEO']['default_operator_and'] = array(
                                "label"     => __( 'Default operator for URLs', "BeRocket_AJAX_domain" ),
                                "name"     => "default_operator_and",   
                                "type"     => "selectbox",
                                "options"  => array(
                                    array('value' => '', 'text' => __('OR', 'BeRocket_AJAX_domain')),
                                    array('value' => '1', 'text' => __('AND', 'BeRocket_AJAX_domain')),
                                ),
                                "value"    => '',
            'label_for' => __('Default operator will not be added to the URL', 'BeRocket_AJAX_domain'),
        );
        return $data;
    }
    function add_filter_to_link($current_url = FALSE, $args = array()) {
        $args = array_merge(array(
            'attribute'         => '',
            'values'            => array(),
            'operator'          => 'OR',
            'remove_attribute'  => FALSE,
            'slider'            => FALSE
        ), $args);
        extract($args);
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( ! is_array($values) ) {
            $values = array($values);
        }
        if( taxonomy_is_product_attribute($attribute) && substr($attribute, 0, 3) == 'pa_' ) {
            $attribute = substr($attribute, 3);
        }
        
        $current_url = $this->get_query_vars_name_link($current_url);
        
        $link_data = $this->get_query_vars_name($current_url);
        $new_url = $current_url;
        if( $slider && count($values) == 2 ) {
            $values = array_values($values);
            $get_key1 = 'pa-'.$attribute.'_from';
            $get_key2 = 'pa-'.$attribute.'_to';
            $taxonomy_value1 = $values[0];
            $taxonomy_value2 = $values[1];
            $new_url = add_query_arg(array($get_key1 => $taxonomy_value1, $get_key2 => $taxonomy_value2), $new_url);
        } else {
            $taxonomy_value = implode(',', $values);
            $get_key = 'pa-'.$attribute;
            foreach($link_data['taxonomy'] as $taxonomy) {
                if( $taxonomy['get_key'] == $attribute ) {
                    $terms = $taxonomy['data']['terms'] ;
                    $terms = explode(',', $terms);
                    foreach($values as $value) {
                        if( ($position = array_search($value, $terms)) === FALSE ) {
                            $terms[] = $value;
                        } else {
                            unset($terms[$position]);
                        }
                    }
                    $taxonomy_value = implode(',', $terms);
                    $get_key = 'pa-'.$taxonomy['get_key'];
                }
            }
            if( empty($taxonomy_value) ) {
                $new_url = add_query_arg(array($get_key => null, $get_key.'_operator' => null), $new_url);
            } else {
                $operator_set = $operator;
                if( $operator == (empty($options['default_operator_and']) ? 'OR' : 'AND') ) {
                    $operator_set = null;
                }
                $new_url = add_query_arg(array($get_key => $taxonomy_value, $get_key.'_operator' => $operator_set), $new_url);
            }
        }
        return $new_url;
    }
    function js_parse_inside($data, $args = array()) {
        return $data;
    }
    function js_footer_new_func() {
        echo '<script>function newUpdateLocation( args, pushstate, return_request ){';
        echo $this->js_generate_inside('');
        echo '}</script>';
    }
    function js_generate_inside($data, $args = array()) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        ob_start();
        ?>
            if ( typeof return_request == 'undefined' ) return_request = false;
            uri_request_array = [];
            var uri_request = '';
            temp_terms = [];
            var taxonomy_sparator = "|", start_terms = "[", end_terms = "]", variable = 'filters';

            if (typeof the_ajax_script.nn_url_variable != "undefined" && the_ajax_script.nn_url_variable.length > 0) {
                variable = the_ajax_script.nn_url_variable;
            }
            if (typeof the_ajax_script.nn_url_value_1 != "undefined" && the_ajax_script.nn_url_value_1.length > 0) {
                start_terms = the_ajax_script.nn_url_value_1;
                end_terms = the_ajax_script.nn_url_value_2;
            }
            if (typeof the_ajax_script.nn_url_split != "undefined" && the_ajax_script.nn_url_split.length > 0) {
                taxonomy_sparator = the_ajax_script.nn_url_split;
            }

            if( the_ajax_script.nice_urls ) {
                taxonomy_sparator = the_ajax_script.nice_url_split;
                start_terms = the_ajax_script.nice_url_value_1;
                end_terms = the_ajax_script.nice_url_value_2;
                variable = the_ajax_script.nice_url_variable;
            }

            if( args.price ){
                $price_obj = jQuery('.berocket_filter_price_slider');
                if( ( args.price[0] || args.price[0] === 0 ) && ( args.price[1] || args.price[1] === 0 ) && ( args.price[0] != $price_obj.data('min') || args.price[1] != $price_obj.data('max') ) ){
                    if( uri_request ) uri_request += taxonomy_sparator;
                    uri_request += 'pa-price_from='+args.price[0]+'&pa-price_to='+args.price[1];
                }
            }

            if( args.limits ){
                jQuery(args.limits).each(function (i,o){
                    if( o[0].substring(0, 3) == 'pa_' ) {
                        if( !berocket_in_array( o[0].substring(3), temp_terms ) ){
                            temp_terms[temp_terms.length] = o[0].substring(3);
                        }
                        if( typeof uri_request_array[berocket_in_array( o[0].substring(3), temp_terms )] == 'undefined' ) {
                            uri_request_array[berocket_in_array(o[0].substring(3), temp_terms)] = [];
                        }
                        uri_request_array[berocket_in_array( o[0].substring(3), temp_terms )]
                            [uri_request_array[berocket_in_array( o[0].substring(3), temp_terms )].length] = [o[1],o[2]];
                    } else {
                        if( !berocket_in_array( o[0], temp_terms ) ){
                            temp_terms[temp_terms.length] = o[0];
                        }
                        if( typeof uri_request_array[berocket_in_array( o[0], temp_terms )] == 'undefined' ) {
                            uri_request_array[berocket_in_array(o[0], temp_terms)] = [];
                        }
                        uri_request_array[berocket_in_array( o[0], temp_terms )]
                            [uri_request_array[berocket_in_array( o[0], temp_terms )].length] = [o[1],o[2]];
                    }
                });
            }
            if( args.terms ){
                jQuery(args.terms).each(function (i,o){
                    if ( the_ajax_script.slug_urls ) {
                        o[1] = o[3];
                    }
                    if( o[0].substring(0, 3) == 'pa_' ) {
                        if( !berocket_in_array( o[0].substring(3), temp_terms ) ){
                            temp_terms[temp_terms.length] = o[0].substring(3);
                        }
                        if( typeof uri_request_array[berocket_in_array( o[0].substring(3), temp_terms )] == 'undefined' ) {
                            uri_request_array[berocket_in_array(o[0].substring(3), temp_terms)] = [];
                        }
                        uri_request_array[berocket_in_array( o[0].substring(3), temp_terms )]
                            [uri_request_array[berocket_in_array( o[0].substring(3), temp_terms )].length] = [o[1],o[2]];
                    } else {
                        if( !berocket_in_array( o[0], temp_terms ) ){
                            temp_terms[temp_terms.length] = o[0];
                        }
                        if( typeof uri_request_array[berocket_in_array( o[0], temp_terms )] == 'undefined' ) {
                            uri_request_array[berocket_in_array(o[0], temp_terms)] = [];
                        }
                        uri_request_array[berocket_in_array( o[0], temp_terms )]
                            [uri_request_array[berocket_in_array( o[0], temp_terms )].length] = [o[1],o[2]];
                    }
                });
            }

            if( uri_request_array.length ) {
                jQuery(uri_request_array).each(function (i,o){
                    if( uri_request ) uri_request += '&';

                    if( typeof o != 'object' ){
                        if( the_ajax_script.seo_uri_decode ) {
                            uri_request += encodeURIComponent( o );
                        } else {
                            uri_request += o;
                        }
                    }else{
                        cnt_oo = false;
                        var element_uri_request = '';
                        var temp_term_name = temp_terms[i];
                        if( the_ajax_script.seo_uri_decode ) {
                            temp_term_name = encodeURIComponent( temp_term_name );
                        }
                        temp_term_name = 'pa-'+temp_term_name;

                        jQuery(o).each(function (ii,oo){
                            if( ( oo[1] == 'AND' || oo[1] == 'OR' ) ){
                                if (! element_uri_request) {
                                    if(oo[1] == '<?php echo (empty($options['default_operator_and']) ? 'AND' : 'OR'); ?>'){
                                        element_uri_request += temp_term_name+'_operator=<?php echo (empty($options['default_operator_and']) ? 'and' : 'or'); ?>&';
                                    }
                                    element_uri_request += temp_term_name + '=';
                                }
                                if( cnt_oo ){
                                    if( the_ajax_script.seo_uri_decode ) {
                                        element_uri_request += encodeURIComponent(',');
                                    } else {
                                        element_uri_request += ',';
                                    }
                                }
                                if( the_ajax_script.seo_uri_decode ) {
                                    element_uri_request += encodeURIComponent(oo[0]);
                                } else {
                                    element_uri_request += oo[0];
                                }
                            }else{
                                element_uri_request += temp_term_name+'_from='+oo[0]+'&'+temp_term_name+'_to='+oo[1];
                            }
                            cnt_oo = true;
                        });
                        uri_request += element_uri_request;
                    }
                });
            }
            uri_request = uri_request;

            if( !pushstate ) {
                return uri_request;
            }

            var uri = the_ajax_script.current_page_url;
            if ( /\?/.test(uri) ) {
                passed_vars1 = uri.split('?');
                uri = passed_vars1[0];
            }
            if( uri && uri.slice(-1) != '/' && ( the_ajax_script.trailing_slash ) ) {
                uri += '/';
            }

            var cur_page = jQuery(the_ajax_script.pagination_class).find('.current').first().text();
            var paginate_regex = new RegExp(".+\/"+the_ajax_script.pagination_base+"\/([0-9]+).+", "i");
            if( prev_page = parseInt( location.href.replace(paginate_regex, "$1") ) ) {
                if( ! parseInt( cur_page ) ){
                    cur_page = prev_page;
                }
            }
            if(berocket_aapf_widget_first_page_jump && the_ajax_script.first_page)   {
                cur_page = 1;
            }
            cur_page = parseInt( cur_page );
            var additional_datas = '';
            something_added = false;
            if( /\?/.test(location.href) ){
                passed_vars1 = location.href;
                if ( /\#/.test(passed_vars1) ) {
                    passed_vars1 = passed_vars1.split('#');
                    passed_vars1 = passed_vars1[0];
                }
                passed_vars1 = passed_vars1.split('?');
                if( passed_vars1[1] ){
                    passed_vars2 = [];
                    if( /&/.test(passed_vars1[1]) ) {
                        passed_vars2 = passed_vars1[1].split('&');
                    } else {
                        passed_vars2[0] = passed_vars1[1];
                    }
                    passed_vars2_length = passed_vars2.length;
                    for ( k = 0; k < passed_vars2.length; k++ ) {
                        temp = passed_vars2[k].split('=');
                        passed_vars2[k] = [];
                        passed_vars2[k][0] = temp.shift();
                        passed_vars2[k][1] = temp.join("=");
                        if( passed_vars2[k][0].substr(0, 3) == 'pa-' || passed_vars2[k][0] == 'page'  || passed_vars2[k][0] == 'paged' || passed_vars2[k][0] == 'product-page' ) continue;

                        if( the_ajax_script.control_sorting && passed_vars2[k][0] == 'orderby' ) continue;

                        if( something_added ) {
                            additional_datas += '&';
                        } else {
                            additional_datas += '?';
                        }

                        additional_datas += passed_vars2[k][0]+'='+passed_vars2[k][1];
                        something_added = true;
                    }
                }
            }
            var next_symbol_sep = '?';
            if( something_added ) {
                uri = uri + additional_datas;
                next_symbol_sep = '&';
            }
            if( cur_page > 1 && jQuery(the_ajax_script.pagination_class+' a').last().length && jQuery(the_ajax_script.pagination_class+' a').last().attr('href').search('product-page=') == -1 ) {
                uri = uri + next_symbol_sep + "paged=" + cur_page;
                next_symbol_sep = '&';
            }
            if( uri_request ) {
                uri = uri + next_symbol_sep + uri_request;
                next_symbol_sep = '&';
            }

            if( the_ajax_script.control_sorting && args.orderby && the_ajax_script.default_sorting != args.orderby ){
                uri = uri + next_symbol_sep + 'orderby=' + args.orderby;
                next_symbol_sep = '&';
            }

            if( cur_page > 1 && jQuery(the_ajax_script.pagination_class+' a').last().length && jQuery(the_ajax_script.pagination_class+' a').last().attr('href').search('product-page=') != -1 ) {
                uri = uri + next_symbol_sep + "product-page=" + cur_page;
            }
            if ( /\#/.test(location.href) ) {
                passed_vars1 = location.href.split('#');
                passed_vars1 = passed_vars1[1];
                uri += '#'+passed_vars1;
            }

            if( return_request ) {
                return uri;
            } else {
                var stateParameters = { BeRocket: "Rules" };
                history.replaceState(stateParameters, "BeRocket Rules");
                history.pushState(stateParameters, "BeRocket Rules", uri);
                history.pathname = uri;
            }
        <?php
        return ob_get_clean();
    }
    function php_parse_inside_test($isset) {
        $link_data = $this->php_parse(array());
        if( ! empty($link_data['taxonomy']) && is_array($link_data['taxonomy']) && count($link_data['taxonomy']) ) {
            return true;
        }
        return $isset;
    }
    function php_parse_inside_filters($base_filters) {
        $link_data = $this->php_parse(array());
        $filters = array();
        if( ! empty($link_data['taxonomy']) && is_array($link_data['taxonomy']) ) {
            foreach($link_data['taxonomy'] as $taxonomy) {
                if( $taxonomy['type'] == 'single' ) {
                    $filters[] = $taxonomy['taxonomy'].'['.implode(($taxonomy['data']['operator'] == 'OR' ? '-' : '+'), explode(',', $taxonomy['data']['terms'])).']';
                } elseif( $taxonomy['type'] == 'from_to' ) {
                    $filters[] = $taxonomy['taxonomy'].'['.$taxonomy['data']['from']. '_' . $taxonomy['data']['to'] .']';
                }
            }
        }
        if( count($filters) ) {
            $filters = implode('|', $filters);
            return $filters;
        }
        return $base_filters;
    }
    function php_parse_inside($data, $args = array()) {
        $link_data = $this->get_query_vars_name();
        return $link_data;
    }
    function php_generate_inside($data, $args = array()) {
        return $data;
    }
    function get_query_vars_name($link = false) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $link = $this->get_query_vars_name_link($link);
        $parts = wp_parse_url($link);
        $link_data = array(
            'var_names' => array(),
            'taxonomy' => array()
        );
        if( ! empty($parts['query']) ) {
            parse_str($parts['query'], $query_vars);
            if( is_array($query_vars) ) {
                $skip = array();
                foreach($query_vars as $get_key => $get_value) {
                    if( substr($get_key, 0, 3) == 'pa-' ) {
                        $get_key = substr($get_key, 3);
                    } else {
                        continue;
                    }
                    if( in_array($get_key, $link_data['var_names']) ) continue;
                    if( ($taxonomy = $this->check_taxonomy($get_key)) !== false ) {
                        $link_data['var_names'][] = $get_key;
                        $operator = (empty($options['default_operator_and']) ? 'OR' : 'AND');
                        $operator_var = 'pa-'.$get_key . '_operator';
                        if( ! empty($query_vars[$operator_var]) ) {
                            $operator = $query_vars[$operator_var];
                            $link_data['var_names'][] = $operator_var;
                        }
                        $link_data['taxonomy'][] = array(
                            'taxonomy'  => $taxonomy,
                            'get_key'   => $get_key,
                            'data'      => array(
                                'terms'     => $get_value,
                                'operator'  => strtoupper($operator)
                            ),
                            'type'      => 'single'
                        );
                    } elseif( strlen($get_key) > 5 && substr($get_key, -5) == '_from' && ! empty($query_vars['pa-'.substr_replace($get_key, '_to', -5)]) ) {
                        if( $taxonomy = $this->check_taxonomy(substr_replace($get_key, '', -5)) ) {
                            $link_data['var_names'][] = $get_key;
                            $link_data['var_names'][] = substr_replace($get_key, '_to', -5);
                            $link_data['taxonomy'][] = array(
                                'taxonomy'  => $taxonomy,
                                'get_key'   => $get_key,
                                'data'      => array(
                                    'from'  => $get_value,
                                    'to'    => $query_vars['pa-'.substr_replace($get_key, '_to', -5)]
                                ),
                                'type'      => 'from_to'
                            );
                        }
                    } elseif( strlen($get_key) > 3 && substr($get_key, -3) == '_to'  && ! empty($query_vars['pa-'.substr_replace($get_key, '_from', -3)]) ) {
                        if( $taxonomy = $this->check_taxonomy(substr_replace($get_key, '', -3)) ) {
                            $link_data['var_names'][] = $get_key;
                            $link_data['var_names'][] = substr_replace($get_key, '_from', -3);
                            $link_data['taxonomy'][] = array(
                                'taxonomy'  => $taxonomy,
                                'get_key'   => $get_key,
                                'data'      => array(
                                    'to'    => $get_value,
                                    'from'  => $query_vars['pa-'.substr_replace($get_key, '_from', -3)]
                                ),
                                'type'      => 'from_to'
                            );
                        }
                    }
                }
            }
        }
        return $link_data;
    }
    function js_generate_new($handle) {
        if( $handle == 'berocket_aapf_widget-script' ) {
            ob_start();
            ?>
//Link Like Woocommerce
var braapf_get_current_filters_separate_link,
braapf_glue_by_operator_separate_link,
braapf_set_filters_to_link_separate_link,
braapf_compat_filters_to_string_single_separate_link,
braapf_compat_filters_result_separate_link;
(function ($){
    braapf_get_current_filters_separate_link = function(url_data) {
        var new_queryargs = [];
        var filters = '';
        $.each(url_data.queryargs, function(i, val) {
            if(val.name.substring(0, 3) == 'pa-') {
                if( filters === '' ) {
                    filters = '';
                } else {
                    filters = filters+'&';
                }
                filters = filters+val.name+'='+val.value;
            } else {
                new_queryargs.push(val);
            }
        });
        url_data.filter = filters;
        url_data.queryargs = new_queryargs;
        return url_data;
    }
    braapf_glue_by_operator_separate_link = function(glue) {
        return ',';
    }
    braapf_compat_filters_result_separate_link = function(filter, val) {
        var operator_string = '';
        if( typeof(val.operator) != 'undefined' && val.operator != the_ajax_script.default_operator ) {
            
            if( val.operator == 'slidr' ) {
                var two_values = filter.values.split('_');
                if( typeof(two_values[0]) != 'undefined' && typeof(two_values[1]) != 'undefined' ) {
                    filter.val_from = 'pa-'+filter.taxonomy+'_from='+two_values[0];
                    filter.val_to = 'pa-'+filter.taxonomy+'_to='+two_values[1];
                }
            } else {
                operator_string = 'pa-'+filter.taxonomy+'_operator='+val.operator;
            }
        }
        filter.operator = operator_string;
        return filter;
    }
    braapf_compat_filters_to_string_single_separate_link = function(single_string, val, compat_filters, filter_mask, glue_between_taxonomy) {
        if( typeof( val.val_from ) != 'undefined' ) {
            single_string = val.val_from+'&'+ val.val_to;
        } else if( val.operator.length ) {
            single_string = single_string+'&'+val.operator;
        }
        return single_string;
    }
    braapf_set_filters_to_link_separate_link = function(url, url_data, parameters, url_without_query, query_get) {
        if(url_data.filter.length) {
            if( query_get.length ) {
                query_get = '&'+query_get;
            }
            query_get = url_data.filter+query_get;
            url = url_without_query+'?'+query_get;
        }
        return url;
    }
})(jQuery);
berocket_remove_filter('get_current_url_data', braapf_get_current_filters);
berocket_remove_filter('url_from_urldata_linkget', braapf_set_filters_to_link);

//Remove filters
berocket_add_filter('get_current_url_data', braapf_get_current_filters_separate_link);
//Add filters
berocket_add_filter('glue_by_operator', braapf_glue_by_operator_separate_link, 1);
berocket_add_filter('compat_filters_result_single', braapf_compat_filters_result_separate_link, 20);
berocket_add_filter('compat_filters_to_string_single', braapf_compat_filters_to_string_single_separate_link);
berocket_add_filter('url_from_urldata_linkget', braapf_set_filters_to_link_separate_link);
            <?php
            $script = ob_get_clean();
            wp_add_inline_script('berocket_aapf_widget-script', $script);
            remove_action('wp_footer', array($this, 'js_footer_new_func'));
            remove_action( 'braapf_wp_enqueue_script_after', array($this, 'js_generate_new'), 10, 1 );
        }
    }
    function localize_widget_script($localization) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $localization['url_mask'] = 'pa-%t%=%v%';
        $localization['url_split'] = '&';
        $localization['nice_url'] = '';
        $localization['seo_uri_decode'] = '';
        $localization['default_operator'] = (empty($options['default_operator_and']) ? 'OR' : 'AND');
        return $localization;
    }
}
new BeRocket_AAPF_lp_separate_vars();
