<?php
if( ! class_exists('BeRocket_aapf_admin_bar_debug') ) {
    class BeRocket_aapf_admin_bar_debug{
        function __construct() {
            add_action( 'admin_bar_menu', array($this, 'debug_admin_bar_menu'), 1000 );
        }
        function debug_admin_bar_menu() {
            global $wp_admin_bar, $wpdb;
            if ( ! BeRocket_AAPF::$user_can_manage || !is_admin_bar_showing() ) return;

            $filter_data = BeRocket_AAPF::$current_page_filters;
            $added_id = $filter_data['added'];
            unset($filter_data['added']);
            if( count($filter_data) > 0 ) {
                $html = '';
                foreach($filter_data as $data_type => $filter_status) {
                    if( count($filter_status) > 0 ) {
                        $html2 = '';
                        foreach($filter_status as $data_status => $filters) {
                            if( count($filters) > 0 ) {
                                $html2 .= '<div><h3>'.esc_html(ucfirst(trim(str_replace('_', ' ', $data_status)))).'</h3><ul>';
                                foreach($filters as $filter_id => $filter_message) {
                                    $filter_id = intval($filter_id);
                                    $title = get_the_title($filter_id);
                                    if( ! empty($title) ) {
                                        $filter_message = '('.$title.')'.$filter_message;
                                    }
                                    $html2 .= '<li title="'.esc_html($filter_message).'"><a href="'.admin_url('post.php?post='.$filter_id.'&action=edit').'" target="_blank">'.esc_html($filter_id).'</a></li>';
                                }
                                $html2 .= '</ul></div>';
                            }
                        }
                        if( ! empty($html2) ) {
                            $html .= '<div><h2>'.esc_html(strtoupper(trim(str_replace('_', ' ', $data_type)))).'</h2>'.$html2.'</div>';
                        }
                    }
                }
                if( empty($html) ) {
                    $html = '<h2>'.__('Filters not detected on page', 'BeRocket_AJAX_domain').'</h2>';
                }
                $html .= '<style>#wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item {height:initial!important;line-height:1em;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item *{line-height:1em;color:#ccc;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item h2{color:white;font-size: 1.5em;text-align:center;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item h3{font-weight:bold;color:#0085ba;font-size: 1.25em;text-align:center;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item ul li {display:inline-block!important;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item ul li a {height:initial;margin:0;padding:2px;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item .bapf_adminbar_status {text-align:center;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item .bapf_adminbar_status .dashicons {font-family: dashicons;font-size: 34px;line-height: 26px;display: block;cursor:pointer;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item .bapf_adminbar_status .dashicons-yes {color:green;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item .bapf_adminbar_status .dashicons-no {color:red;}
                #wp-admin-bar-bapf_debug_bar .ab-submenu .ab-item .bapf_adminbar_status_element {display:inline-block;text-align:center; padding:3px;}
                </style>';
                $html .= '<div class="bapf_adminbar_status">';
                $html .= '</div>';
                global $br_aapf_wc_footer_widget;
                $html .= '<script>jQuery(document).ready(function() {
                    if( typeof(the_ajax_script) != "undefined" && jQuery(".bapf_sfilter").length ) {
                        var html = "<h2>STATUS</h2>";
                        
                        html += "<div class=\'bapf_adminbar_status_element\'>Is WC page";
                        html += "<span class=\'dashicons dashicons-'.(is_shop() || is_product_taxonomy() ? 'yes\' title=\'Yes, it is default WooCommerce archive page' : 'no\' title=\'No, it is not WooCommerce archive page').'\'></span>";
                        html += "</div>";
                        
                        html += "<div class=\'bapf_adminbar_status_element\'>Shortcode";
                        html += "<span class=\'dashicons dashicons-'.($br_aapf_wc_footer_widget ? 'yes\' title=\'Yes, WooCommerce products shortcode detected' : 'no\' title=\'No, page do not have any custom WooCommerce products').'\'></span>";
                        html += "</div>";
                        
                        html += "<div class=\'bapf_adminbar_status_element\'>Products";
                        try {
                            var products_elements = jQuery(the_ajax_script.products_holder_id).length;
                            var error = false;
                            if( products_elements == 0 ) {
                                error = "Products element not detected. Please check that selectors setuped correct";
                            } else if( products_elements > 1 ) {
                                error = "Multiple Products element detected on page("+products_elements+"). It can cause issue on filtering";
                            }
                            if( error === false ) {
                                html += "<span class=\'dashicons dashicons-yes\' title=\'Products element detected on page\'></span>";
                            } else {
                                html += "<span class=\'dashicons dashicons-no\' title=\'"+error+"\'></span>";
                            }
                        } catch(e) {
                            html = +"<strong>ERROR</strong>";
                            console.log(e);
                        }
                        html += "</div>";
                        html += "<div class=\'bapf_adminbar_status_element\'>Pagination";
                        try {
                            var products_elements = jQuery(the_ajax_script.products_holder_id).length;
                            var pagination_elements = jQuery(the_ajax_script.pagination_class).length;
                            var error = false;
                            if( pagination_elements == 0 ) {
                                error = "Pagination element not detected. If page has pagination or infinite scroll/load more button, then Please check that selectors setuped correct";
                            } else if( pagination_elements > 1 ) {
                                error = "Multiple Pagination element detected on page("+pagination_elements+"). It can cause issue on filtering if pagination from different products list";
                            }
                            if( error === false ) {
                                html += "<span class=\'dashicons dashicons-yes\' title=\'Pagination element detected on page\'></span>";
                            } else {
                                html += "<span class=\'dashicons dashicons-no\' title=\'"+error+"\'></span>";
                            }
                        } catch(e) {
                            html = +"<strong>ERROR</strong>";
                            console.log(e);
                        }
                        html += "</div>";
                        jQuery(".bapf_adminbar_status").html(html);
                    }
                });</script>';
                $BeRocket_AAPF = BeRocket_AAPF::getInstance();
                $title = '<img style="width:22px;height:22px;display:inline;" src="' . plugin_dir_url( BeRocket_AJAX_filters_file ) . 'berocket/includes/ico.png" alt="">' . $BeRocket_AAPF->info['norm_name'];
                $wp_admin_bar->add_menu( array( 'id' => 'bapf_debug_bar', 'title' => $title, 'href' => FALSE ) );
                $wp_admin_bar->add_menu( array( 'id' => 'bapf_debug_bar_content', 'parent' => 'bapf_debug_bar', 'title' => $html, 'href' => FALSE ) );
            }
        }
    }
    new BeRocket_aapf_admin_bar_debug();
}