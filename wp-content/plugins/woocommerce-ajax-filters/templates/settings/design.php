<?php
$fonts_list = g_fonts_list();
?>
<table class="wp-list-table widefat fixed posts">
    <thead>
        <tr><th colspan="7" style="text-align: center; font-size: 2em;"><?php _e('Show title only Styles', 'BeRocket_AJAX_domain') ?></th></tr>
        <tr>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Element', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Border color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Border width', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Border radius', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Font color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Background', 'BeRocket_AJAX_domain') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr class="br_onlyTitle_title_radio_settings">
            <td><?php _e('Title', 'BeRocket_AJAX_domain') ?></td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'bcolor'), '000000') ?>"></div>
                <input class="br_border_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'bcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_title][bcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_width_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_title][bwidth]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'bwidth')); ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_radius_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_title][bradius]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'bradius')); ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_size_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_title][fontsize]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'fontsize')); ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'fcolor'), '000000') ?>"></div>
                <input class="br_font_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'fcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_title][fcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'backcolor'), '000000') ?>"></div>
                <input class="br_background_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_title', 'backcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_title][backcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
        </tr>
        <tr class="br_onlyTitle_title_radio_settings">
            <td><?php _e('Title when opened', 'BeRocket_AJAX_domain') ?></td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'bcolor'), '000000') ?>"></div>
                <input class="br_border_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'bcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_titleopened][bcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_width_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_titleopened][bwidth]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'bwidth')); ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_radius_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_titleopened][bradius]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'bradius')); ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_size_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_titleopened][fontsize]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'fontsize')); ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'fcolor'), '000000') ?>"></div>
                <input class="br_font_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'fcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_titleopened][fcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'backcolor'), '000000') ?>"></div>
                <input class="br_background_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_titleopened', 'backcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_titleopened][backcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
        </tr>
        <tr class="br_onlyTitle_filter_radio_settings">
            <td><?php _e('Filter', 'BeRocket_AJAX_domain') ?></td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'bcolor'), '000000') ?>"></div>
                <input class="br_border_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'bcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_filter][bcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_width_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_filter][bwidth]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'bwidth')) ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_radius_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_filter][bradius]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'bradius')) ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_size_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][onlyTitle_filter][fontsize]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'fontsize')) ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'fcolor'), '000000') ?>"></div>
                <input class="br_font_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'fcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_filter][fcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'backcolor'), '000000') ?>"></div>
                <input class="br_background_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'onlyTitle_filter', 'backcolor')) ?>" name="br_filters_options[styles_input][onlyTitle_filter][backcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th class="manage-column admin-column-theme" scope="col" colspan="7">
                <input type="button" value="<?php _e('Set all to theme default', 'BeRocket_AJAX_domain') ?>" class="all_theme_default button">
                <div style="clear:both;"></div>
            </th>
        </tr>
    </tfoot>
</table>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Loading icon', 'BeRocket_AJAX_domain') ?></th>
        <td>
            <?php echo berocket_font_select_upload('', 'br_filters_options_ajax_load_icon', 'br_filters_options[ajax_load_icon]', br_get_value_from_array($options, 'ajax_load_icon'), false); ?>
        </td>
    </tr>
</table>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Loading icon text', 'BeRocket_AJAX_domain') ?></th>
        <td>
            <span><?php _e('Above:', 'BeRocket_AJAX_domain') ?> </span><input name="br_filters_options[ajax_load_text][top]" type='text' value='<?php echo br_get_value_from_array($options, array('ajax_load_text', 'top')); ?>'/>
        </td>
        <td>
            <span><?php _e('Below:', 'BeRocket_AJAX_domain') ?> </span><input name="br_filters_options[ajax_load_text][bottom]" type='text' value='<?php echo br_get_value_from_array($options, array('ajax_load_text', 'bottom')); ?>'/>
        </td>
        <td>
            <span><?php _e('Before:', 'BeRocket_AJAX_domain') ?> </span><input name="br_filters_options[ajax_load_text][left]" type='text' value='<?php echo br_get_value_from_array($options, array('ajax_load_text', 'left')); ?>'/>
        </td>
        <td>
            <span><?php _e('After:', 'BeRocket_AJAX_domain') ?> </span><input name="br_filters_options[ajax_load_text][right]" type='text' value='<?php echo br_get_value_from_array($options, array('ajax_load_text', 'right')); ?>'/>
        </td>
    </tr>
</table>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Show and hide description', 'BeRocket_AJAX_domain') ?></th>
        <td>
            <span><?php _e('Show when user:', 'BeRocket_AJAX_domain') ?> </span>
            <select name="br_filters_options[description][show]">
                <option <?php echo ( $options['description']['show'] == 'click' ) ? 'selected' : '' ?> value="click"><?php _e('Click', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['description']['show'] == 'hover' ) ? 'selected' : '' ?> value="hover"><?php _e('Hovering over the icon', 'BeRocket_AJAX_domain') ?></option>
            </select>
        </td>
    </tr>
</table>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Style for number of products', 'BeRocket_AJAX_domain') ?></th>
        <td>
            <select name="br_filters_options[styles_input][product_count]">
                <option <?php echo ( $options['styles_input']['product_count'] ) ? 'selected' : '' ?> value=""><?php _e('4', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['styles_input']['product_count'] == 'round' ) ? 'selected' : '' ?> value="round"><?php _e('(4)', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['styles_input']['product_count'] == 'quad' ) ? 'selected' : '' ?> value="quad"><?php _e('[4]', 'BeRocket_AJAX_domain') ?></option>
            </select>
        </td>
        <td>
            <span><?php _e('Position:', 'BeRocket_AJAX_domain') ?> </span>
            <select name="br_filters_options[styles_input][product_count_position]">
                <option <?php echo ( $options['styles_input']['product_count_position'] ) ? 'selected' : '' ?> value=""><?php _e('Normal', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['styles_input']['product_count_position'] == 'right' ) ? 'selected' : '' ?> value="right"><?php _e('Right', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['styles_input']['product_count_position'] == 'right2em' ) ? 'selected' : '' ?> value="right2em"><?php _e('Right from name', 'BeRocket_AJAX_domain') ?></option>
            </select>
        </td>
        <td>
            <span><?php _e('Position on Image:', 'BeRocket_AJAX_domain') ?> </span>
            <select name="br_filters_options[styles_input][product_count_position_image]">
                <option value=""><?php _e('Normal', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( br_get_value_from_array($options, array('styles_input','product_count_position_image') ) == 'right' ) ? 'selected' : '' ?> value="right"><?php _e('Right', 'BeRocket_AJAX_domain') ?></option>
            </select>
        </td>
    </tr>
</table>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Indent for hierarchy in Drop-Down', 'BeRocket_AJAX_domain') ?></th>
        <td>
            <select name="br_filters_options[child_pre_indent]">
                <option <?php echo ( $options['child_pre_indent'] ) ? 'selected' : '' ?> value=""><?php _e('-', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['child_pre_indent'] == 's' ) ? 'selected' : '' ?> value="s"><?php _e('space', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['child_pre_indent'] == '2s' ) ? 'selected' : '' ?> value="2s"><?php _e('2 spaces', 'BeRocket_AJAX_domain') ?></option>
                <option <?php echo ( $options['child_pre_indent'] == '4s' ) ? 'selected' : '' ?> value="4s"><?php _e('tab', 'BeRocket_AJAX_domain') ?></option>
            </select>
        </td>
    </tr>
</table>
