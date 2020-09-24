var berocket_admin_filter_types = {
    tag:['checkbox','radio','select','color','image','tag_cloud'],
    product_cat:['checkbox','radio','select','color','image'],
    sale:['checkbox','radio','select'],
    custom_taxonomy:['checkbox','radio','select','color','image'],
    attribute:['checkbox','radio','select','color','image'],
    price:['slider'],
    filter_by:['checkbox','radio','select','color','image']
};
var berocket_admin_filter_types_by_attr = {
    checkbox:'<option value="checkbox">'+aapf_admin_text.checkbox_text+'</option>',
    radio:'<option value="radio">'+aapf_admin_text.radio_text+'</option>',
    select:'<option value="select">'+aapf_admin_text.select_text+'</option>',
    color:'<option value="color">'+aapf_admin_text.color_text+'</option>',
    image:'<option value="image">'+aapf_admin_text.image_text+'</option>',
    slider:'<option value="slider">'+aapf_admin_text.slider_text+'</option>',
    tag_cloud:'<option value="tag_cloud">'+aapf_admin_text.tag_cloud_text+'</option>'
};

(function ($) {
    $(document).ready(function () {

        $('.get_shortcode').click( function ( event ) {
            event.preventDefault();
            $form = $(this).parents('form');
            var params = $( '.br_colorpicker_field_input, .berocket_image_value' );
            var attr = $( '#berocket_sc_attribute' );
            var type = $( '#berocket_sc_type' );
            if ( params.length > 0 ) {
                params = params.serialize();
                params = params+'&action=aapf_color_set&type='+type.val()+'&tax_color_name='+attr.val();
                $.post(ajaxurl, params, function (data) {});
            }
            create_shortcode( $form );
        });
        $(document).on('change', '.berocket_aapf_widget_sc, .berocket_aapf_style_sb_sc, .berocket_aapf_sb_attributes_sc, .berocket_aapf_childs_sc, .berocket_aapf_include_list_sc', function() {
            $(this).data('sc_change', '1');
        });
        function create_shortcode( $form ) {
            var shortcode = {
                key:   [],
                value: [],
            };
            var widget_type = $form.find('.berocket_aapf_widget_admin_widget_type_select').val();
            $form.find('.berocket_aapf_widget_sc').each( function ( i, o ) {
                if( $(o).data('sc_change') ) {
                    if ( $(o).is('[type=checkbox]') ) {
                        if( shortcode.key.indexOf( $(o).data('sc') ) == -1 ) {
                            shortcode.key.push($(o).data('sc'));
                            if ( $(o).prop('checked') ) {
                                shortcode.value.push($(o).val());
                            } else {
                                shortcode.value.push('');
                            }
                        } else {
                            index = shortcode.key.indexOf( $(o).data('sc') );
                            if ( $(o).prop('checked') ) {
                                if ( ! Array.isArray( shortcode.value[index] ) ) {
                                    firstvalue = shortcode.value[index];
                                    shortcode.value[index] = [];
                                    if( firstvalue != '' ) {
                                        shortcode.value[index].push(firstvalue);
                                    }
                                }
                                shortcode.value[index].push($(o).val());
                            }
                        }
                    } else if ( $(o).is('[type=radio]') ) {
                        if ( $(o).prop('checked') ) {
                            shortcode.key.push($(o).data('sc'));
                            shortcode.value.push($(o).val());
                        }
                    } else {
                        if( shortcode.key.indexOf( $(o).data('sc') ) == -1 ) {
                            shortcode.key.push($(o).data('sc'));
                            shortcode.value.push($(o).val());
                        } else {
                            if( $(o).val() != '' ) {
                                index = shortcode.key.indexOf( $(o).data('sc') );
                                if ( ! Array.isArray( shortcode.value[index] ) ) {
                                    firstvalue = shortcode.value[index];
                                    shortcode.value[index] = [];
                                    if( firstvalue != '' ) {
                                        shortcode.value[index].push(firstvalue);
                                    }
                                }
                                shortcode.value[index].push($(o).val());
                            }
                        }
                    }
                }
            });
            if( widget_type == 'search_box' ) {
                var search_box_count = $form.find('.br_search_box_count').val();
                var search_box_style = {};
                var search_box_style_exist = false;
                $form.find('.berocket_aapf_style_sb_sc').each( function ( i, o ) {
                    if( $(o).data('sc_change') ) {
                        search_box_style[$(o).data('sc')] = $(o).val();
                        search_box_style_exist = true;
                    }
                });
                if( search_box_style_exist ) {
                    shortcode.key.push('search_box_style');
                    shortcode.value.push(JSON.stringify(search_box_style));
                }

                var search_box_attributes = {};
                var search_box_attributes_exist = false;
                for( var i = 1; i <= search_box_count; i++ ) {
                    var $attribute_block = $form.find('.berocket_search_box_attribute_'+i);
                    
                    var current_attr = {};
                    var current_attr_exist = false;
                    $attribute_block.find('.berocket_aapf_sb_attributes_sc').each( function ( i, o ) {
                        if( $(o).data('sc_change') ) {
                            current_attr[$(o).data('sc')] = $(o).val();
                            current_attr_exist = true;
                        }
                    });
                    if( current_attr_exist ) {
                        search_box_attributes[i] = current_attr;
                        search_box_attributes_exist = true;
                    }
                }
                if( search_box_attributes_exist ) {
                    shortcode.key.push('search_box_attributes');
                    shortcode.value.push(JSON.stringify(search_box_attributes));
                }
            }
            var child_type = $form.find('.berocket_aapf_widget_child_parent_select').val();
            if( widget_type == 'filter' && child_type == 'depth' ) {
                var child_count = $form.find('.br_onew_child_count_select').val();

                var child_onew_childs = {};
                var child_onew_childs_exist = false;
                for( var i = 1; i <= child_count; i++ ) {
                    var $child_block = $form.find('.child_onew_childs_'+i);
                    
                    var current_child = {};
                    var current_child_exist = false;
                    $child_block.find('.berocket_aapf_childs_sc').each( function ( i, o ) {
                        if( $(o).data('sc_change') ) {
                            current_child[$(o).data('sc')] = $(o).val();
                            current_child_exist = true;
                        }
                    });
                    if( current_child_exist ) {
                        child_onew_childs[i] = current_child;
                        child_onew_childs_exist = true;
                    }
                }
                if( child_onew_childs_exist ) {
                    shortcode.key.push('child_onew_childs');
                    shortcode.value.push(JSON.stringify(child_onew_childs));
                }
            }
            var br_filters = '[br_filters';
            for( var i = 0; i < shortcode.key.length; i++ ) {
                br_filters += ' '+shortcode.key[i]+'=\'';
                if ( Array.isArray( shortcode.value[i] ) ) {
                    for( var j = 0; j < shortcode.value[i].length; j++ ) {
                        if ( j != 0 ) {
                            br_filters += '|';
                        }
                        br_filters += shortcode.value[i][j];
                    }
                } else {
                    br_filters += shortcode.value[i];
                }
                br_filters += '\'';
            }
            br_filters += ']';
            window.prompt('Shortcode',br_filters);
        }

        function berocket_aapf_show_hide($block, is_hide) {
            if( is_hide ) {
                $block.hide(0);
            } else {
                $block.show(0);
            }
        }
        function berocket_aapf_hide_blocks ( $parent, args ) {
			if( args.changed != undefined ) {
			    changed = args.changed;
			} else {
                changed = 'none';
            }
            product_cat_current = $('.berocket_aapf_product_sub_cat_current_input', $parent).prop('checked');
            filter_type = $('.berocket_aapf_widget_admin_filter_type_select', $parent).val();
            filter_type_data = $('.berocket_aapf_widget_admin_filter_type_select', $parent).find('option:selected').data();
            attribute = $('.berocket_aapf_widget_admin_filter_type_attribute_select', $parent).val();
            custom_taxonomy = $('.berocket_aapf_widget_admin_filter_type_custom_taxonomy_select', $parent).val();

            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_filter_type_', $parent), 
                                     true );
            if ( $('.berocket_aapf_widget_admin_filter_type_'+filter_type, $parent).hasClass('berocket_aapf_widget_admin_filter_type_'+filter_type) ) {
                $('.berocket_aapf_widget_admin_filter_type_'+filter_type, $parent).show();
            }
            if( filter_type_data.sameas ) {
                filter_type = filter_type_data.sameas;
                if( filter_type_data.attribute ) {
                    if( filter_type_data.sameas == 'custom_taxonomy' ) {
                        custom_taxonomy = filter_type_data.attribute;
                    } else if( filter_type_data.sameas == 'attribute' ) {
                        attribute = filter_type_data.attribute;
                    }
                }
            }
            
            if ( changed != 'type' && changed != 'child_parent' ) {
                var select_options = '';
                var select_options_variants = [];
                if ( filter_type == 'tag' ) {
                    select_options_variants = berocket_admin_filter_types.tag;
                } else if ( filter_type == 'product_cat' || ( filter_type == 'custom_taxonomy' && ( custom_taxonomy == 'product_tag' || custom_taxonomy == 'product_cat' ) ) ) {
                    select_options_variants = berocket_admin_filter_types.product_cat;
                } else if ( filter_type == '_sale' || filter_type == '_stock_status' || filter_type == '_rating' ) {
                    select_options_variants = berocket_admin_filter_types.sale;
                } else if ( filter_type == 'custom_taxonomy' ) {
                    select_options_variants = berocket_admin_filter_types.custom_taxonomy;
                } else if ( filter_type == 'attribute' ) {
                    if ( attribute == 'price' ) {
                        select_options_variants = berocket_admin_filter_types.price;
                    } else {
                        select_options_variants = berocket_admin_filter_types.attribute;
                    }
                } else if ( filter_type == 'filter_by' ) {
                    select_options_variants = berocket_admin_filter_types.filter_by;
                }
                select_options_variants.forEach(function(element) {
                    select_options = select_options + berocket_admin_filter_types_by_attr[element];
                });
                $('.berocket_aapf_widget_admin_type_select', $parent).html(select_options);
                $('.berocket_aapf_widget_admin_type_select', $parent).data('sc_change', '1');
            }
            type = $('.berocket_aapf_widget_admin_type_select', $parent).val();
            child_parent = $('.berocket_aapf_widget_child_parent_select', $parent).val();

            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_operator_select', $parent).parent(), 
                                     ( ( filter_type == 'attribute'
                                     && ( attribute == 'price' ) )
                                     || type == 'slider'
                                     || filter_type == 'date'
                                     || filter_type == '_sale'
                                     || filter_type == '_rating' ) );
            berocket_aapf_show_hide( $('.br_aapf_child_parent_selector', $parent), 
                                     ( ( filter_type == 'attribute'
                                     && attribute == 'price' )
                                     || filter_type == 'product_cat'
                                     || filter_type == '_stock_status'
                                     || filter_type == 'tag'
                                     || type == 'slider'
                                     || filter_type == 'date'
                                     || filter_type == '_sale'
                                     || filter_type == '_rating' ) );
            berocket_aapf_show_hide( $('.berocket_ranges_block', $parent), 
                                     ( filter_type != 'attribute'
                                     || attribute != 'price'
                                     || type != 'ranges' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_values_per_row', $parent).parent(), 
                                     ( ( filter_type == 'attribute'
                                     && ( attribute == 'price' || attribute == 'product_cat' ) )
                                     || type == 'slider' 
                                     || type == 'select' 
                                     || type == 'tag_cloud' 
                                     || filter_type == 'product_cat'
                                     || filter_type == 'custom_taxonomy' && custom_taxonomy == 'product_cat' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_non_price_tag_cloud', $parent), 
                                     ( filter_type == 'date'
                                     || ( filter_type != 'date'
                                     && ( type == 'tag_cloud'
                                     || type == 'slider' ) ) ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_non_price_tag_cloud_select', $parent), 
                                     ( filter_type == 'date'
                                     || ( filter_type != 'date'
                                     && ( type == 'tag_cloud'
                                     || type == 'slider'
                                     || type == 'select' ) ) ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_ranges_hide', $parent),
                                     ( type == 'ranges' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_price_attribute', $parent), 
                                     ( filter_type != 'attribute'
                                     || attribute != 'price'
                                     || type != 'slider' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_advanced_color_pick_settings', $parent), 
                                     ( type != 'color' && type != 'image' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_product_sub_cat_current', $parent), 
                                     ( filter_type != 'product_cat' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_product_sub_cat_div', $parent), 
                                     ( filter_type != 'product_cat' || product_cat_current ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_tag_cloud_block', $parent), 
                                     ( type != 'tag_cloud' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_icons_select_block', $parent), 
                                     ( type == 'select' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_child_parent_depth_block', $parent), 
                                     ( child_parent != 'child' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_child_parent_one_widget', $parent), 
                                     ( child_parent != 'depth' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_order_values_by', $parent), 
                                     ( filter_type == '_stock_status'
                                     || filter_type == 'attribute' && attribute == 'price'
                                     || filter_type == 'date'
                                     || filter_type == '_sale'
                                     || filter_type == '_rating' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_order_values_type', $parent), 
                                     ( ( filter_type != 'attribute'
                                     && filter_type != 'custom_taxonomy' )
                                     || type == 'ranges')
                                     && filter_type != 'tag'
                                     && filter_type != '_rating' );
            berocket_aapf_show_hide( $('.berocket_attributes_slider_data', $parent), 
                                     ! ( ( (filter_type == 'attribute' && attribute != 'price' )
                                     || filter_type == 'custom_taxonomy' )
                                     && type == 'slider' ) );
            berocket_aapf_show_hide( $('.berocket_attributes_checkbox_radio_data', $parent), 
                                     ! ( ( filter_type == 'attribute' || filter_type == 'custom_taxonomy' )
                                     && ( type == 'checkbox' || type == 'radio' || type == 'color' || type == 'image' ) ) );
            berocket_aapf_show_hide( $('.berocket_attributes_number_style_data', $parent), 
                                     ! ( ( filter_type == 'attribute' 
                                     || filter_type == 'custom_taxonomy' )
                                     && type == 'slider' ) );
            berocket_aapf_show_hide( $('.br_type_select_block', $parent), 
                                     ( filter_type == 'date' ) );
            berocket_aapf_show_hide( $('.berocket_options_for_select', $parent), 
                                     ( ( filter_type != 'attribute'
                                     && filter_type != 'custom_taxonomy'
                                     && filter_type != 'product_cat'
                                     && filter_type != 'tag' )
                                     || type != 'select' ) );
            berocket_aapf_show_hide( $('.br_aapf_date_style_select', $parent), 
                                     ( filter_type != 'date' ) );
            if ( type == 'color' || type == 'image' ) {
                var tax_color_name;
                if ( filter_type == 'attribute' ) {
                    tax_color_name = attribute;
                } else if ( filter_type == 'custom_taxonomy' ) {
                    tax_color_name = custom_taxonomy;
                } else if( filter_type == 'tag' ) {
                    tax_color_name = 'product_tag';
                } else if( filter_type == 'product_cat' ) {
                    tax_color_name = 'product_cat';
                }
                var data = {
                    'action': 'berocket_aapf_color_listener',
                    'tax_color_name': tax_color_name,
                    'type': type
                };
                $.post(ajaxurl, data, function(data) {
                    $('.berocket_widget_color_pick', $parent).html(data);
                    $('.berocket_aapf_advanced_color_pick_settings', $parent).show(0);
                });
            } else {
                $('.berocket_widget_color_pick', $parent).text("");
            }
            if( args.changed == 'filter_type' || args.changed == 'attribute' || args.changed == 'custom_taxonomy' || args.changed == 'type' ) {
                var taxonomy_name = false;
                if( filter_type == 'product_cat' ) {
                    taxonomy_name = 'product_cat';
                } else if( filter_type == 'tag' ) {
                    taxonomy_name = 'product_tag';
                } else if( filter_type == 'attribute' && attribute != 'price' ) {
                    taxonomy_name = attribute;
                } else if ( filter_type == 'custom_taxonomy' ) {
                    taxonomy_name = custom_taxonomy;
                }
                var exclude_include_name = $('.include_exclude_list', $parent).data('name');
                if( taxonomy_name === false ) {
                    $('.include_exclude_list', $parent).text("");
                    $('.include_exclude_select', $parent).hide();
                } else {
                    if( $('.include_exclude_list', $parent).length ) {
                        $('.include_exclude_select', $parent).show();
                        var data = {
                            'action': 'br_include_exclude_list',
                            'taxonomy_name': taxonomy_name,
                        };
                        $.post(ajaxurl, data, function(data) {
                            if( data ) {
                                var replace_str = /%field_name%/g;
                                data = data.replace(replace_str, exclude_include_name);
                                $('.include_exclude_list', $parent).html(data);
                            } else {
                                $('.include_exclude_list', $parent).text("");
                            }
                        });
                    }
                }
            } else {
                $('.include_exclude_list', $parent).text("");
                $('.include_exclude_select', $parent).hide();
            }
            brjsf($('.berocket_aapf_widget_admin_type_select', $parent));
        }
        $(document).on('change', '.berocket_aapf_widget_admin_filter_type_select', function () {
            $parent = $(this).parents('.customize-control.customize-control-widget_form.widget-rendered');
            if( $parent.length == 0 ) {
                $parent = $(this).parents('form');
            }
            berocket_aapf_hide_blocks ( $parent, { changed:'filter_type' } );
        });

        $(document).on('change', '.berocket_aapf_widget_admin_filter_type_attribute_select', function () {
            $parent = $(this).parents('.customize-control.customize-control-widget_form.widget-rendered');
            if( $parent.length == 0 ) {
                $parent = $(this).parents('form');
            }
            berocket_aapf_hide_blocks ( $parent, { changed:'attribute' } );
        });

        $(document).on('change', '.berocket_aapf_widget_admin_type_select', function () {
            $parent = $(this).parents('.customize-control.customize-control-widget_form.widget-rendered');
            if( $parent.length == 0 ) {
                $parent = $(this).parents('form');
            }
            berocket_aapf_hide_blocks ( $parent, { changed:'type' } );
        });

        $(document).on('change', '.berocket_aapf_widget_admin_filter_type_custom_taxonomy_select', function () {
            $parent = $(this).parents('.customize-control.customize-control-widget_form.widget-rendered');
            if( $parent.length == 0 ) {
                $parent = $(this).parents('form');
            }
            berocket_aapf_hide_blocks ( $parent, { changed:'custom_taxonomy' } );
        });

        $(document).on('change', '.berocket_aapf_widget_child_parent_select', function () {
            $parent = $(this).parents('.customize-control.customize-control-widget_form.widget-rendered');
            if( $parent.length == 0 ) {
                $parent = $(this).parents('form');
            }
            berocket_aapf_hide_blocks ( $parent, { changed:'child_parent' } );
        });

        $(document).on('change', '.berocket_aapf_product_sub_cat_current_input', function () {
            $parent = $(this).parents('.customize-control.customize-control-widget_form.widget-rendered');
            if( $parent.length == 0 ) {
                $parent = $(this).parents('form');
            }
            berocket_aapf_hide_blocks ( $parent, { changed:'product_cat_current' } );
        });

        $(document).on('change', '.berocket_aapf_checked_show_next', function () {
            if($(this).find('input[type=checkbox]').attr('checked') == 'checked') {
                $(this).next().show(0);
            } else {
                $(this).next().hide(0);
            }
        });

        $(document).on('change', '.include_exclude_select select', function() {
            if( $(this).val() ) {
                $('.include_exclude_list').show();
            } else {
                $('.include_exclude_list').hide();
            }
        });

        $(document).on('click', '.berocket_aapf_advanced_settings_pointer', function (event) {
            event.preventDefault();
            $next = $(this).parent().next();
            if ( $next.is(':visible') ) {
                $next.slideUp(300);
            } else {
                $next.slideDown(300);
            }
        });

        $(document).on('click', '.berocket_aapf_output_limitations_pointer', function (event) {
            event.preventDefault();
            $next = $(this).parent().next();
            if ( $next.is(':visible') ) {
                $next.slideUp(300);
            } else {
                $next.slideDown(300);
            }
        });

        $('.br_colorpicker_field').each(function (i,o){
            if( typeof($(o).colpick) != 'undefined' ) {
                $(o).css('backgroundColor', '#'+$(o).data('color'));
                $(o).colpick({
                    layout: 'hex',
                    submit: 0,
                    color: '#'+$(o).data('color'),
                    onChange: function(hsb,hex,rgb,el,bySetColor) {
                        $(el).removeClass('colorpicker_removed');
                        $(el).css('backgroundColor', '#'+hex).next().val(hex).trigger('change');
                    }
                })
            }
        });

        $(document).on('click', '.theme_default', function (event) {
            event.preventDefault();
            $(this).prev().prev().css('backgroundColor', '#000000').colpickSetColor('#000000');
            $(this).prev().val('');
        });

        $(document).on('click', '.all_theme_default', function (event) {
            event.preventDefault();
            $table = $(this).parents('table');
            $table.find('.br_colorpicker_field').css('backgroundColor', '#000000').colpickSetColor('#000000');
            $table.find('.br_colorpicker_field').next().val('');
            $table.find('select').val("");
            $table.find('input[type=text]').val("");
        });

        $('.filter_settings_tabs').on('click', 'a', function (event) {
            if( ! $(this).is('.link-tab') ) {
                event.preventDefault();
                $('#br_opened_tab').val( $(this).attr('href').replace('#', '') );
                $id = $(this).attr('href');
                $('.tab-item.current').removeClass('current');
                $($id).addClass('current');

                $('.filter_settings_tabs .nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
            }
        });

        $(document).on('change', '.berocket_aapf_widget_admin_widget_type_select', function () {
            $parent = $(this).parents('form');
            if ( $(this).val() == 'filter' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).show();
                $('.berocket_aapf_admin_widget_selected_area', $parent).hide();
                $('.berocket_aapf_admin_search_box', $parent).hide();
                $('.berocket_product_category_value_limit, .berocket_widget_output_limitation_block', $parent).show();
                $('.berocket_widget_reset_button_block', $parent).hide();
            } else if( $(this).val() == 'update_button' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).hide();
                $('.berocket_aapf_admin_widget_selected_area', $parent).hide();
                $('.berocket_aapf_admin_search_box', $parent).hide();
                $('.berocket_product_category_value_limit, .berocket_widget_output_limitation_block', $parent).hide();
                $('.berocket_widget_reset_button_block', $parent).hide();
            } else if( $(this).val() == 'reset_button' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).hide();
                $('.berocket_aapf_admin_widget_selected_area', $parent).hide();
                $('.berocket_aapf_admin_search_box', $parent).hide();
                $('.berocket_product_category_value_limit, .berocket_widget_output_limitation_block', $parent).hide();
                $('.berocket_widget_reset_button_block', $parent).show();
            } else if( $(this).val() == 'selected_area' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).hide();
                $('.berocket_aapf_admin_search_box', $parent).hide();
                $('.berocket_aapf_admin_widget_selected_area', $parent).show();
                $('.berocket_product_category_value_limit, .berocket_widget_output_limitation_block', $parent).hide();
                $('.berocket_widget_reset_button_block', $parent).hide();
            } else if( $(this).val() == 'search_box' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).hide();
                $('.berocket_aapf_admin_widget_selected_area', $parent).hide();
                $('.berocket_aapf_admin_search_box', $parent).show();
                $('.berocket_product_category_value_limit, .berocket_widget_output_limitation_block', $parent).hide();
                $('.berocket_widget_reset_button_block', $parent).hide();
            }
        });
        $(document).on('change', '.berocket_scroll_shop_top', function () {
            if ( $(this).prop('checked') ) {
                $(this).parent().next().show();
            } else {
                $(this).parent().next().hide();
            }
        });
        $(document).on('click', '.berocket_aapf_font_awesome_icon_select',function(event) {
            event.preventDefault();
            $(this).next('.berocket_aapf_select_icon').show();
        });
        $(document).on('click', '.berocket_aapf_select_icon',function(event) {
            event.preventDefault();
            $(this).hide();
        });
        $(document).on('click', '.berocket_aapf_select_icon div p i.fa',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_aapf_select_icon').hide();
        });
        $(document).on('click', '.berocket_aapf_select_icon div',function(event) {
            event.preventDefault();
            event.stopPropagation()
        });
        $(document).on('click', '.berocket_aapf_select_icon label',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_aapf_select_icon').prevAll(".berocket_aapf_icon_text_value").val($(this).find('span').data('value'));
            $(this).parents('.berocket_aapf_select_icon').prevAll(".berocket_aapf_selected_icon_show").html('<i class="fa '+$(this).find('span').data('value')+'"></i>');
            $(this).parents('.berocket_aapf_select_icon').hide();
        });
        $(document).on('click', '.berocket_aapf_upload_icon', function(e) {
            e.preventDefault();
            $p = $(this);
            var custom_uploader = wp.media({
                title: 'Select custom Icon',
                button: {
                    text: 'Set Icon'
                },
                multiple: false 
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $p.prevAll(".berocket_aapf_selected_icon_show").html('<i class="fa"><image src="'+attachment.url+'" alt=""></i>');
                $p.prevAll(".berocket_aapf_icon_text_value").val(attachment.url);
            }).open();
        });
        $(document).on('click', '.berocket_aapf_remove_icon',function(event) {
            event.preventDefault();
            $(this).prevAll(".berocket_aapf_icon_text_value").val("");
            $(this).prevAll(".berocket_aapf_selected_icon_show").html("");
        });
        br_widget_set();
        $(document).on( 'change', '.br_theme_set_select', function(event) {
            var $parent = $(this).parents('.br_checkbox_radio_settings');
            var $data = $(this).find('option:selected').data();
            var $color = '000000';
            if( ! $data['border_color'] ) {
                $color = '000000';
            } else {
                $color = $data['border_color'];
            }
            $parent.find('.br_border_color_set').prev().css('backgroundColor', '#' + $color).colpickSetColor('#' + $color);
            $parent.find('.br_border_color_set').val( $data['border_color'] );
            if( ! $data['font_color'] ) {
                $color = '000000';
            } else {
                $color = $data['font_color'];
            }
            $parent.find('.br_font_color_set').prev().css('backgroundColor', '#' + $color).colpickSetColor('#' + $color);
            $parent.find('.br_font_color_set').val( $data['font_color'] );
            if( ! $data['background'] ) {
                $color = '000000';
            } else {
                $color = $data['background'];
            }
            $parent.find('.br_background_set').prev().css('backgroundColor', '#' + $color).colpickSetColor('#' + $color);
            $parent.find('.br_background_set').val( $data['background'] );
            $parent.find('.br_border_width_set').val( $data['border_width'] );
            $parent.find('.br_border_radius_set').val( $data['border_radius'] );
            $parent.find('.br_size_set').val( $data['size'] );
            $parent.find('.br_icon_set').val( $data['icon'] );
        });
        $(document).on( 'change', '.br_checkbox_radio_settings input, .br_checkbox_radio_settings select', function(event) {
            if( ! $(this).is( '.br_theme_set_select' ) ) {
                $(this).parents('.br_checkbox_radio_settings').find('.br_theme_set_select').val('');
            }
        });
        $(document).on('click', '.berocket_remove_ranges',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_ranges').remove();
        });
        $(document).on('click', '.berocket_add_ranges',function(event) {
            event.preventDefault();
            $(this).before($(this).data('html'));
        });
        $(document).on('change', '.br_onew_child_count_select', function() {
            var child_count = $(this).val();
            $('.child_onew_childs_settings').hide();
            $parents = $(this).parents('.berocket_aapf_widget_child_parent_one_widget');
            for( var i = 1; i <= child_count; i++, $parents ) {
                $('.child_onew_childs_'+i).show();
            }
        }); 
        $(document).on('change', '.br_search_box_count', function() {
            var $parent = $(this).parents('.berocket_aapf_admin_search_box');
            for(i = 1; i < 11; i++ ) {
                if( i <= $(this).val() ) {
                    $parent.find('.berocket_search_box_attribute_'+i).show();
                } else {
                    $parent.find('.berocket_search_box_attribute_'+i).hide();
                }
            }
        });
        $(document).on('change', '.br_search_box_attribute_type', function() {
            var $parent = $(this).parents('.br_search_box_attribute_block');
            $parent.find('.br_search_box_attribute_attribute_block').hide();
            $parent.find('.br_search_box_attribute_custom_taxonomy_block').hide();
            if( $(this).val() == 'attribute' ) {
                $parent.find('.br_search_box_attribute_attribute_block').show();
            } else if( $(this).val() == 'custom_taxonomy' ) {
                $parent.find('.br_search_box_attribute_custom_taxonomy_block').show();
            }
        });
        $(document).on('change', '.berocket_search_link_select', function() {
            var $parent = $(this).parents('.berocket_aapf_admin_search_box');
            $parent.find('.berocket_search_link').hide();
            $parent.find('.berocket_search_link_'+$(this).val()).show();
        });
        $(document).on('change', '.berocket_attributes_number_style', function() {
            var $parent = $(this).parents('.berocket_attributes_number_style_data');
            if( $(this).prop('checked') ) {
                $parent.find('.berocket_attributes_number_styles').show();
            } else {
                $parent.find('.berocket_attributes_number_styles').hide();
            }
        });
        $(document).on('change', '.berocket_seo_friendly_urls', berocket_change_seo_friendly_urls);
        $(document).on('change', '.berocket_nice_url', berocket_change_seo_friendly_urls);
        $(document).on('change', '.berocket_seo_meta_title', berocket_change_seo_meta_title);
        $(document).on('change', '.berocket_use_links_filters', berocket_change_use_links_filters);
        berocket_change_seo_friendly_urls();
        berocket_change_seo_meta_title();
        berocket_change_use_links_filters();
    })
})(jQuery);
function berocket_change_seo_friendly_urls() {
    if( jQuery('.berocket_seo_friendly_urls').prop('checked') ) {
        jQuery('.berocket_use_slug_in_url').parents('tr').first().show();
        jQuery('.berocket_use_links_filters').parents('tr').first().show();
        jQuery('.berocket_nice_url').parents('tr').first().show();
        jQuery('.berocket_uri_decode').parents('tr').first().show();
    } else {
        jQuery('.berocket_use_slug_in_url').prop('checked', false);
        jQuery('.berocket_nice_url').prop('checked', false);
        jQuery('.berocket_use_links_filters').prop('checked', false);
        jQuery('.berocket_use_slug_in_url').parents('tr').first().hide();
        jQuery('.berocket_use_links_filters').parents('tr').first().hide();
        jQuery('.berocket_nice_url').parents('tr').first().hide();
        jQuery('.berocket_uri_decode').parents('tr').first().hide();
    }
    if( jQuery('.berocket_seo_friendly_urls').prop('checked') && jQuery('.berocket_nice_url').prop('checked') ) {
        jQuery('.berocket_canonicalization').parents('tr').first().show();
    } else {
        jQuery('.berocket_canonicalization').prop('checked', false);
        jQuery('.berocket_canonicalization').parents('tr').first().hide();
    }
}
function  berocket_change_seo_meta_title() {
    if( jQuery('.berocket_seo_meta_title').prop('checked') ) {
        jQuery('.berocket_seo_meta_title_elements').show();
    } else {
        jQuery('.berocket_seo_meta_title_elements').hide();
    }
}
function  berocket_change_use_links_filters() {
    if( jQuery('.berocket_use_links_filters').prop('checked') ) {
        jQuery('.berocket_use_noindex').show();
        jQuery('.berocket_use_nofollow').show();
    } else {
        jQuery('.berocket_use_noindex').hide();
        jQuery('.berocket_use_nofollow').hide();
    }
}
var br_widget_setted = false;
function br_widget_set() {
    if ( br_widget_setted !== false ) {
        clearTimeout( br_widget_setted );
    }
    br_widget_setted = setTimeout( function () {
        if( typeof(brjsf) != 'undefined' && jQuery.isFunction(brjsf) && jQuery.isFunction(brjsf_accordion) ) {
            brjsf(jQuery( ".br_select_menu_left" ));
            brjsf(jQuery( ".br_select_menu_right" ));
            brjsf_accordion(jQuery( ".br_accordion" ));
            jQuery('.berocket_aapf_widget_admin_widget_type_select').parents('.editwidget').first().css('width', 'initial');
        } else {
            br_widget_set();
        }
        br_widget_setted = false;
    }, 400);
}
var br_saved_timeout;
var br_savin_ajax = false;
(function ($){
    $(document).ready( function () {
        $(document).on('click', '.br_aapf_settings_fa .berocket_upload_image', function(e) {
            e.preventDefault();
            $p = $(this);
            var custom_uploader = wp.media({
                title: 'Select custom Icon',
                button: {
                    text: 'Set Icon'
                },
                multiple: false 
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $p.prevAll(".berocket_selected_image").html('<image src="'+attachment.url+'" alt="">');
                $p.prevAll(".berocket_image_value").val(attachment.url);
            }).open();
        });
        $(document).on('click', '.br_aapf_settings_fa .berocket_remove_image',function(event) {
            event.preventDefault();
            $(this).prevAll(".berocket_image_value").val("");
            $(this).prevAll(".berocket_selected_image").html("");
        });
        var berocket_fa_select_for = $('.berocket_fa_dark');
        $(document).on('click', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_select_fa',function(event) {
            event.preventDefault();
            berocket_fa_select_for = $(this);
            $('.berocket_fa_dark').not(':first').remove();
            var $html = $('<div class="berocket_select_fontawesome"></div>');
            $html.append($('.berocket_fa_dark'));
            var $html2 = $('<div class="br_aapf_settings_fa"></div>');
            $html2.append($html);
            $('body').children('.br_aapf_settings_fa').remove();
            $('body').append($html2);
            $('.berocket_fa_dark').show();
        });
        $(document).on('hover', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_fa_hover', function() {
            var window_width = $(window).width();
            window_width = window_width / 2;
            var $this = $(this).parents('.berocket_fa_icon');
            if( $this.offset().left < window_width ) {
                $this.find('.berocket_fa_preview').css({left: '0', right: 'initial'});
                $this.find('.berocket_fa_preview span').appendTo($this.find('.berocket_fa_preview'));
            } else {
                $this.find('.berocket_fa_preview').css({left: 'initial', right: '0'});
                $this.find('.berocket_fa_preview .fa').appendTo($this.find('.berocket_fa_preview'));
            }
        });
        $(document).on('click', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_fa_hover',function(event) {
            event.preventDefault();
            var value = $(this).parents('.berocket_fa_icon').first().find('.berocket_fa_preview span').text();
            $(berocket_fa_select_for).parents('.berocket_select_fontawesome').find('.berocket_fa_value').val(value);
            $(berocket_fa_select_for).parents('.berocket_select_fontawesome').find('.berocket_selected_fa').html('<i class="fa '+value+'"></i>');
            $('.berocket_fa_dark').hide();
        });
        $(document).on('click', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_remove_fa',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_select_fontawesome').find('.berocket_fa_value').val('');
            $(this).parents('.berocket_select_fontawesome').find('.berocket_selected_fa').html('');
        });
        $(document).on('keyup', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_fa_search', function() {
            var $parent = $(this).parents('.berocket_select_fontawesome').first();
            var value = $(this).val();
            value = value.replace(/\s+/g, '');
            value = value.toLowerCase();
            if( value.length >=1 ) {
                $parent.find('.berocket_fa_icon').hide();
                $parent.find('.berocket_fa_preview span:contains("'+value+'")').parents('.berocket_fa_icon').show();
            } else {
                $parent.find('.berocket_fa_icon').show();
            }
        });
        $(document).on('click', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_fa_dark',function(event) {
            event.preventDefault();
            $(this).hide();
        });
        $(document).on('click', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_fa_dark .berocket_fa_close',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_fa_dark').hide();
        });
        $(document).on('click', '.br_aapf_settings_fa .berocket_select_fontawesome .berocket_fa_popup',function(event) {
            event.preventDefault();
            event.stopPropagation();
        });
        $(document).on('click', '.berocket_generate_new_filter_from_old', function(event) {
            event.preventDefault();
            var form_data = $(this).parents('form').first().serialize();
            form_data = 'action=aapf_generate_new_filter&'+form_data;
            $.post(ajaxurl, form_data, function (data) {
                if( data != 'error' ) {
                    location.href = data;
                }
            });
        });
        jQuery('#widget-1_berocket_aapf_widget-__i__, #widget-2_berocket_aapf_widget-__i__, #widget-3_berocket_aapf_widget-__i__, #widget-4_berocket_aapf_widget-__i__, #widget-5_berocket_aapf_widget-__i__, #widget-6_berocket_aapf_widget-__i__, #widget-7_berocket_aapf_widget-__i__, #widget-8_berocket_aapf_widget-__i__').remove();
        $(document).on('click', '.berocket_create_new', function(event) {
            event.preventDefault();
            var $this = $(this);
            var data = $(this).data();
            $.post(ajaxurl, data, function(html) {
                var parent = $this.parents('form').first();
                if( $this.parents('.widget').length ) {
                    parent = $this.parents('.widget').first();
                }
                parent.css('position', 'relative');
                parent.append($(html));
                berocket_add_submit_function_to_element();
            });
        });
        function berocket_add_submit_function_to_element() {
            $('.berocket_simple_filter_creation:not(.berocket_submit_event_added)').on('submit', function(event) {
                event.preventDefault();
                $this = $(this);
                var form_data = $this.serialize();
                $.post($this.attr('action'), form_data, function(result) {
                    if( typeof(window[$this.data('function')]) == 'function' ) {
                        window[$this.data('function')]($this, result);
                    }
                }, 'json');
            }).addClass('berocket_submit_event_added');
        }
        $(document).on('click', '.berocket_simple_filter_creation .berocket_simple_close', function(event) {
            event.preventDefault();
            $(this).parents('.berocket_simple_filter_creation').remove();
        });
        $(document).on('change', '.berocket_new_widget_selectbox', function() {
            var edit = $(this).find('option:selected').data('edit');
            if( typeof(edit) != 'undefined' && edit ) {
                $(this).next('.berocket_aapf_edit_post_link').attr('href', edit).show();
            } else {
                $(this).next('.berocket_aapf_edit_post_link').hide();
            }
        });
    });
})(jQuery);
function berocket_semple_creation_single_return($this, data) {
    var $widget = $this.parent();
    jQuery('.berocket_new_widget_selectbox.single').append('<option data-name="'+data.name2+'" data-edit="'+data.edit+'" value="'+data.value+'">'+data.name+'</option>');
    $widget.find('.berocket_new_widget_selectbox.single').val(data.value).trigger('change');
    $this.remove();
}
function berocket_semple_creation_group_return($this, data) {
    var $widget = $this.parent();
    jQuery('.berocket_new_widget_selectbox.group').append('<option data-edit="'+data.edit+'" value="'+data.value+'">'+data.name+'</option>');
    $widget.find('.berocket_new_widget_selectbox.group').val(data.value).trigger('change');
    $this.remove();
}
