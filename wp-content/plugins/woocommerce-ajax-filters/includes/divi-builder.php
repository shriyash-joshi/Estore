<?php
function berocket_filter_et_builder_ready() {
    if( class_exists('ET_Builder_Module') ) {
        class ET_Builder_Module_br_filter_single extends ET_Builder_Module {
            public $vb_support = 'partial';
            function init() {
                $this->name       = __( 'Single Filter', 'BeRocket_AJAX_domain' );
                $this->slug       = 'et_pb_br_filter_single';

                $this->fields_defaults = array(
                    'filter_id' => array(''),
                );
            }

            function get_fields() {
                $query = new WP_Query(array('post_type' => 'br_product_filter', 'nopaging' => true, 'fields' => 'ids'));
                $posts = $query->get_posts();
                $filter_list = array('0' => __('--Please select filter--', 'BeRocket_AJAX_domain'));
                if ( is_array($posts) && count($posts) ) {
                    foreach($posts as $post_id) {
                        $filter_list[$post_id] = get_the_title($post_id) . ' (ID:' . $post_id . ')';
                    }
                }
    
                $fields = array(
                    'filter_id' => array(
                        'label'           => esc_html__( 'Filter', 'BeRocket_AJAX_domain' ),
                        'type'            => 'select',
                        'options'         => $filter_list,
                    ),
                );

                return $fields;
            }

            function render( $atts, $content = null, $function_name ) {
                $html = '';
                if( ! empty($atts['filter_id']) ) {
                    $html .= trim(do_shortcode('[br_filter_single filter_id='.$atts['filter_id'].']'));
                }
                if(empty($html) && defined('DOING_AJAX') && berocket_isset($_REQUEST['action']) == 'et_fb_ajax_render_shortcode') {
                    $html .= '<h3 style="background-color:gray;color:white;">'.__('BeRocket Filter', 'BeRocket_AJAX_domain').'</h3>';
                }

                return $html;
            }
        }
        new ET_Builder_Module_br_filter_single;
        class ET_Builder_Module_br_filters_group extends ET_Builder_Module {
            public $vb_support = 'partial';
            function init() {
                $this->name       = __( 'Group Filter', 'BeRocket_AJAX_domain' );
                $this->slug       = 'et_pb_br_filters_group';

                $this->fields_defaults = array(
                    'group_id' => array(''),
                );
            }

            function get_fields() {
                $query = new WP_Query(array('post_type' => 'br_filters_group', 'nopaging' => true, 'fields' => 'ids'));
                $posts = $query->get_posts();
                $filter_list = array('0' => __('--Please select group--', 'BeRocket_AJAX_domain'));
                if ( is_array($posts) && count($posts) ) {
                    foreach($posts as $post_id) {
                        $filter_list[$post_id] = get_the_title($post_id) . ' (ID:' . $post_id . ')';
                    }
                }
                $fields = array(
                    'group_id' => array(
                        'label'           => esc_html__( 'Group', 'BeRocket_AJAX_domain' ),
                        'type'            => 'select',
                        'options'         => $filter_list,
                    ),
                );

                return $fields;
            }

            function render( $atts, $content = null, $function_name ) {
                $html = '';
                if( ! empty($atts['group_id']) ) {
                    $html .= trim(do_shortcode('[br_filters_group group_id='.$atts['group_id'].']'));
                }
                if(empty($html) && defined('DOING_AJAX') && berocket_isset($_REQUEST['action']) == 'et_fb_ajax_render_shortcode') {
                    $html .= '<h3 style="background-color:gray;color:white;">'.__('BeRocket Filter', 'BeRocket_AJAX_domain').'</h3>';
                }

                return $html;
            }
        }
        new ET_Builder_Module_br_filters_group;

        class ET_Builder_Module_braapf_filter_next extends ET_Builder_Module {
            function init() {
                $this->name       = __( 'BeRocket Filter Next Product', 'BeRocket_AJAX_domain' );
                $this->slug       = 'et_pb_braapf_filter_next';
                $this->fields_defaults = array();
            }

            function get_fields() {
                $fields = array();
                return $fields;
            }

            function render( $atts, $content = null, $function_name ) {
                add_filter('berocket_aapf_wcshortcode_is_filtering', array($this, 'enable_filtering'));
                return '';
            }

            protected function _add_additional_border_fields() {
                $this->advanced_options["border"]['css'] = array();
            }
            function enable_filtering($enabled) {
                remove_filter('berocket_aapf_wcshortcode_is_filtering', array($this, 'enable_filtering'));
                return true;
            }
        }
        new ET_Builder_Module_braapf_filter_next;
    }
}

