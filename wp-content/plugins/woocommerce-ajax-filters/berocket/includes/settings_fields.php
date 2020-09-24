<?php
if( ! class_exists('BeRocket_framework_settings_fields') ) {
class BeRocket_framework_settings_fields {
    function __construct() {
        do_action('BeRocket_framework_settings_fields_construct');
        add_filter('berocket_framework_item_content_text', array($this, 'text'), 10, 6);
        add_filter('berocket_framework_item_content_number', array($this, 'number'), 10, 6);
        add_filter('berocket_framework_item_content_radio', array($this, 'radio'), 10, 8);
        add_filter('berocket_framework_item_content_checkbox', array($this, 'checkbox'), 10, 8);
        add_filter('berocket_framework_item_content_selectbox', array($this, 'selectbox'), 10, 6);
        add_filter('berocket_framework_item_content_textarea', array($this, 'textarea'), 10, 6);
        add_filter('berocket_framework_item_content_color', array($this, 'color'), 10, 6);
        add_filter('berocket_framework_item_content_image', array($this, 'image'), 10, 6);
        add_filter('berocket_framework_item_content_faimage', array($this, 'faimage'), 10, 6);
        add_filter('berocket_framework_item_content_fontawesome', array($this, 'fontawesome'), 10, 6);
        add_filter('berocket_framework_item_content_fa', array($this, 'fontawesome'), 10, 6);
        add_filter('berocket_framework_item_content_products', array($this, 'products'), 10, 6);
    }
    function text($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<label>' . '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>'
              . '<input type="text" name="' . $field_name
              . '" value="' . htmlentities($value) . '"' . $class . $extra . '/>'
              . '<span class="br_label_for">' . $field_item['label_for'] . '</span></label>';
        return $html;
    }
    function number($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<label>' . '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>'
              . '<input type="number" name="' . $field_name
              . '" value="' . $value . '"' . $class . $extra
              . ( empty($field_item['min']) ? '' : ' min="' . $field_item['min'] . '"' ) . ( empty($field_item['max']) ? '' : ' max="' . $field_item['max'] . '"' ) . '/>'
              . '<span class="br_label_for">' . $field_item['label_for'] . '</span></label>';
        return $html;
    }
    function radio($html, $field_item, $field_name, $value, $class, $extra, $option_values, $option_deault_values) {
        $radio_default = ( isset($option_values) ? $option_values : (! empty($field_item['default']) ? $field_item['value'] : ( ! empty($option_deault_values) ? $option_deault_values : '' ) ) );
        $html .= '<label>' . $field_item['label_be_for']
              . '<input type="radio" name="' . $field_name
              . '" value="' . $field_item['value'] . '"'
              . ( $field_item['value'] == $radio_default ? ' checked="checked" ' : '' )
              . $class . $extra . '/>'
              . $field_item['label_for'] . '</label>';
        return $html;
    }
    function checkbox($html, $field_item, $field_name, $value, $class, $extra, $option_values, $option_deault_values) {
        $html .= '<label>' . $field_item['label_be_for']
              . '<input type="checkbox" name="' . $field_name
              . '" value="' . $field_item['value'] . '"' .
              ( ( ! empty($option_values) ) ? ' checked="checked" ' : '' ) . $class . $extra . '/>'
              . $field_item['label_for'] . '</label>';
        return $html;
    }
    function selectbox($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<label>' . '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>'
             . '<select name="' . $field_name
             . '"' . $class . $extra . '>';
        if ( isset($field_item['options']) and is_array($field_item['options']) and count( $field_item['options'] ) ) {
            foreach ( $field_item['options'] as $option ) {
                $html .= '<option value="' . $option['value'] . '"' .
                     ( ( $value == $option['value'] ) ? ' selected="selected" ' : '' )
                     . '>' . $option['text'] . '</option>';
            }
        } else {
            $html .= "<option>Options data is corrupted!</option>";
        }
        $html .= '</select>' . '<span class="br_label_for">' . $field_item['label_for'] . '</span></label>';
        return $html;
    }
    function textarea($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>' . '<textarea name="' . $field_name
              . '"' . $class . $extra . '>'. htmlentities($value) . '</textarea>' . '<span class="br_label_for">' . $field_item['label_for'] . '</span>';
        return $html;
    }
    function color($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>';
        if( empty($value) ) {
            $value = $field_item['value'];
        }
        $html .= br_color_picker( $field_name, $value, ( isset($field_item['value']) ? $field_item['value'] : '' ), $field_item);
        $html .= '<span class="br_label_for">' . $field_item['label_for'] . '</span>';
        return $html;
    }
    function image($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>';
        $html .= br_upload_image( $field_name, $value, $field_item);
        $html .= '<span class="br_label_for">' . $field_item['label_for'] . '</span>';
        return $html;
    }
    function faimage($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>';
        $html .= br_fontawesome_image( $field_name, $value, $field_item);
        $html .= '<span class="br_label_for">' . $field_item['label_for'] . '</span>';
        return $html;
    }
    function fontawesome($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>';
        $html .= br_select_fontawesome( $field_name, $value, $field_item);
        $html .= '<span class="br_label_for">' . $field_item['label_for'] . '</span>';
        return $html;
    }
    function products($html, $field_item, $field_name, $value, $class, $extra) {
        $html .= '<span class="br_label_be_for">' . $field_item['label_be_for'] . '</span>';
        $html .= br_products_selector( $field_name, $value, $field_item);
        $html .= '<span class="br_label_for">' . $field_item['label_for'] . '</span>';
        return $html;
    }
}
new BeRocket_framework_settings_fields();
}
