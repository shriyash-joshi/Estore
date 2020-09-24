<?php
class BeRocket_AAPF_display_filters_selected_area_type extends BeRocket_AAPF_display_filters_additional_type {
    public static $type_slug = 'selected_area';
    public static $type_name;
    public static $needed_options = array(
        'title'         => '',
        'scroll_theme' => 'dark',
        'is_hide_mobile' => false,
        'selected_area_show' => '0',
        'hide_selected_arrow' => '0',
        'selected_is_hide' => '0',
    );
    function init() {
        static::$type_name = __('Selected Filters area', 'BeRocket_AJAX_domain');
        parent::init();
    }
    public static function return_html($html, $additional) {
        extract($additional['options']);
        $br_options = self::get_option();
        $style = br_get_value_from_array($additional, array('args', 'widget_inline_style'));
        $set_query_var_title = array(
            'title'          => $additional['options']['title'],
            'uo'             => br_aapf_converter_styles( (empty($br_options['styles']) ? NULL : $br_options['styles']) ),
            'is_hide_mobile' => ( empty($additional['options']['is_hide_mobile']) ? '' : $additional['options']['is_hide_mobile'] ),
            'selected_area_show' => $additional['options']['selected_area_show'],
            'hide_selected_arrow' => $additional['options']['hide_selected_arrow'],
            'selected_is_hide' => $additional['options']['selected_is_hide'],
            'style'             => $style,
            'custom_css'     => berocket_isset($css_class),
        );
        set_query_var( 'berocket_query_var_title', $set_query_var_title );
        ob_start();
        br_get_template_part( 'old_templates/widget_selected_area' );
        return ob_get_clean();
    }
}
new BeRocket_AAPF_display_filters_selected_area_type();
