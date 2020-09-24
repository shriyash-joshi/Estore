<?php
$fonts_list = g_fonts_list();
?>
<table class="wp-list-table widefat fixed posts">
    <thead>
        <tr>
            <th class="manage-column column-cb check-column" id="cb" scope="col">
                <label for="cb-select-all-1" class="screen-reader-text"><?php _e('Select All', 'BeRocket_AJAX_domain') ?></label>
                <input type="checkbox" id="cb-select-all-1" />
            </th>
            <th class="manage-column" scope="col"><?php _e('Element', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-family" scope="col"><?php _e('Font Family', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-weight" scope="col"><?php _e('Font-Weight', 'BeRocket_AJAX_domain') ?><br /><small><?php _e('(depends on font)', 'BeRocket_AJAX_domain') ?></small></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Font-Size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-theme" scope="col"><?php _e('Theme', 'BeRocket_AJAX_domain') ?></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <th class="manage-column column-cb check-column" scope="col">
                <label for="cb-select-all-2" class="screen-reader-text"><?php _e('Select All', 'BeRocket_AJAX_domain') ?></label>
                <input type="checkbox" id="cb-select-all-2" />
            </th>
            <th class="manage-column" scope="col"><?php _e('Element', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-family" scope="col"><?php _e('Font Family', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-weight" scope="col"><?php _e('Font-Weight', 'BeRocket_AJAX_domain') ?><br /><small><?php _e('(depends on font)', 'BeRocket_AJAX_domain') ?></small></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Font-Size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-theme" scope="col"><?php _e('Theme', 'BeRocket_AJAX_domain') ?></th>
        </tr>
        <tr>
            <th class="manage-column admin-column-theme" scope="col" colspan="7">
                <input type="button" value="<?php _e('Set all to theme default', 'BeRocket_AJAX_domain') ?>" class="all_theme_default button">
                <div style="clear:both;"></div>
            </th>
        </tr>
    </tfoot>

    <tbody id="the-list">
        <?php
            $i_designable = 1;
            foreach ( $designables as $key => $designable ) {
                ?>
                <tr class="type-page status-publish author-self">
                    <th class="check-column" scope="row">
                        <label for="cb-select-<?php echo $i_designable ?>" class="screen-reader-text"><?php _e('Select Element', 'BeRocket_AJAX_domain') ?></label>
                        <input type="checkbox" value="<?php echo $i_designable ?>" name="element[]" id="cb-select-<?php echo $i_designable ?>">
                        <div class="locked-indicator"></div>
                    </th>
                    <td><?php echo $designable['name'] ?></td>
                    <td class="admin-column-color">
                        <?php if ( $designable['has']['color'] ) { ?>
                            <div class="br_colorpicker_field" data-color="<?php echo ( ! empty($options['styles'][$key]['color']) ) ? $options['styles'][$key]['color'] : '000000' ?>"></div>
                            <input type="hidden" value="<?php echo ( ! empty($options['styles'][$key]['color']) ) ? $options['styles'][$key]['color'] : '' ?>" name="br_filters_options[styles][<?php echo $key ?>][color]" />
                            <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
                        <?php } else {
                            _e('N/A', 'BeRocket_AJAX_domain');
                        } ?>
                    </td>
                    <td class="admin-column-font-family">
                        <?php if ( $designable['has']['font_family'] ) { ?>
                            <select name="br_filters_options[styles][<?php echo $key ?>][font_family]">
                                <option value=""><?php _e('Theme Default', 'BeRocket_AJAX_domain') ?></option>
                                <?php foreach( $fonts_list as $font ) { ?>
                                    <option <?php echo ( br_get_value_from_array($options, array('styles', $key, 'font_family')) == $font ) ? 'selected' : '' ?>><?php echo $font?></option>
                                <?php } ?>
                            </select>
                        <?php } else {
                            _e('N/A', 'BeRocket_AJAX_domain');
                        } ?>
                    </td>
                    <td class="admin-column-font-weight">
                        <?php if ( $designable['has']['bold'] ) {
                            if( empty( $options['styles'][$key]['bold'] ) ) {
                                $options['styles'][$key]['bold'] = '';
                            } ?>
                            <select name="br_filters_options[styles][<?php echo $key ?>][bold]">
                                <option value=""><?php _e('Theme Default', 'BeRocket_AJAX_domain') ?></option>
                                <?php
                                $font_weight = array(
                                    'Textual Values' => array(
                                        'lighter'   => 'light',
                                        'normal'    => 'normal',
                                        'bold'      => 'bold',
                                        'bolder'    => 'bolder',
                                    ),
                                    'Numeric Values' => array(
                                        '100' => '100',
                                        '200' => '200',
                                        '300' => '300',
                                        '400' => '400',
                                        '500' => '500',
                                        '600' => '600',
                                        '700' => '700',
                                        '800' => '800',
                                        '900' => '900',
                                    ),
                                );
                                $fw_current = br_get_value_from_array($options, array('styles', $key, 'bold'));
                                foreach($font_weight as $fm_optgroup => $fw_options) {
                                    echo '<optgroup label="', $fm_optgroup, '">';
                                    foreach($fw_options as $fw_key => $fw_value) {
                                        echo '<option', ( $fw_current == $fw_key ? ' selected' : '' ), ' value="', $fw_key, '">', $fw_value, '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                                ?>
                            </select>
                        <?php } else {
                            _e('N/A', 'BeRocket_AJAX_domain');
                        } ?>
                    </td>
                    <td class="admin-column-font-size">
                        <?php if ( ! empty($designable['has']['font_size']) ) { ?>
                            <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles][<?php echo $key ?>][font_size]" value="<?php echo br_get_value_from_array($options, array('styles', $key, 'font_size')) ?>" />
                        <?php } else {
                            _e('N/A', 'BeRocket_AJAX_domain');
                        } ?>
                    </td>
                   <td class="admin-column-theme">
                        <?php if ( $designable['has']['theme'] ) { ?>
                            <select name="br_filters_options[styles][<?php echo $key ?>][theme]">
                                <option value=""><?php _e('Without Theme', 'BeRocket_AJAX_domain') ?></option>
                                <?php if ( $key != 'selectbox' ) { ?>
                                    <option value="aapf_grey1" <?php echo ( empty($options['styles'][$key]['theme']) && $options['styles'][$key]['theme'] == 'aapf_grey1' ) ? 'selected' : '' ?>>Grey</option>
                                <?php } ?>
                                <?php if ( $key != 'slider' and $key != 'checkbox_radio' ) { ?>
                                <option value="aapf_grey2" <?php echo ( ! empty($options['styles'][$key]['theme']) && $options['styles'][$key]['theme'] == 'aapf_grey2' ) ? 'selected' : '' ?>>Grey 2</option>
                                <?php } ?>
                            </select>
                        <?php } else {
                            _e('N/A', 'BeRocket_AJAX_domain');
                        } ?>
                    </td>
                </tr>
                <?php
                $i_designable++;
            }
        ?>
    </tbody>
</table>
<table class="wp-list-table widefat fixed posts">
    <thead>
        <tr><th colspan="9" style="text-align: center; font-size: 2em;"><?php _e('Checkbox / Radio', 'BeRocket_AJAX_domain') ?></th></tr>
        <tr>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Element', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Border color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Border width', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Border radius', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Font color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Background', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Icon', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Theme', 'BeRocket_AJAX_domain') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr class="br_checkbox_radio_settings">
            <td><?php _e('Checkbox', 'BeRocket_AJAX_domain') ?></td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'bcolor'), '000000') ?>"></div>
                <input class="br_border_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'bcolor')) ?>" name="br_filters_options[styles_input][checkbox][bcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_width_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][checkbox][bwidth]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'bwidth')); ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_radius_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][checkbox][bradius]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'bradius')); ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_size_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][checkbox][fontsize]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'fontsize')); ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'fcolor'), '000000') ?>"></div>
                <input class="br_font_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'fcolor')) ?>" name="br_filters_options[styles_input][checkbox][fcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'backcolor'), '000000') ?>"></div>
                <input class="br_background_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'checkbox', 'backcolor')) ?>" name="br_filters_options[styles_input][checkbox][backcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <select name="br_filters_options[styles_input][checkbox][icon]" class="fontawesome br_icon_set">
                    <option value=""<?php if ( empty($options['styles_input']['checkbox']['icon']) ) echo ' selected' ?>>NONE</option>
                    <?php $radion_icon = array( 'f00c', '2713', 'f00d', 'f067', 'f055', 'f0fe', 'f14a', 'f058' );
                    foreach( $radion_icon as $r_icon ) {
                        echo '<option value="'.$r_icon.'"'.( br_get_value_from_array($options, array('styles_input', 'checkbox', 'icon')) == $r_icon ? ' selected' : '' ).'>&#x'.$r_icon.';</option>';
                    }?>
                </select>
            </td>
            <td class="admin-column-color">
                <select name="br_filters_options[styles_input][checkbox][theme]" class="br_theme_set_select">
                    <option value=""<?php if ( empty($options['styles_input']['checkbox']['theme']) ) echo ' selected' ?>>NONE</option>
                    <?php
                    $checkbox_theme_current = br_get_value_from_array($options, array('styles_input', 'checkbox', 'theme'));
                    $checkbox_themes = array(
                        'black_1' => array(
                            'name'          => 'Black 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => '333333',
                            'background'    => 'bbbbbb',
                            'icon'          => 'f00c',
                        ),
                        'black_2' => array(
                            'name'          => 'Black 2',
                            'border_color'  => '333333',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '333333',
                            'background'    => '',
                            'icon'          => '2713',
                        ),
                        'black_3' => array(
                            'name'          => 'Black 3',
                            'border_color'  => '333333',
                            'border_width'  => '2',
                            'border_radius' => '50',
                            'size'          => '',
                            'font_color'    => '333333',
                            'background'    => '',
                            'icon'          => 'f058',
                        ),
                        'black_4' => array(
                            'name'          => 'Black 4',
                            'border_color'  => '333333',
                            'border_width'  => '2',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '333333',
                            'background'    => '',
                            'icon'          => 'f14a',
                        ),
                        'white_1' => array(
                            'name'          => 'White 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => 'dddddd',
                            'background'    => '333333',
                            'icon'          => 'f00c',
                        ),
                        'white_2' => array(
                            'name'          => 'White 2',
                            'border_color'  => 'dddddd',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => 'dddddd',
                            'background'    => '',
                            'icon'          => '2713',
                        ),
                        'white_3' => array(
                            'name'          => 'White 3',
                            'border_color'  => 'dddddd',
                            'border_width'  => '2',
                            'border_radius' => '50',
                            'size'          => '',
                            'font_color'    => 'dddddd',
                            'background'    => '',
                            'icon'          => 'f058',
                        ),
                        'white_4' => array(
                            'name'          => 'White 4',
                            'border_color'  => 'dddddd',
                            'border_width'  => '2',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => 'dddddd',
                            'background'    => '',
                            'icon'          => 'f14a',
                        ),
                        'red_1' => array(
                            'name'          => 'Red 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => 'dd3333',
                            'background'    => '333333',
                            'icon'          => 'f00c',
                        ),
                        'red_2' => array(
                            'name'          => 'Red 2',
                            'border_color'  => 'dd3333',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => 'dd3333',
                            'background'    => '',
                            'icon'          => '2713',
                        ),
                        'red_3' => array(
                            'name'          => 'Red 3',
                            'border_color'  => 'dd3333',
                            'border_width'  => '2',
                            'border_radius' => '50',
                            'size'          => '',
                            'font_color'    => 'dd3333',
                            'background'    => '',
                            'icon'          => 'f058',
                        ),
                        'red_4' => array(
                            'name'          => 'Red 4',
                            'border_color'  => 'dd3333',
                            'border_width'  => '2',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => 'dd3333',
                            'background'    => '',
                            'icon'          => 'f14a',
                        ),
                        'green_1' => array(
                            'name'          => 'Green 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => '33dd33',
                            'background'    => '333333',
                            'icon'          => 'f00c',
                        ),
                        'green_2' => array(
                            'name'          => 'Green 2',
                            'border_color'  => '33dd33',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '33dd33',
                            'background'    => '',
                            'icon'          => '2713',
                        ),
                        'green_3' => array(
                            'name'          => 'Green 3',
                            'border_color'  => '33dd33',
                            'border_width'  => '2',
                            'border_radius' => '50',
                            'size'          => '',
                            'font_color'    => '33dd33',
                            'background'    => '',
                            'icon'          => 'f058',
                        ),
                        'green_4' => array(
                            'name'          => 'Green 4',
                            'border_color'  => '33dd33',
                            'border_width'  => '2',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '33dd33',
                            'background'    => '',
                            'icon'          => 'f14a',
                        ),
                        'blue_1' => array(
                            'name'          => 'Blue 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => '3333dd',
                            'background'    => '333333',
                            'icon'          => 'f00c',
                        ),
                        'blue_2' => array(
                            'name'          => 'Blue 2',
                            'border_color'  => '3333dd',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '3333dd',
                            'background'    => '',
                            'icon'          => '2713',
                        ),
                        'blue_3' => array(
                            'name'          => 'Blue 3',
                            'border_color'  => '3333dd',
                            'border_width'  => '2',
                            'border_radius' => '50',
                            'size'          => '',
                            'font_color'    => '3333dd',
                            'background'    => '',
                            'icon'          => 'f058',
                        ),
                        'blue_4' => array(
                            'name'          => 'Blue 4',
                            'border_color'  => '3333dd',
                            'border_width'  => '2',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '3333dd',
                            'background'    => '',
                            'icon'          => 'f14a',
                        ),
                    );
                    foreach($checkbox_themes as $chth_key => $chth_data) {
                        echo '<option value="', $chth_key, '"';
                        foreach($chth_data as $chth_data_key => $chth_data_val) {
                            echo ' data-', $chth_data_key, '="', $chth_data_val, '"';
                        }
                        if( $checkbox_theme_current == $chth_key ) {
                            echo ' selected';
                        }
                        echo '>', $chth_data['name'], '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr class="br_checkbox_radio_settings">
            <td><?php _e('Radio', 'BeRocket_AJAX_domain') ?></td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'bcolor'), '000000') ?>"></div>
                <input class="br_border_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'bcolor')) ?>" name="br_filters_options[styles_input][radio][bcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_width_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][radio][bwidth]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'bwidth')) ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_border_radius_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][radio][bradius]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'bradius')) ?>" />
            </td>
            <td class="admin-column-font-size">
                <input class="br_size_set" type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][radio][fontsize]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'fontsize')) ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'fcolor'), '000000') ?>"></div>
                <input class="br_font_color_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'fcolor')) ?>" name="br_filters_options[styles_input][radio][fcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'backcolor'), '000000') ?>"></div>
                <input class="br_background_set" type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'radio', 'backcolor')) ?>" name="br_filters_options[styles_input][radio][backcolor]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <select name="br_filters_options[styles_input][radio][icon]" class="fontawesome br_icon_set">
                    <option value=""<?php if ( empty($options['styles_input']['radio']['icon']) ) echo ' selected' ?>>NONE</option>
                    <?php $radion_icon = array( 'f111', '2022', 'f10c', 'f192', 'f0c8', 'f055', 'f0fe', 'f14a', 'f058' );
                    foreach( $radion_icon as $r_icon ) {
                        echo '<option value="'.$r_icon.'"'.( br_get_value_from_array($options, array('styles_input', 'radio', 'icon')) == $r_icon ? ' selected' : '' ).'>&#x'.$r_icon.';</option>';
                    }?>
                </select>
            </td>
            <td class="admin-column-color">
                <select name="br_filters_options[styles_input][radio][theme]" class="br_theme_set_select">
                    <option value=""<?php if ( empty($options['styles_input']['radio']['theme']) ) echo ' selected' ?>>NONE</option>
                    <?php
                    $radio_theme_current = br_get_value_from_array($options, array('styles_input', 'checkbox', 'theme'));
                    $radio_themes = array(
                        'black_1' => array(
                            'name'          => 'Black 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => '333333',
                            'background'    => 'bbbbbb',
                            'icon'          => 'f111',
                        ),
                        'black_2' => array(
                            'name'          => 'Black 2',
                            'border_color'  => '333333',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '333333',
                            'background'    => '',
                            'icon'          => 'f0c8',
                        ),
                        'black_3' => array(
                            'name'          => 'Black 3',
                            'border_color'  => '333333',
                            'border_width'  => '2',
                            'border_radius' => '',
                            'size'          => '10',
                            'font_color'    => '333333',
                            'background'    => '',
                            'icon'          => 'f055',
                        ),
                        'white_1' => array(
                            'name'          => 'White 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => 'dddddd',
                            'background'    => '333333',
                            'icon'          => 'f111',
                        ),
                        'white_2' => array(
                            'name'          => 'White 2',
                            'border_color'  => 'dddddd',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => 'dddddd',
                            'background'    => '',
                            'icon'          => 'f0c8',
                        ),
                        'white_3' => array(
                            'name'          => 'White 3',
                            'border_color'  => 'dddddd',
                            'border_width'  => '2',
                            'border_radius' => '',
                            'size'          => '10',
                            'font_color'    => 'dddddd',
                            'background'    => '',
                            'icon'          => 'f055',
                        ),
                        'red_1' => array(
                            'name'          => 'Red 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => 'dd3333',
                            'background'    => '333333',
                            'icon'          => 'f111',
                        ),
                        'red_2' => array(
                            'name'          => 'Red 2',
                            'border_color'  => 'dd3333',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => 'dd3333',
                            'background'    => '',
                            'icon'          => 'f0c8',
                        ),
                        'red_3' => array(
                            'name'          => 'Red 3',
                            'border_color'  => 'dd3333',
                            'border_width'  => '2',
                            'border_radius' => '',
                            'size'          => '10',
                            'font_color'    => 'dd3333',
                            'background'    => '',
                            'icon'          => 'f055',
                        ),
                        'green_1' => array(
                            'name'          => 'Green 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => '33dd33',
                            'background'    => '333333',
                            'icon'          => 'f111',
                        ),
                        'green_2' => array(
                            'name'          => 'Green 2',
                            'border_color'  => '33dd33',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '33dd33',
                            'background'    => '',
                            'icon'          => 'f0c8',
                        ),
                        'green_3' => array(
                            'name'          => 'Green 3',
                            'border_color'  => '33dd33',
                            'border_width'  => '2',
                            'border_radius' => '',
                            'size'          => '10',
                            'font_color'    => '33dd33',
                            'background'    => '',
                            'icon'          => 'f055',
                        ),
                        'blue_1' => array(
                            'name'          => 'Blue 1',
                            'border_color'  => '',
                            'border_width'  => '0',
                            'border_radius' => '5',
                            'size'          => '',
                            'font_color'    => '3333dd',
                            'background'    => '333333',
                            'icon'          => 'f111',
                        ),
                        'blue_2' => array(
                            'name'          => 'Blue 2',
                            'border_color'  => '3333dd',
                            'border_width'  => '1',
                            'border_radius' => '2',
                            'size'          => '',
                            'font_color'    => '3333dd',
                            'background'    => '',
                            'icon'          => 'f0c8',
                        ),
                        'blue_3' => array(
                            'name'          => 'Blue 3',
                            'border_color'  => '3333dd',
                            'border_width'  => '2',
                            'border_radius' => '',
                            'size'          => '10',
                            'font_color'    => '3333dd',
                            'background'    => '',
                            'icon'          => 'f055',
                        ),
                    );
                    foreach($radio_themes as $rth_key => $rth_data) {
                        echo '<option value="', $rth_key, '"';
                        foreach($rth_data as $rth_data_key => $rth_data_val) {
                            echo ' data-', $rth_data_key, '="', $rth_data_val, '"';
                        }
                        if( $checkbox_theme_current == $rth_key ) {
                            echo ' selected';
                        }
                        echo '>', $rth_data['name'], '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th class="manage-column admin-column-theme" scope="col" colspan="9">
                <input type="button" value="<?php _e('Set all to theme default', 'BeRocket_AJAX_domain') ?>" class="all_theme_default button">
                <div style="clear:both;"></div>
            </th>
        </tr>
    </tfoot>
</table>
<table class="wp-list-table widefat fixed posts">
    <thead>
        <tr><th colspan="10" style="text-align: center; font-size: 2em;"><?php _e('Slider', 'BeRocket_AJAX_domain') ?></th></tr>
        <tr>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Line color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Back line color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Line height', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Line border color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Line border width', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Button size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Button color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Button border color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Button border width', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Button border radius', 'BeRocket_AJAX_domain') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'line_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'line_color')) ?>" name="br_filters_options[styles_input][slider][line_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'back_line_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'back_line_color')) ?>" name="br_filters_options[styles_input][slider][back_line_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][slider][line_height]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'line_height')) ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'line_border_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'line_border_color')) ?>" name="br_filters_options[styles_input][slider][line_border_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][slider][line_border_width]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'line_border_width')) ?>" />
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][slider][button_size]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'button_size')) ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'button_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'button_color')) ?>" name="br_filters_options[styles_input][slider][button_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'button_border_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'button_border_color')) ?>" name="br_filters_options[styles_input][slider][button_border_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][slider][button_border_width]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'button_border_width')); ?>" />
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][slider][button_border_radius]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'slider', 'button_border_radius')); ?>" />
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th class="manage-column admin-column-theme" scope="col" colspan="10">
                <input type="button" value="<?php _e('Set all to theme default', 'BeRocket_AJAX_domain') ?>" class="all_theme_default button">
                <div style="clear:both;"></div>
            </th>
        </tr>
    </tfoot>
</table>
<table class="wp-list-table widefat fixed posts">
    <thead>
        <tr><th colspan="10" style="text-align: center; font-size: 2em;"><?php _e('Product count description before filtering with Apply Filters button', 'BeRocket_AJAX_domain') ?></th></tr>
        <tr>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Background color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Border color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Font size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Font color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Show button font size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Show button font color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Show button font color on mouse over', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-font-size" scope="col"><?php _e('Close button size', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Close button font color', 'BeRocket_AJAX_domain') ?></th>
            <th class="manage-column admin-column-color" scope="col"><?php _e('Close button font color on mouse over', 'BeRocket_AJAX_domain') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'back_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'back_color')) ?>" name="br_filters_options[styles_input][pc_ub][back_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'border_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'border_color')) ?>" name="br_filters_options[styles_input][pc_ub][border_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][pc_ub][font_size]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'font_size')); ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'font_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'font_color')) ?>" name="br_filters_options[styles_input][pc_ub][font_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][pc_ub][show_font_size]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'show_font_size')); ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'show_font_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'show_font_color')) ?>" name="br_filters_options[styles_input][pc_ub][show_font_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'show_font_color_hover'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'show_font_color_hover')) ?>" name="br_filters_options[styles_input][pc_ub][show_font_color_hover]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-font-size">
                <input type="text" placeholder="<?php _e('Theme Default', 'BeRocket_AJAX_domain') ?>" name="br_filters_options[styles_input][pc_ub][close_size]" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'close_size')); ?>" />
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'close_font_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'close_font_color')) ?>" name="br_filters_options[styles_input][pc_ub][close_font_color]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
            <td class="admin-column-color">
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'close_font_color_hover'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($options, array('styles_input', 'pc_ub', 'close_font_color_hover')) ?>" name="br_filters_options[styles_input][pc_ub][close_font_color_hover]" />
                <input type="button" value="<?php _e('Default', 'BeRocket_AJAX_domain') ?>" class="theme_default button tiny-button">
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th class="manage-column admin-column-theme" scope="col" colspan="10">
                <input type="button" value="<?php _e('Set all to theme default', 'BeRocket_AJAX_domain') ?>" class="all_theme_default button">
                <div style="clear:both;"></div>
            </th>
        </tr>
    </tfoot>
</table>