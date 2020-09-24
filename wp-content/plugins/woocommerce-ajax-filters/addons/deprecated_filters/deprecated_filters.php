<?php
class BeRocket_aapf_deprecated_compat_addon extends BeRocket_framework_addon_lib {
    public $addon_file = __FILE__;
    public $plugin_name = 'ajax_filters';
    public $php_file_name   = 'display_widget';
    function get_addon_data() {
        $data = parent::get_addon_data();
        return array_merge($data, array(
            'addon_name'    => __('Deprecated Filters', 'BeRocket_AJAX_domain'),
            'deprecated'    => true,
            'tooltip'       => __('<span style="color: red;">IT WILL BE REMOVED IN THE FUTURE</span><br>Temporary compatibility with older filters', 'BeRocket_AJAX_domain')
        ));
    }
    function init_active() {
        parent::init_active();
        add_filter('BeRocket_AAPF_widget_load_file', array($this, 'disable_file'));
        add_filter('BRAAPF_single_filter_settings_meta_use', array($this, 'settings_meta'), 10, 3);
        add_filter('BRAAPF_single_filter_settings_enqueue_scripts', array($this, 'enqueue_scripts'), 10, 1);
        add_action( 'braapf_register_frontend_assets', array( $this, 'init_scripts' ), 9999999999 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_additional_scripts' ) );
        foreach (glob(__DIR__ . "/display_filter/*.php") as $filename)
        {
            include_once($filename);
        }
        add_action('wp', array($this, 'wp'));
        add_filter('brfr_data_ajax_filters', array($this, 'settings_page'));
        add_filter('brfr_ajax_filters_prevent_disable', array($this, 'section_prevent_disable'), 10, 3);
        add_filter( 'berocket_filter_filter_type_array', array($this, 'filter_filter_type_array'), 20 );
        add_filter( 'brfr_data_ajax_filters', array($this, 'plugin_settings_page'), 50) ;
        add_filter( 'brfr_ajax_filters_old_design', array($this, 'section_old_design'), 50, 3) ;
        add_filter( 'ajax_filters_get_template_part', array($this, 'deprecated_template_get'), 10, 2 );
        add_filter( 'aapf_localize_widget_script', array($this, 'js_data_fix'), 9000000 );
        add_action( 'bapf_search_button_meta_box', array($this, 'search_box_settings'), 10, 2 );
        add_filter( 'berocket_aapf_group_before_all', array($this, 'search_box_before_group_start'), 11, 2 );
        add_filter( 'berocket_aapf_group_after_all', array($this, 'search_box_after_group_end'), 9, 2 );
        add_filter( 'BeRocket_AAPF_getall_Template_Styles', array($this, 'remove_new_templates'), 9000000 );
        add_filter( 'braapf_custom_user_css_replacement', array($this, 'custom_user_css_replacement') );
        update_option('braapf_new_filters_converted', false);
    }
    function wp() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        if ( ! empty($option['selected_area_show']) ) {
            add_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($this, 'selected_area'), 1 );
            remove_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($BeRocket_AAPF, 'selected_area'), 1 );
        }
    }
    function disable_file($isload) {
        return false;
    }
    function enqueue_additional_scripts() {
        wp_register_script( 'berocket_aapf_widget-tag_cloud', plugins_url( 'j.doe.cloud.min.js', __FILE__ ), array( 'jquery-ui-core' ), BeRocket_AJAX_filters_version );
    }
    function enqueue_scripts($enqueue) {
        BeRocket_AAPF::wp_enqueue_script('braapf-deprecated-admin-js', plugins_url( '/admin.js', __FILE__ ), array('jquery') );
        wp_localize_script(
            'braapf-deprecated-admin-js',
            'aapf_admin_text',
            array(
                'checkbox_text' => __('Checkbox', 'BeRocket_AJAX_domain'),
                'radio_text' => __('Radio', 'BeRocket_AJAX_domain'),
                'select_text' => __('Select', 'BeRocket_AJAX_domain'),
                'color_text' => __('Color', 'BeRocket_AJAX_domain'),
                'image_text' => __('Image', 'BeRocket_AJAX_domain'),
                'slider_text' => __('Slider', 'BeRocket_AJAX_domain'),
                'tag_cloud_text' => __('Tag cloud', 'BeRocket_AJAX_domain'),
            )
        );
        return false;
    }
    function settings_meta($isuse, $clthis, $post) {
        $instance = $clthis->get_option($post->ID);
        $post_name = $clthis->post_name;
        include "filter_post.php";
        return false;
    }
    function init_scripts() {
        wp_deregister_script('berocket_aapf_widget-script');
        wp_deregister_style('berocket_aapf_widget-style');
        wp_register_script( 'berocket_aapf_widget-script', plugins_url( 'widget.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-slider', 'jquery-ui-datepicker' ), BeRocket_AJAX_filters_version );
        wp_register_style ( 'berocket_aapf_widget-style', plugins_url( 'widget.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
        add_action('wp_footer', array($this, 'footer_css'));
    }
    public function footer_css() {
        $this->br_custom_user_css();
    }
    public function selected_area() {
        $set_query_var_title = array();
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options = $BeRocket_AAPF->get_option();
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', $br_options );
        $set_query_var_title['title'] = apply_filters( 'berocket_aapf_widget_title', ( empty($title) ? '' : $title ) );
        $set_query_var_title['uo'] = br_aapf_converter_styles( ( empty($br_options['styles']) ? '' : $br_options['styles'] ) );
        $set_query_var_title['selected_area_show'] = empty($br_options['selected_area_hide_empty']);
        $set_query_var_title['hide_selected_arrow'] = false;
        $set_query_var_title['selected_is_hide'] = false;
        $set_query_var_title['is_hooked'] = true;
        $set_query_var_title['is_hide_mobile'] = false;
        set_query_var( 'berocket_query_var_title', $set_query_var_title );
        br_get_template_part( 'old_templates/widget_selected_area' );
    }
    public function settings_page($data) {
        $data['Addons']['prevent_disable'] = array(
            "section"   => "prevent_disable",
            "value"     => "",
        );
        return $data;
    }
    function section_prevent_disable ( $item, $element_data, $options ) {
        $html = '<script>
            jQuery(":contains(Deprecated Filters)").parents(".berocket_addon_label").find("input").on("change", function(e) {
                if( ! jQuery(this).prop("checked") ) {
                    jQuery(document).trigger("braapf_deprecated_filters_disabled");
                }
            });
        </script>';
        $popup_text = '<h2>'. __('ATTENTION! After disabling this addon all filters will be converted to new version and there is no way to convert them back automatically.', 'BeRocket_AJAX_domain') . '</h2>'
        . '<p><strong>' . __('We recommend you to try it on a staging/dev/local site first.', 'BeRocket_AJAX_domain') . '</strong></p>'
        . __('Please check this on addon deactivation (it will be deactivated when settings saved).', 'BeRocket_AJAX_domain')
        . '<ol>'
        . '<li>' . __('Filters on Front-end, how they looks and works. Some styles can differ in new version.', 'BeRocket_AJAX_domain') . '</li>';
        if( ! empty($options['user_custom_css']) ) {
            $popup_text .= '<li>' . __('You have Custom CSS. Most likely it won\'t work without this addon. You will need to update it as CSS classes were changed.', 'BeRocket_AJAX_domain') . '</li>';
        }
        if( ! empty($options['javascript']) && ( 
            ! empty($options['javascript']['berocket_ajax_filtering_start']) 
            || ! empty($options['javascript']['berocket_ajax_filtering_start']) 
            || ! empty($options['javascript']['berocket_ajax_filtering_start']) 
        ) ) {
            $popup_text .= '<li>' . __('You have Custom Javascript. You need to check it after turning off this addon. It can work correctly same as stop working.', 'BeRocket_AJAX_domain') . '</li>';
        }
        $popup_text .= '</ol>'
        . '<p><strong>' . __('Do you want to disable it anyway?', 'BeRocket_AJAX_domain') . '</strong></p>';
        
        BeRocket_popup_display::add_popup(
            array(
                'yes_no_buttons' => array(
                    'show'          => true,
                    'yes_text'      => __('Yes, disable add-on', 'BeRocket_AJAX_domain'),
                    'no_text'       => __('No, turn it back', 'BeRocket_AJAX_domain'),
                    'location'      => 'popup',
                    'yes_func'      => '',
                    'no_func'       => 'jQuery(":contains(Deprecated Filters)").parents(".berocket_addon_label").find("input").prop("checked", true);',
                ),
                'no_x_button'   => true,
                'height'        => '500px',
                'width'         => '800px',
            ),
            $popup_text,
            array('event_new' => array('type' => 'event', 'event' => 'braapf_deprecated_filters_disabled'))
        );
        return $html;
    }
    function filter_filter_type_array($filter_type) {
        $filter_type = berocket_insert_to_array(
            $filter_type,
            'tag',
            array(
                'product_cat' => array(
                    'name' => __('Product sub-categories', 'BeRocket_AJAX_domain'),
                    'sameas' => 'product_cat',
                ),
            )
        );
        return $filter_type;
    }
    function plugin_settings_page($data) {
        $data['General'] = berocket_insert_to_array(
            $data['General'],
            'hide_values',
            array(
                'use_select2' => array(
                    "label"     => __( 'Select2', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "use_select2",
                    "class"     => "br_use_select2",
                    "value"     => '1',
                    'label_for' => __("Use Select2 script for dropdown menu", 'BeRocket_AJAX_domain') . '<br>',
                ),
            )
        );
        $data['Design'] = berocket_insert_to_array(
            $data['Design'],
            'design',
            array(
                'design_old' => array(
                    'section' => 'old_design',
                    "value"   => "",
                ),
            ),
            true
        );
        return $data;
    }
    public function section_old_design($html, $item, $options) {
        $designables = br_aapf_get_styled();
        ob_start();
        include __DIR__ . '/settings/design.php';
        $html = ob_get_clean();
        return $html;
    }
    function br_custom_user_css() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options     = $BeRocket_AAPF->get_option();
        $replace_css = array(
            '#widget#'       => '.berocket_aapf_widget',
            '#widget-title#' => '.berocket_aapf_widget-title'
        );
        $result_css = "";
        $result_css = str_replace(array('<style>', '</style>', '<'), '', $options[ 'user_custom_css' ]);
        foreach ( $replace_css as $key => $value ) {
            $result_css = str_replace( $key, $value, $result_css );
        }
        $result_css = trim($result_css);
        $uo = br_aapf_converter_styles( (isset($options['styles']) ? $options['styles'] : array()) );
        if( ! empty($uo['style']['selected_area']) ) {
            $result_css .= 'div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a, div.berocket_aapf_selected_area_block a{'.$uo['style']['selected_area'].'}';
        }
        if( ! empty($uo['style']['selected_area_hover']) ) {
            $result_css .= 'div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a.br_hover *, div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a.br_hover, div.berocket_aapf_selected_area_block a.br_hover{'.$uo['style']['selected_area_hover'].'}';
        }
        if ( ! empty($options['styles_input']['checkbox']['icon']) ) {
            $result_css .= 'ul.berocket_aapf_widget li > span > input[type="checkbox"] + .berocket_label_widgets:before {display:inline-block;}';
            $result_css .= '.berocket_aapf_widget input[type="checkbox"] {display: none;}';
        }
        $add_css = $BeRocket_AAPF->convert_styles_to_string($options['styles_input']['checkbox']);
        if( ! empty($add_css) ) {
            $result_css .= 'ul.berocket_aapf_widget li > span > input[type="checkbox"] + .berocket_label_widgets:before {'.$add_css.'}';
        }
        if ( ! empty($options['styles_input']['checkbox']['icon']) ) {
            $result_css .= 'ul.berocket_aapf_widget li > span > input[type="checkbox"]:checked + .berocket_label_widgets:before {';
            $result_css .= 'content: "\\'.$options['styles_input']['checkbox']['icon'].'";';
            $result_css .= '}';
        }
        if ( ! empty($options['styles_input']['radio']['icon']) ) {
            $result_css .= 'ul.berocket_aapf_widget li > span > input[type="radio"] + .berocket_label_widgets:before {display:inline-block;}';
            $result_css .= '.berocket_aapf_widget input[type="radio"] {display: none;}';
        }
        $add_css = $BeRocket_AAPF->convert_styles_to_string($options['styles_input']['radio']);
        if( ! empty($add_css) ) {
            $result_css .= 'ul.berocket_aapf_widget li > span > input[type="radio"] + .berocket_label_widgets:before {' . $add_css . '}';
        }
        if ( ! empty($options['styles_input']['radio']['icon']) ) {
            $result_css .= 'ul.berocket_aapf_widget li > span > input[type="radio"]:checked + .berocket_label_widgets:before {';
            $result_css .= 'content: "\\'.$options['styles_input']['radio']['icon'].'";';
            $result_css .= '}';
        }
        if ( ! empty($options['styles_input']['slider']['line_color']) ) {
            $result_css .= '.berocket_aapf_widget .slide .berocket_filter_slider.ui-widget-content .ui-slider-range, .berocket_aapf_widget .slide .berocket_filter_price_slider.ui-widget-content .ui-slider-range{';
            $result_css .= 'background-color: ';
            if ( $options['styles_input']['slider']['line_color'][0] != '#' ) {
                $result_css .= '#';
            }
            $result_css .= $options['styles_input']['slider']['line_color'].';';
            $result_css .= '}';
        }
        $add_css = '';
        if ( isset($options['styles_input']['slider']['line_height']) && strlen($options['styles_input']['slider']['line_height']) ) {
            $add_css .= 'height: '.$options['styles_input']['slider']['line_height'].'px;';
        }
        if ( ! empty($options['styles_input']['slider']['line_border_color']) ) {
            $add_css .= 'border-color: ';
            if ( $options['styles_input']['slider']['line_border_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['slider']['line_border_color'].';';
        }
        if ( ! empty($options['styles_input']['slider']['back_line_color']) ) {
            $add_css .= 'background-color: ';
            if ( $options['styles_input']['slider']['back_line_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['slider']['back_line_color'].';';
        }
        if ( isset($options['styles_input']['slider']['line_border_width']) && strlen($options['styles_input']['slider']['line_border_width']) ) {
            $add_css .= 'border-width: '.$options['styles_input']['slider']['line_border_width'].'px;';
        }
        if( ! empty($add_css) ) {
            $result_css .= '.berocket_aapf_widget .slide .berocket_filter_slider.ui-widget-content, .berocket_aapf_widget .slide .berocket_filter_price_slider.ui-widget-content{'.$add_css.'}';
        }
        $add_css = '';
        if ( isset($options['styles_input']['slider']['button_size']) && strlen($options['styles_input']['slider']['button_size']) ) {
            $add_css .= 'font-size: '.$options['styles_input']['slider']['button_size'].'px;';
        }
        if ( ! empty($options['styles_input']['slider']['button_color']) ) {
            $add_css .= 'background-color: ';
            if ( $options['styles_input']['slider']['button_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['slider']['button_color'].';';
        }
        if ( ! empty($options['styles_input']['slider']['button_border_color']) ) {
            $add_css .= 'border-color: ';
            if ( $options['styles_input']['slider']['button_border_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['slider']['button_border_color'].';';
        }
        if ( isset($options['styles_input']['slider']['button_border_width']) && strlen($options['styles_input']['slider']['button_border_width']) ) {
            $add_css .= 'border-width: '.$options['styles_input']['slider']['button_border_width'].'px;';
        }
        if ( isset($options['styles_input']['slider']['button_border_radius']) && strlen($options['styles_input']['slider']['button_border_radius']) ) {
            $add_css .= 'border-radius: '.$options['styles_input']['slider']['button_border_radius'].'px;';
        }
        if( ! empty($add_css) ) {
            $result_css .= '.berocket_aapf_widget .slide .berocket_filter_slider .ui-state-default, 
            .berocket_aapf_widget .slide .berocket_filter_price_slider .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_slider.ui-widget-content .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_price_slider.ui-widget-content .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_slider .ui-widget-header .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_price_slider .ui-widget-header .ui-state-default
            .berocket_aapf_widget .berocket_filter_slider.ui-widget-content .ui-slider-handle,
            .berocket_aapf_widget .berocket_filter_price_slider.ui-widget-content .ui-slider-handle{'.$add_css.'}';
        }
        if( ! empty( $uo['style']['selected_area_block'] ) || ! empty( $uo['style']['selected_area_border'] ) ) {
            $result_css .= ' .berocket_aapf_selected_area_hook div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a{'
            .( ! empty( $uo['style']['selected_area_block'] ) ? 'background-'.$uo['style']['selected_area_block'] : '' )
            .( ! empty( $uo['style']['selected_area_border'] ) ? ' border-'.$uo['style']['selected_area_border'] : '' ).'}';
        }
        $add_css = '';
        if ( ! empty($options['styles_input']['pc_ub']['back_color']) ) {
            $add_css .= 'background-color: ';
            if ( $options['styles_input']['pc_ub']['back_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['pc_ub']['back_color'].';';
        }
        if ( ! empty($options['styles_input']['pc_ub']['border_color']) ) {
            $add_css .= 'border-color: ';
            if ( $options['styles_input']['pc_ub']['border_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['pc_ub']['border_color'].';';
        }
        if ( ! empty($options['styles_input']['pc_ub']['font_color']) ) {
            $add_css .= 'color: ';
            if ( $options['styles_input']['pc_ub']['font_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['pc_ub']['font_color'].';';
        }
        if ( isset($options['styles_input']['pc_ub']['font_size']) && strlen($options['styles_input']['pc_ub']['font_size']) ) {
            $add_css .= 'font-size: '.$options['styles_input']['pc_ub']['font_size'].'px;';
        }
        if( ! empty($add_css) ) {
            $result_css .= '.berocket_aapf_widget div.berocket_aapf_product_count_desc {'.$add_css.'}';
        }
        $add_css = '';
        if ( ! empty($options['styles_input']['pc_ub']['back_color']) ) {
            $add_css .= 'background-color: ';
            if ( $options['styles_input']['pc_ub']['back_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['pc_ub']['back_color'].';';
        }
        if ( ! empty($options['styles_input']['pc_ub']['border_color']) ) {
            $add_css .= 'border-color: ';
            if ( $options['styles_input']['pc_ub']['border_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['pc_ub']['border_color'].';';
        }
        if( ! empty($add_css) ) {
            $result_css .= '.berocket_aapf_widget div.berocket_aapf_product_count_desc > span {'.$add_css.'}';
        }
        $add_css = '';
        if ( ! empty($options['styles_input']['pc_ub']['show_font_color']) ) {
            $add_css .= 'color: ';
            if ( $options['styles_input']['pc_ub']['show_font_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['pc_ub']['show_font_color'].';';
        }
        if ( ! empty($options['styles_input']['pc_ub']['show_font_size']) ) {
            $add_css .= 'font-size: '.$options['styles_input']['pc_ub']['show_font_size'].'px;';
        }
        if( ! empty($add_css) ) {
            $result_css .= '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_widget_update_button {'.$add_css.'}';
        }
        if ( ! empty($options['styles_input']['pc_ub']['show_font_color_hover']) ) {
            $result_css .= '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_widget_update_button:hover {';
            $result_css .= 'color: ';
            if ( $options['styles_input']['pc_ub']['show_font_color_hover'][0] != '#' ) {
                $result_css .= '#';
            }
            $result_css .= $options['styles_input']['pc_ub']['show_font_color_hover'].';';
            $result_css .= '}';
        }
        $add_css = '';
        if ( ! empty($options['styles_input']['pc_ub']['close_font_color']) ) {
            $add_css .= 'color: ';
            if ( $options['styles_input']['pc_ub']['close_font_color'][0] != '#' ) {
                $add_css .= '#';
            }
            $add_css .= $options['styles_input']['pc_ub']['close_font_color'].';';
        }
        if ( ! empty($options['styles_input']['pc_ub']['close_size']) ) {
            $add_css .= 'font-size: '.$options['styles_input']['pc_ub']['close_size'].'px;';
        }
        if( ! empty($add_css) ) {
            $result_css .= '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_close_pc {'.$add_css.'}';
        }
        if ( ! empty($options['styles_input']['pc_ub']['close_font_color_hover']) ) {
            $result_css .= '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_close_pc:hover {';
            $result_css .= 'color: ';
            if ( $options['styles_input']['pc_ub']['close_font_color_hover'][0] != '#' ) {
                $result_css .= '#';
            }
            $result_css .= $options['styles_input']['pc_ub']['close_font_color_hover'].';';
            $result_css .= '}';
        }
        $add_css = $BeRocket_AAPF->convert_styles_to_string($options['styles_input']['onlyTitle_title']);
        if( ! empty($add_css) ) {
            $result_css .= 'div.berocket_single_filter_widget.berocket_hidden_clickable .berocket_aapf_widget-title_div,
            div.berocket_single_filter_widget.berocket_hidden_clickable .berocket_aapf_widget-title_div span {'.$add_css.'}';
        }
        $add_css = $BeRocket_AAPF->convert_styles_to_string($options['styles_input']['onlyTitle_titleopened']);
        if( ! empty($add_css) ) {
            $result_css .= 'div.berocket_single_filter_widget.berocket_hidden_clickable.berocket_single_filter_visible .berocket_aapf_widget-title_div,
            div.berocket_single_filter_widget.berocket_hidden_clickable.berocket_single_filter_visible .berocket_aapf_widget-title_div span {'.$add_css.'}';
        }
        $add_css = $BeRocket_AAPF->convert_styles_to_string($options['styles_input']['onlyTitle_filter']);
        if( ! empty($add_css) ) {
            $result_css .= 'div.berocket_single_filter_widget.berocket_hidden_clickable .berocket_aapf_widget {'.$add_css.'}';
        }
        if ( ! empty($options['styles_input']['onlyTitle_filter']['fcolor']) ) {
            $result_css .= 'div.berocket_single_filter_widget.berocket_hidden_clickable .berocket_aapf_widget * {';
            $result_css .= 'color: ';
            if ( $options['styles_input']['onlyTitle_filter']['fcolor'][0] != '#' ) {
                $result_css .= '#';
            }
            $result_css .= $options['styles_input']['onlyTitle_filter']['fcolor'].';';
            $result_css .= '}';
            $result_css .= 'div.berocket_single_filter_widget.berocket_hidden_clickable .berocket_aapf_widget input {';
            $result_css .= 'color: black;';
            $result_css .= '}';
        }
        if( ! empty($result_css) ) {
            echo '<style type="text/css">' . $result_css . '</style>';
        }
    }
    function deprecated_template_get($template, $name) {
        if( strpos($name, 'old_templates/') !== FALSE ) {
            $new_name = str_replace('old_templates/', '', $name);
            $new_template = locate_template( "woocommerce-ajax_filters/{$new_name}.php" );
            if( $new_template ) {
                $template = $new_template;
            }
        }
        return $template;
    }
    function js_data_fix($data) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', $BeRocket_AAPF->get_option() );
        $data['load_image'] = '<div class="berocket_aapf_widget_loading"><div class="berocket_aapf_widget_loading_container">
          <div class="berocket_aapf_widget_loading_top">' . ( ! empty( $br_options['ajax_load_text']['top'] ) ? $br_options['ajax_load_text']['top'] : '' ) . '</div>
          <div class="berocket_aapf_widget_loading_left">' . ( ! empty( $br_options['ajax_load_text']['left'] ) ? $br_options['ajax_load_text']['left'] : '' ) . '</div>' .
          ( ! empty( $br_options['ajax_load_icon'] ) ? '<img alt="" src="'.$br_options['ajax_load_icon'].'">' : '<div class="berocket_aapf_widget_loading_image"></div>' ) .
          '<div class="berocket_aapf_widget_loading_right">' . ( ! empty( $br_options['ajax_load_text']['right'] ) ? $br_options['ajax_load_text']['right'] : '' ) . '</div>
          <div class="berocket_aapf_widget_loading_bottom">' . ( ! empty( $br_options['ajax_load_text']['bottom'] ) ? $br_options['ajax_load_text']['bottom'] : '' ) . '</div>
          </div></div>';
        return $data;
    }
    function search_box_before_group_start($custom_vars, $filters) {
        if( ! empty($filters['search_box']) ) {
            $search_box_style = br_get_value_from_array($filters, 'search_box_style');
            $search_box_url = br_get_value_from_array($filters, 'search_box_url');
            $sb_style = '';
            if ( $search_box_style['position'] == 'horizontal' ) {
                $sb_count = count($filters['filters']);
                if( $search_box_style['search_position'] == 'before_after' ) {
                    $sb_count += 2;
                } else {
                    $sb_count++;
                }
                $search_box_width = (int)(100 / $sb_count);
                $sb_style .= 'width:'.$search_box_width.'%;display:inline-block;padding: 4px;';
            }
            $search_box_button_class = 'search_box_button_class_'.rand();
            if ( $search_box_style['search_position'] != 'after' ) {
                echo '<div style="'.$sb_style.'"><a data-url="'.$search_box_url.'" class="'.$search_box_button_class.' berocket_search_box_button">'.$search_box_style['search_text'].'</a></div>';
            }
            $custom_vars['search_box_button_class'] = $search_box_button_class;
            $sbb_style = '';
            if( ! empty($search_box_style['button_background']) ) {
                $sbb_style .= 'background-color:'.($search_box_style['button_background'][0] == '#' ? $search_box_style['button_background'] : '#'.$search_box_style['button_background']).';';
            }
            if( ! empty($search_box_style['text_color']) ) {
                $sbb_style .= 'color:'.($search_box_style['text_color'][0] == '#' ? $search_box_style['text_color'] : '#'.$search_box_style['text_color']).';';
            }
            $sbb_style_hover = '';
            if( ! empty($search_box_style['button_background_over']) ) {
                $sbb_style_hover .= 'background-color:'.($search_box_style['button_background_over'][0] == '#' ? $search_box_style['button_background_over'] : '#'.$search_box_style['button_background_over']).';';
            }
            if( ! empty($search_box_style['text_color_over']) ) {
                $sbb_style_hover .= 'color:'.($search_box_style['text_color_over'][0] == '#' ? $search_box_style['text_color_over'] : '#'.$search_box_style['text_color_over']).';';
            }
            $custom_vars['sbb_style'] = $sbb_style;
            $custom_vars['sbb_style_hover'] = $sbb_style_hover;
        }
        return $custom_vars;
    }
    function search_box_after_group_end($custom_vars, $filters) {
        extract($custom_vars);
        if( ! empty($filters['search_box']) ) {
            if ( $search_box_style['search_position'] != 'before' ) {
                echo '<div style="'.$sb_style.'">
                <a data-url="'.$search_box_url.'" 
                class="'.$search_box_button_class.' berocket_search_box_button">
                '.$search_box_style['search_text'].'</a></div>';
            }
            echo '<style>.'.$search_box_button_class.'{'.$sbb_style.'}.'.$search_box_button_class.':hover{'.$sbb_style_hover.'}</style>';
        }
        return $custom_vars;
    }
    function search_box_settings($post_name, $filters) {
        ?>
        <div>
            <label><?php _e('Search button position', 'BeRocket_AJAX_domain') ?></label>
            <select class="br_select_menu_left" name="<?php echo $post_name; ?>[search_box_style][search_position]">
                <option value="before"<?php if( br_get_value_from_array($filters, array('search_box_style', 'search_position')) == 'before' ) echo ' selected'; ?>><?php _e('Before', 'BeRocket_AJAX_domain') ?></option>
                <option value="after"<?php if( br_get_value_from_array($filters, array('search_box_style', 'search_position')) == 'after' ) echo ' selected'; ?>><?php _e('After', 'BeRocket_AJAX_domain') ?></option>
                <option value="before_after"<?php if( br_get_value_from_array($filters, array('search_box_style', 'search_position')) == 'before_after' ) echo ' selected'; ?>><?php _e('Before and after', 'BeRocket_AJAX_domain') ?></option>
            </select>
        </div>
        <div>
            <label><?php _e('Search button text', 'BeRocket_AJAX_domain') ?></label>
            <input type="text" class="br_admin_full_size" value="<?php echo br_get_value_from_array($filters, array('search_box_style', 'search_text')); ?>" name="<?php echo $post_name; ?>[search_box_style][search_text]">
        </div>
        <div>
            <label><?php _e('Button background color', 'BeRocket_AJAX_domain') ?></label>
            <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($filters, array('search_box_style', 'button_background'), '000000'); ?>"></div>
            <input type="hidden" value="<?php echo br_get_value_from_array($filters, array('search_box_style', 'button_background')) ?>" name="<?php echo $post_name; ?>[search_box_style][button_background]">
        </div>
        <div>
            <label><?php _e('Button background color on mouse over', 'BeRocket_AJAX_domain') ?></label>
            <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($filters, array('search_box_style', 'button_background_over'), '000000'); ?>"></div>
            <input type="hidden" value="<?php echo br_get_value_from_array($filters, array('search_box_style', 'button_background_over')) ?>" name="<?php echo $post_name; ?>[search_box_style][button_background_over]">
        </div>
        <div>
            <label><?php _e('Button text color', 'BeRocket_AJAX_domain') ?></label>
            <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($filters, array('search_box_style', 'text_color'), '000000') ?>"></div>
            <input type="hidden" value="<?php echo br_get_value_from_array($filters, array('search_box_style', 'text_color')) ?>" name="<?php echo $post_name; ?>[search_box_style][text_color]">
        </div>
        <div>
            <label><?php _e('Button text color on mouse over', 'BeRocket_AJAX_domain') ?></label>
            <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($filters, array('search_box_style', 'text_color_over'), '000000') ?>"></div>
            <input type="hidden" value="<?php echo br_get_value_from_array($filters, array('search_box_style', 'text_color_over')) ?>" name="<?php echo $post_name; ?>[search_box_style][text_color_over]">
        </div>
        <?php
    }
    function remove_new_templates() {
        return array();
    }
    function custom_user_css_replacement($replace = array()) {
        $replace['#widget#']        = '.berocket_aapf_widget';
        $replace['#widget-title#']  = '.berocket_aapf_widget-title';
        return $replace;
    }
}
new BeRocket_aapf_deprecated_compat_addon();
