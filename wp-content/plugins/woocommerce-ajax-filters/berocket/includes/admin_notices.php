<?php
/*new berocket_admin_notices(array(
    'start' => 1497880000, // timestamp when notice start
    'end'   => 1497885000, // timestamp when notice end
    'name'  => 'name', //notice name must be unique for this time period
    'html'  => '', //text or html code as content of notice
    'righthtml'  => '<a class="berocket_no_thanks">No thanks</a>', //content in the right block, this is default value. This html code must be added to all notices
    'rightwidth'  => 80, //width of right content is static and will be as this value. berocket_no_thanks block is 60px and 20px is additional
    'nothankswidth'  => 60, //berocket_no_thanks width. set to 0 if block doesn't uses. Or set to any other value if uses other text inside berocket_no_thanks
    'contentwidth'  => 400, //width that uses for mediaquery is image_width + contentwidth + rightwidth
    'subscribe'  => false, //add subscribe form to the righthtml
    'priority'  => 20, //priority of notice. 1-5 is main priority and displays on settings page always
    'height'  => 50, //height of notice. image will be scaled
    'repeat'  => false, //repeat notice after some time. time can use any values that accept function strtotime
    'repeatcount'  => 1, //repeat count. how many times notice will be displayed after close
    'image'  => array(
        'global' => 'http://berocket.com/images/logo-2.png', //image URL from other site. Image will be copied to uploads folder if it possible
        //'local' => 'http://wordpress-site.com/wp-content/uploads/logo-2.png', //notice will be used this image directly
    ),
));*/
//delete_option('berocket_admin_notices'); //remove all notice information
//delete_option('berocket_last_close_notices_time'); //remove wait time before next notice
//delete_option('berocket_admin_notices_rate_stars');
if( ! class_exists( 'berocket_admin_notices' ) ) {
    /**
     * Class berocket_admin_notices
     */

    class berocket_admin_notices {
        public $find_names, $notice_exist = false;
        public static $last_time = '-24 hours';
        public static $end_soon_time = '+1 hour';
        public static $subscribed = false;
        public static $jquery_script_exist = false;
        public static $styles_exist = false;
        public static $notice_index = 0;
        public static $default_notice_options = array(
                'start'         => 0,
                'end'           => 0,
                'name'          => 'sale',
                'html'          => '',
                'righthtml'     => '<a class="berocket_no_thanks">No thanks</a>',
                'rightwidth'    => 80,
                'nothankswidth' => 60,
                'contentwidth'  => 400,
                'subscribe'     => false,
                'closed'        => '0',
                'priority'      => 20,
                'height'        => 50,
                'repeat'        => false,
                'repeatcount'   => 1,
                'image'         => array(
                    'global'    => 'http://berocket.com/images/logo-2.png'
                ),
            );
        function __construct($options = array()) {
            if( ! is_admin() ) return;
            $options = array_merge(self::$default_notice_options, $options);
            self::set_notice_by_path($options);
        }
        public static function sort_notices($notices) {
            return self::sort_array (
                $notices,
                array(
                    1 => 'krsort',
                    2 => 'ksort',
                    3 => 'ksort'
                ),
                array(
                    '1' => SORT_NUMERIC,
                    '2' => SORT_NUMERIC,
                    '3' => SORT_NUMERIC
                )
            );
        }
        public static function sort_array($array, $sort_functions, $options, $count = 3) {
            if( $count > 0 ) {
                if( ! is_array($array) ) {
                    return array();
                }
                $call_function = $sort_functions[$count];
                $call_function($array, $options[$count]);
                if( isset($array[0]) ) {
                    $first_element = $array[0];
                    unset($array[0]);
                    $array[0] = $first_element;
                    unset($first_element);
                }
                foreach($array as $item_id => $item) {
                    if( $count == 2 ) {
                        $time = time();
                        if( $item_id < $time && $item_id != 0 ) {
                            unset($array[$item_id]);
                        } else {
                            $array[$item_id] = self::sort_array($item, $sort_functions, $options, $count - 1);
                        }
                    } else {
                        $array[$item_id] = self::sort_array($item, $sort_functions, $options, $count - 1);
                    }
                    if( isset($array[$item_id]) && ( ! is_array($array[$item_id]) || count($array[$item_id]) == 0 ) ) {
                        unset($array[$item_id]);
                    }
                }
            }
            return $array;
        }
        public static function get_notice_by_path($find_names) {
            $notices = get_option( 'berocket_admin_notices' );
            if ( ! is_array( $notices ) ) {
                $notices = array();
            }

            $current_notice = &$notices;
            foreach ( $find_names as $find_name ) {
                if ( isset( $current_notice[ $find_name ] ) ) {
                    $new_current_notice = &$current_notice[ $find_name ];
                    unset( $current_notice );
                    $current_notice = &$new_current_notice;
                    unset( $new_current_notice );
                } else {
                    unset( $current_notice );
                    break;
                }
            }

            if ( ! isset( $current_notice ) ) $current_notice = false;

            return $current_notice;
        }
        public static function berocket_array_udiff_assoc_notice($a1, $a2) {
            return json_encode($a1) > json_encode($a2);
        }
        public static function set_notice_by_path($options, $replace = false, $find_names = false) {
            self::$subscribed = get_option('berocket_email_subscribed');
            if( self::$subscribed && $options['subscribe'] ) {
                return false;
            }
            $notices = get_option('berocket_admin_notices');
            if( $options['end'] < time() && $options['end'] != 0 ) {
                return false;
            }
            if( $find_names === false ) {
                $find_names = array($options['priority'], $options['end'], $options['start'], $options['name']);
            }
            if( ! is_array($notices) ) {
                $notices = array();
            }

            $current_notice = &$notices;
            foreach($find_names as $find_name) {
                if( ! isset($current_notice[$find_name]) ) {
                    $current_notice[$find_name] = array();
                }
                $new_current_notice = &$current_notice[$find_name];
                unset($current_notice);
                $current_notice = &$new_current_notice;
                unset($new_current_notice);
            }
            $array_diff = array_udiff_assoc($options, $current_notice, array(__CLASS__, 'berocket_array_udiff_assoc_notice'));
            if( isset($array_diff['image']) ) {
                unset($array_diff['image']);
            }

            if( count($array_diff) == 0 ) {
                return true;
            }
            if( empty($options['image']) || (empty($options['image']['local']) && empty($options['image']['global'])) ) {
                $options['image'] = array('width' => 0, 'height' => 0, 'scale' => 0);
            } else {
                $file_exist = false;
                if( isset($options['image']['global']) ) {
                    $wp_upload = wp_upload_dir();
                    if( ! isset($options['image']['local']) ) {
                        $url_global = $options['image']['global'];
                        $img_local = $wp_upload['basedir'] . '/' . basename($url_global);
                        $url_local = $wp_upload['baseurl'] . '/' . basename($url_global);
                        if( ! file_exists($img_local) && is_writable($wp_upload['path']) ) {
                            file_put_contents($img_local, file_get_contents($url_global));
                        }
                        if( file_exists($img_local) ) {
                            $options['image']['local'] = $url_local;
                            $options['image']['pathlocal'] = $img_local;
                        } else {
                            $options['image']['local'] = $url_global;
                            $file_exist = true;
                        }
                    }
                }
                if( ! $file_exist ) {
                    if( ! empty($options['image']['local']) ) {
                        $img_local = $options['image']['local'];
                        $img_local = str_replace(site_url('/'), '', $img_local);
                        $img_local = ABSPATH . $img_local;
                        $file_exist = ( file_exists($img_local) );
                    } else {
                        $file_exist = false;
                    }
                }
                if( $file_exist ) {
                    $check_size = true;
                    if( isset($current_notice['image']['local']) && $current_notice['image']['local'] == $options['image']['local'] ) {
                        if( isset($current_notice['image']['width']) && isset($current_notice['image']['height']) ) {
                            $options['image']['width'] = $current_notice['image']['width'];
                            $options['image']['height'] = $current_notice['image']['height'];
                            $check_size = false;
                        }
                    }
                    if( $check_size ) {
                        $image_size = @ getimagesize($options['image']['local']);
                        if( ! empty($image_size[0]) && ! empty($image_size[1]) ) {
                            $options['image']['width'] = $image_size[0];
                            $options['image']['height'] = $image_size[1];
                        } else {
                            $options['image']['width'] = $options['height'];
                            $options['image']['height'] = $options['height'];
                        }
                    }
                    $options['image']['scale'] = $options['height'] / $options['image']['height'];
                } else {
                    $options['image'] = array('width' => 0, 'height' => 0, 'scale' => 0);
                }
            }
            if( count($current_notice) == 0 ) {
                $current_notice = $options;
            } else {
                if( ! empty($options['image']['local']) && $options['image']['local'] != $current_notice['image']['local'] ) {
                    if( isset($current_notice['image']['pathlocal']) ) {
                        unlink($current_notice['image']['pathlocal']);
                    }
                }
                if( ! $replace ) {
                    $options['closed'] = $current_notice['closed'];
                }
                $current_notice = $options;
            }
            $notices = self::sort_notices($notices);
            update_option('berocket_admin_notices', $notices);
            return true;
        }
        public static function get_notice() {
            $notices = get_option('berocket_admin_notices');
            $last_time = get_option('berocket_last_close_notices_time');
            self::$subscribed = get_option('berocket_email_subscribed');
            if( ! is_array($notices) || count($notices) == 0 ) return false;
            if( $last_time > strtotime(self::$last_time) ) {
                $current_notice = self::get_not_closed_notice($notices, true);
            } else {
                $current_notice = self::get_not_closed_notice($notices);
            }
            update_option('berocket_current_displayed_notice', $current_notice);
            return $current_notice;
        }
        public static function get_notice_for_settings() {
            $notices = get_option('berocket_admin_notices');
            $last_notice = get_option('berocket_admin_notices_last_on_options');
            self::$subscribed = get_option('berocket_email_subscribed');
            $notices = self::get_notices_with_priority($notices);
            if( ! is_array($notices) || count($notices) == 0 ) {
                return false;
            }
            if( $last_notice === false ) {
                $last_notice = 0;
            } else {
                $last_notice++;
            }
            if( count($notices) <= $last_notice ) {
                $last_notice = 0;
            }
            update_option('berocket_admin_notices_last_on_options', $last_notice);
            $notice = $notices[$last_notice];
            return $notice;
        }
        public static function get_not_closed_notice($array, $end_soon = false, $closed = 0, $count = 3) {
            $notice = false;
            if( empty($array) || ! is_array($array) ) {
                $array = array();
            }
            $time = time();
            foreach($array as $item_id => $item) {
                if( $count > 0 ) {
                    if( $count == 2 && $item_id < $time && $item_id != 0 || $count == 1 && $item_id > $time && $item_id != 0 ) {
                        continue;
                    }
                    if( $count == 2 && $item_id < strtotime(self::$end_soon_time) && $item_id != 0 ) {
                        $notice = self::get_not_closed_notice($item, $end_soon, 1, $count - 1);
                    } else {
                        if( $end_soon && $count == 2 ) {
                            break;
                        }
                        $notice = self::get_not_closed_notice($item, $end_soon, $closed, $count - 1);
                    }
                } else {
                    $display_notice = ( $item['closed'] <= $closed && ( ! self::$subscribed || ! $item['subscribe'] ) && ($item['start'] == 0 || $item['start'] < $time) && ($item['end'] == 0 || $item['end'] > $time) );
                    $display_notice = apply_filters( 'berocket_admin_notice_is_display_notice', $display_notice, $item, array(
                        'subscribed' => self::$subscribed,
                        'end_soon'   => $end_soon,
                        'closed'     => $closed,
                    ) );
                    if( $display_notice ) {
                        return $item;
                    }
                }
                if( $notice != false ) break;
            }
            return $notice;
        }
        public static function get_notices_with_priority($array, $priority = 19, $count = 3) {
            if( empty($array) || ! is_array($array) ) {
                $array = array();
            }
            $time = time();
            $notices = array();
            foreach($array as $item_id => $item) {
                if( $count > 0 ) {
                    if( $count == 3 && $item_id > $priority || $count == 2 && $item_id < $time && $item_id != 0 || $count == 1 && $item_id > $time && $item_id != 0 ) {
                        continue;
                    }
                    $notice = self::get_notices_with_priority($item, $priority, $count - 1);
                    $notices = array_merge($notices, $notice);
                } else {
                    $display_notice = ( (!self::$subscribed || ! $item['subscribe']) && ($item['priority'] <= 5 || !$item['closed']) );
                    $display_notice = apply_filters( 'berocket_admin_notice_is_display_notice_priority', $display_notice, $item, array(
                        'subscribed' => self::$subscribed,
                        'priority'   => $priority,
                    ) );
                    if( $display_notice ) {
                        $notices[] = $item;
                    }
                }
            }
            return $notices;
        }
        public static function display_admin_notice() {
            $settings_page = apply_filters('is_berocket_settings_page', false);
            if( $settings_page ) {
                $notice = self::get_notice_for_settings();
            } else {
                $notice = self::get_notice();
            }
            if( ! empty($notice['original']) ) {
                $original_notice = self::get_notice_by_path($notice['original']);
                unset($original_notice['start'], $original_notice['closed'], $original_notice['repeatcount']);
                $notice = array_merge($notice, $original_notice);
            }
            
            if( $notice !== false ) {
                self::echo_notice($notice);
            }
            $additional_notice = apply_filters('berocket_display_additional_notices', array());
            if( is_array($additional_notice) && count($additional_notice) > 0 ) {
                foreach($additional_notice as $notice) {
                    if( is_array($notice) ) {
                        self::echo_notice($notice);
                    }
                }
            }
        }
        public static function echo_notice($notice) {
            $notice = array_merge(self::$default_notice_options, $notice);
            $settings_page = apply_filters('is_berocket_settings_page', false);
            self::$notice_index++;
            $notice_data = array(
                'start'     => $notice['start'],
                'end'       => $notice['end'],
                'name'      => $notice['name'],
                'priority'  => $notice['priority'],
            );
            if( $notice['end'] < strtotime(self::$end_soon_time) && $notice['end'] != 0 ) {
                $time_left = $notice['end'] - time();
                $time_left_str = "";
                $time = $time_left;
                if ( $time >= 3600 ) {
                    $hours = floor( $time/3600 );
                    $time  = $time%3600;
                    $time_left_str .= sprintf("%02d", $hours) . ":";
                }
                if ( $time >= 60 || $time_left >= 3600 ) {
                    $minutes = floor( $time/60 );
                    $time  = $time%60;
                    $time_left_str .= sprintf("%02d", $minutes) . ":";
                }
                
                $time_left_str .= sprintf("%02d", $time);
                $notice['rightwidth'] += 60;
                $notice['righthtml'] .= '<div class="berocket_time_left_block">Left<br><span class="berocket_time_left" data-time="' . $time_left . '">' . $time_left_str . '</span></div>';
            }
            if( ! empty($notice['subscribe']) ) {
                $user_email = wp_get_current_user();
                if( isset($user_email->user_email) ) {
                    $user_email = $user_email->user_email;
                } else {
                    $user_email = '';
                }
                $notice['righthtml'] = 
                '<form class="berocket_subscribe_form" method="POST" action="' . admin_url( 'admin-ajax.php' ) . '">
                    <input type="hidden" name="berocket_action" value="berocket_subscribe_email">
                    <input class="berocket_subscribe_email" type="email" name="email" value="' . $user_email . '">
                    <input type="submit" class="button-primary button berocket_notice_submit" value="Subscribe">
                </form>' . $notice['righthtml'];
                $notice['rightwidth'] += 300;
            }
            echo '
                <div class="notice berocket_admin_notice berocket_admin_notice_', self::$notice_index, '" data-notice=\'', json_encode($notice_data), '\'>',
                    ( empty($notice['image']['local']) ? '' : '<img class="berocket_notice_img" src="' . $notice['image']['local'] . '">' ),
                    ( empty($notice['righthtml']) ? '' :
                    '<div class="berocket_notice_right_content">
                        <div class="berocket_notice_content">' . $notice['righthtml'] . '</div>
                        <div class="berocket_notice_after_content"></div>
                    </div>' ),
                    '<div class="berocket_notice_content_wrap">
                        <div class="berocket_notice_content">', $notice['html'], '</div>
                        <div class="berocket_notice_after_content"></div>
                    </div></div>';
            if( $settings_page && $notice['priority'] <= 5 ) {
                $notice['rightwidth'] -= $notice['nothankswidth'];
            }
            echo '<style>
                .berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' {
                    height: ', $notice['height'], 'px;
                    padding: 0;
                    min-width: ', max($notice['image']['width'] * $notice['image']['scale'], $notice['rightwidth']), 'px;
                    border-left: 0 none;
                    border-radius: 3px;
                    overflow: hidden;
                    box-shadow: 0 0 3px 0 rgba(0, 0, 0, 0.2);
                }
                .berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_img {
                    height: ', $notice['height'], 'px;
                    width: ', ($notice['image']['width'] * $notice['image']['scale']), 'px;
                    float: left;
                }
                .berocket_admin_notice .berocket_notice_content_wrap {
                    margin-left: ', ($notice['image']['width'] * $notice['image']['scale'] + 5), 'px;
                    margin-right: ', ($notice['rightwidth'] <= 20 ? 0 : $notice['rightwidth'] + 15), 'px;
                    box-sizing: border-box;
                    height: ', $notice['height'], 'px;
                    overflow: auto;
                    overflow-x: hidden;
                    overflow-y: auto;
                    font-size: 16px;
                    line-height: 1em;
                    text-align: center;
                }
                .berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_right_content {',
                    ( $notice['rightwidth'] <= 20 ? ' display: none' :
                    'height: ' . $notice['height'] . 'px;
                    float: right;
                    width: ' . $notice['rightwidth'] . 'px;
                    -webkit-box-shadow: box-shadow: -1px 0 0 0 rgba(0, 0, 0, 0.1);
                    box-shadow: -1px 0 0 0 rgba(0, 0, 0, 0.1);
                    padding-left: 10px;' ),
                '}
                .berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_no_thanks {',
                    ( $settings_page && $notice['priority'] <= 5 ? 'display: none!important;' : 'cursor: pointer;
                    color: #0073aa;
                    opacity: 0.5;
                    display: inline-block;' ),
                '}
                ', ( empty($notice['subscribe']) ? '' : '
                .berocket_admin_notice.berocket_admin_notice_' . self::$notice_index . ' .berocket_subscribe_form {
                    display: inline-block;
                    padding-right: 10px;
                }
                .berocket_admin_notice.berocket_admin_notice_' . self::$notice_index . ' .berocket_subscribe_form .berocket_subscribe_email {
                    width: 180px;
                    margin: 0;
                    height: 28px
                    display: inline;
                }
                .berocket_admin_notice.berocket_admin_notice_' . self::$notice_index . ' .berocket_subscribe_form .berocket_notice_submit {
                    margin: 0 0 0 10px;
                    min-width: 80px;
                    max-width: 80px;
                    width: 80px;
                    padding: 0;
                    display: inline;
                    vertical-align: baseline;
                    color: #fff;
                    box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                    text-shadow: none;
                    border: 0 none;
                    -moz-user-select: none;
                    background: #ff5252 none repeat scroll 0 0;
                    box-sizing: border-box;
                    cursor: pointer;
                    font-size: 14px;
                    outline: 0 none;
                    position: relative;
                    text-align: center;
                    text-decoration: none;
                    transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                    white-space: nowrap;
                    height: auto;
                }
                .berocket_admin_notice.berocket_admin_notice_' . self::$notice_index . ' .berocket_subscribe_form .berocket_notice_submit:hover,
                .berocket_admin_notice.berocket_admin_notice_' . self::$notice_index . ' .berocket_subscribe_form .berocket_notice_submit:focus,
                .berocket_admin_notice.berocket_admin_notice_' . self::$notice_index . ' .berocket_subscribe_form .berocket_notice_submit:active{
                    background: #ff6e68 none repeat scroll 0 0;
                    color: white;
                }' ), '
                @media screen and (min-width: 783px) and (max-width: ', round($notice['image']['width'] * $notice['image']['scale'] + $notice['rightwidth'] + $notice['contentwidth'] + 10 + 200), 'px) {
                    div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content_wrap {
                        font-size: 14px;
                    }
                    div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_button {
                        padding: 4px 15px;
                    }
                }
                @media screen and (max-width: 782px) {
                    div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content_wrap {
                        margin-left: 0;
                        margin-right: 0;
                        clear: both;
                        height: initial;
                    }
                    div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content {
                        line-height: 2.5em;
                    }
                    div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content .berocket_button {
                        line-height: 1em;
                    }
                    div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' {
                        height: initial;
                        text-align: center;
                        padding: 20px;
                    }
                    .berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_img {
                        float: none;
                        display: inline-block;
                    }
                    div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_right_content {
                        display: block;
                        float: none;
                        clear: both;
                        width: 100%;
                        -webkit-box-shadow: none;
                        box-shadow: none;
                        padding: 0;
                    }
                }
            </style>
            <script>
                jQuery(document).ready(function() {
                    jQuery(document).on("click", ".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_no_thanks", function(event){
                        event.preventDefault();
                        var notice = jQuery(this).parents(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, '").data("notice");
                        jQuery.post(ajaxurl, {action:"berocket_admin_close_notice", notice:notice}, function(data){});
                        jQuery(this).parents(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, '").hide();
                    });
                });';
            if( $notice['end'] < strtotime(self::$end_soon_time) && $notice['end'] != 0 ) {
                echo 'setInterval(function(){
                    jQuery(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_time_left").each(function(i, o) {
                        var left_time = jQuery(o).data("time");
                        var time = left_time;
                        if( time <= 0 ) {
                            jQuery(o).parents(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, '").hide();
                        } else {
                            time--;
                            jQuery(o).data("time", time);
                            var str = "";
                            if ( time >= 3600 ) {
                                hours = Math.floor( time/3600 );
                                time  = time%3600;
                                str += ("0" + hours).slice(-2) + ":";
                            }
                            if ( time >= 60 || left_time >= 3600 ) {
                                minutes = Math.floor( time/60 );
                                time  = time%60;
                                str += ("0" + minutes).slice(-2) + ":";
                            }
                            seconds = time;
                            str += ("0" + seconds).slice(-2);
                            jQuery(o).html(str);
                        }
                    });
                }, 1000);';
            }
            echo '</script>';
            self::echo_styles();
            self::echo_jquery_functions();
        }
        public static function echo_styles() {
            if( ! self::$styles_exist ) {
                self::$styles_exist = true;
                echo '<style>
                .berocket_admin_notice .berocket_notice_content {
                    display: inline-block;
                    vertical-align: middle;
                    padding: 2px 5px;
                    max-width: 99%;
                    box-sizing: border-box;
                }
                .berocket_admin_notice .berocket_notice_after_content {
                    display: inline-block;
                    vertical-align: middle;
                    height: 100%;
                    width: 0px;
                }
                .berocket_admin_notice .berocket_no_thanks:hover {
                    opacity: 1;
                }
                .berocket_admin_notice .berocket_time_left_block {
                    display: inline-block;
                    text-align: center;
                    vertical-align: middle;
                    padding: 0 0 0 10px;
                }
                .berocket_notice_content .berocket_button {
                    margin: 0 0 0 10px;
                    min-width: 80px;
                    padding: 6px 16px;
                    vertical-align: baseline;
                    color: #fff;
                    box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                    text-shadow: none;
                    border: 0 none;
                    -moz-user-select: none;
                    background: #ff5252 none repeat scroll 0 0;
                    box-sizing: border-box;
                    cursor: pointer;
                    font-size: 15px;
                    outline: 0 none;
                    position: relative;
                    text-align: center;
                    text-decoration: none;
                    transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                    white-space: nowrap;
                    height: auto;
                    display: inline-block;
                    font-weight: bold;
                    line-height: 120%;
                }
                </style>';
            }
        }
        public static function echo_jquery_functions() {
            if( ! self::$jquery_script_exist ) {
                self::$jquery_script_exist = true;
                echo '<script>
                    jQuery(document).on("berocket_subscribed", ".berocket_admin_notice", function(){
                        jQuery(this).find(".berocket_no_thanks").click();
                    });
                    jQuery(document).on("berocket_incorrect_email", ".berocket_admin_notice", function(){
                        jQuery(this).find(".berocket_subscribe_form").addClass("form-invalid");
                    });
                    jQuery(document).on("change", ".berocket_admin_notice", function(){
                        jQuery(this).find(".berocket_subscribe_form").removeClass("form-invalid");
                    });
                    var berocket_email_submited = false;
                    jQuery(document).on("submit berocket_subscribe_send", ".berocket_subscribe_form", function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        var $this = jQuery(this);
                        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        var email = $this.find("[name=email]").val();
                        if( ! re.test(email) ) {
                            $this.trigger("berocket_incorrect_email");
                            return false;
                        }
                        if( ! berocket_email_submited ) {
                            berocket_email_submited = true;
                            if( $this.is("form") ) {
                                var data = $this.serialize();
                                data = data+"&action="+$this.find("[name=\'berocket_action\']").val();
                            } else {
                                if( jQuery(".berocket_plugin_id_subscribe").length ) {
                                    var data = {email:email, action: $this.find("[name=\'berocket_action\']").val(), plugin:jQuery(".berocket_plugin_id_subscribe").val()};
                                } else {
                                    var data = {email:email, action: $this.find("[name=\'berocket_action\']").val()};
                                }
                            }
                            var url = $this.attr("action");
                            $this.trigger("berocket_subscribing");
                            jQuery.post(url, data, function(data){
                                $this.trigger("berocket_subscribed");
                            }).fail(function(){
                                $this.trigger("berocket_not_subscribed");
                            });
                        }
                    });
                    jQuery(document).on("berocket_subscribing", ".berocket_subscribe", function(event) {
                        event.preventDefault();
                        jQuery(this).hide();
                    });
                    jQuery(document).on("berocket_incorrect_email", ".berocket_subscribe", function(event) {
                        event.preventDefault();
                        jQuery(this).addClass("form-invalid").find(".error").show();
                    });
                    jQuery(document).on("keyup", ".berocket_subscribe.berocket_subscribe_form .berocket_subscribe_email", function(event) {
                        var keyCode = event.keyCode || event.which;
                        if (keyCode === 13) {
                            event.preventDefault();
                            jQuery(this).parents(".berocket_subscribe_form").trigger("berocket_subscribe_send");
                            return false;
                        }
                    });
                    jQuery(document).on("click", ".berocket_subscribe.berocket_subscribe_form .berocket_notice_submit", function(event) {
                        event.preventDefault();
                        jQuery(this).parents(".berocket_subscribe_form").trigger("berocket_subscribe_send");
                    });
                    
                </script>';
            }
        }
        public static function close_notice($notice = FALSE) {
            self::$subscribed = get_option('berocket_email_subscribed');
            if( ( $notice == FALSE || ! is_array($notice) ) && ! empty($_POST['notice']) ) {
                $notice = sanitize_textarea_field($_POST['notice']);
            }
            if (empty($notice) || ! is_array($notice)
            || (empty($notice['start']) && $notice['start'] !== '0')
            || (empty($notice['end']) && $notice['end'] !== '0')
            || (empty($notice['priority']) && $notice['priority'] !== '0')
            || (empty($notice['name'])) ) {
                $notice = self::get_notice();
            }
            if( empty($notice) || ! is_array($notice) ) {
                wp_die();
            }
            $find_names = array($notice['priority'], $notice['end'], $notice['start'], $notice['name']);
            $current_notice = self::get_notice_by_path($find_names);
            if( isset($current_notice) ) {
                if( $current_notice['end'] < strtotime(self::$end_soon_time) ) {
                    $current_notice['closed'] = 2;
                } else {
                    $current_notice['closed'] = 1;
                }
                if( $current_notice['closed'] < 2 && ! empty($current_notice['repeat']) && ! empty($current_notice['repeatcount']) && ( ! self::$subscribed || ! $current_notice['subscribe'] ) ) {
                    $new_notice = $current_notice;
                    if( empty($current_notice['original']) ) {
                        $new_notice['original'] = $find_names;
                    }
                    $new_notice['repeatcount'] = $current_notice['repeatcount'] - 1;
                    $new_notice['start'] = strtotime($current_notice['repeat']);
                    $new_notice['closed'] = 0;
                    self::set_notice_by_path($new_notice);
                }
                self::set_notice_by_path($current_notice, true);
            }
            update_option('berocket_last_close_notices_time', time());
            wp_die();
        }
        public static function subscribe() {
            if( ! empty($_POST['email']) ) {
                $plugins = array();
                if( ! empty($_POST['plugin']) ) {
                    $plugins[] = sanitize_textarea_field($_POST['plugin']);
                }
                $plugins = apply_filters('berocket_admin_notices_subscribe_plugins', $plugins);
                $plugins = array_unique($plugins);
                $plugins = implode(',', $plugins);
                $email = sanitize_email($_POST['email']);
                update_option('berocket_email_subscribed', true);
                
                $response = wp_remote_post('https://berocket.com/main/subscribe', array(
                    'body' => array(
                        'subs_email' => $email,
                        'plugins'    => $plugins
                    ),
                    'method' => 'POST',
                    'timeout' => 15,
                    'redirection' => 5,
                    'blocking' => true,
                    'sslverify' => false
                ));
                if( ! is_wp_error($response) ) {
                    $out = wp_remote_retrieve_body($response);
                    echo $out;
                }
            }
            wp_die();
        }
        public static function generate_subscribe_notice() {
            new berocket_admin_notices(array(
                'start' => 0,
                'end'   => 0,
                'name'  => 'subscribe',
                'html'  => 'Subscribe to get latest BeRocket news and updates, plugin recommendations and configuration help, promotional email with discount codes.',
                'subscribe'  => true,
                'image'  => array(
                    'local' => plugin_dir_url( __FILE__ ) . '../assets/images/ad_white_on_orange.webp',
                ),
            ));
        }
    }
    add_action( 'admin_notices', array('berocket_admin_notices', 'display_admin_notice') );
    add_action( 'wp_ajax_berocket_admin_close_notice', array('berocket_admin_notices', 'close_notice') );
    add_action( 'wp_ajax_berocket_subscribe_email', array('berocket_admin_notices', 'subscribe') );
}
if( ! class_exists( 'berocket_admin_notices_rate_stars' ) ) {
    class berocket_admin_notices_rate_stars {
        public $first_time = '+7 days';
        public $later_time = '+7 days';
        function __construct() {
            add_action( 'admin_notices', array($this, 'admin_notices') );
            add_action( 'wp_ajax_berocket_rate_stars_close', array($this, 'disable_rate_notice') );
            add_action( 'wp_ajax_berocket_feature_request_send', array($this, 'feature_request_send') );
            add_action( 'berocket_rate_plugin_window', array($this, 'show_rate_window'), 10, 2 );
            add_action( 'berocket_related_plugins_window', array($this, 'show_related_window'), 10, 3 );
            add_action( 'berocket_above_admin_settings', array($this, 'show_ad_above_admin_settings'), 10, 2 );
            add_action( 'berocket_feature_request_window', array($this, 'show_feature_request_window'), 10, 2 );
        }
        function admin_notices() {
            $display_one = false;
            $disabled = get_option('berocket_admin_notices_rate_stars');
            if( ! is_array($disabled) ) {
                $disabled = array();
            }
            $plugins = apply_filters('berocket_admin_notices_rate_stars_plugins', array());
            foreach($plugins as $plugin_id => $plugin) {
                $display = false;
                if( empty($disabled[$plugin['id']]) ) {
                    $disabled[$plugin['id']] = array(
                        'time' => strtotime($this->first_time),
                        'count' => 0
                    );
                } elseif($disabled[$plugin['id']]['time'] != 0 && $disabled[$plugin['id']]['time'] < time()) {
                    $display = true;
                }
                if( $display ) {
                    $display_one = true;
                    ?>
                    <div class="notice notice-info berocket-rate-stars berocket-rate-stars-block berocket-rate-stars-<?php echo $plugin['id']; ?>">
                        <p><?php
                        $text = __( 'Awesome, you\'ve been using %plugin_name% Plugin for more than 1 week. May we ask you to give it a 5-star rating on WordPress?', 'BeRocket_domain' );
                        $text_mobile = __( 'May we ask you to give our plugin %plugin_name% a 5-star rating?', 'BeRocket_domain' );
                        $plugin['name'] = str_replace(' for WooCommerce', '', $plugin['name']);
                        $text = str_replace('%plugin_name%', '<a href="https://wordpress.org/support/plugin/'.$plugin['free_slug'].'/" target="_blank">'.$plugin['name'].'</a>', $text);
                        $text_mobile = str_replace('%plugin_name%', '<a href="https://wordpress.org/support/plugin/'.$plugin['free_slug'].'/" target="_blank">'.$plugin['name'].'</a>', $text_mobile);
                        $text = '<span class="brfeature_show_mobile">' . $text_mobile.'</span><span class="berocket-right-block">
                            <a class="berocket_rate_close brfirst" 
                                data-plugin="'.$plugin['id'].'" 
                                data-action="berocket_rate_stars_close" 
                                data-prevent="0" 
                                data-function="berocket_rate_star_close_notice"
                                data-later="0" 
                                data-thanks_html=\'<picture><source type="image/webp" srcset="'.plugin_dir_url( __FILE__ ).'../assets/images/Thank-you.webp" alt="Feature Request"><img src="https://berocket.com/images/plugin/Thank-you.png" style="width: 100%;" alt="Feature Request"></picture><h3 class="berocket_thank_you_rate_us">'.__('Each good feedback is very important for plugin growth', 'BeRocket_domain').'</h3>\'
                                href="https://wordpress.org/support/plugin/'.$plugin['free_slug'].'/reviews/?filter=5#new-post" 
                                target="_blank">'.__('Ok, you deserved it', 'BeRocket_domain').'</a>
                            <span class="brfirts"> | </span>
                            <a class="berocket_rate_close brsecond" 
                                data-plugin="'.$plugin['id'].'" 
                                data-action="berocket_rate_stars_close" 
                                data-prevent="1" 
                                data-later="1" 
                                data-function="berocket_rate_star_close_notice"
                                href="#later">
                                    <span class="brfeature_hide_mobile">'.__('Maybe later', 'BeRocket_domain').'</span>
                                    <span class="brfeature_show_mobile">'.__('Later', 'BeRocket_domain').'</span>
                                </a>
                            <span class="brsecond"> | </span>
                            <a class="berocket_rate_close brthird" 
                                data-plugin="'.$plugin['id'].'" 
                                data-action="berocket_rate_stars_close" 
                                data-prevent="1" 
                                data-later="0" 
                                data-function="berocket_rate_star_close_notice"
                                href="#close">
                                    <span class="brfeature_hide_mobile">'.__('I already did', 'BeRocket_domain').'</span>
                                    <span class="brfeature_show_mobile">'.__('Already', 'BeRocket_domain').'</span>
                                </a>
                        </span><span class="brfeature_hide_mobile">' . $text.'</span>';
                        echo $text;
                        ?></p>
                    </div>
                    <?php
                }
            }
            if( $display_one ) {
                add_action('admin_footer', array($this, 'wp_footer_js'));
                ?>
                <style>
                    .berocket-rate-stars span.brsecond,
                    .berocket-rate-stars a.brthird {
                        color: #999;
                    }
                    .berocket-rate-stars .berocket-right-block > span {
                        display: inline-block;
                        margin-left: 10px;
                        margin-right: 10px;
                    }
                    .berocket-rate-stars a.brthird:hover {
                        color: #00a0d2;
                    }
                    .berocket-rate-stars a {
                        text-decoration: none;
                    }
                    .berocket-rate-stars .berocket-right-block {
                        float: right;
                        padding-left: 20px;
                        display: inline-block;
                        
                    }
                    .berocket-rate-stars .brfeature_show_mobile {
                        display: none;
                    }
                    @media screen and (min-width: 768px) and (max-width: 1024px) {
                        .berocket-rate-stars .berocket-right-block span.brfirts {
                            display: none;
                        }
                        .berocket-rate-stars .berocket-right-block .berocket_rate_close.brfirst {
                            display: block;
                        }
                    }
                    @media screen and (max-width: 768px) {
                        .berocket-rate-stars {
                            display: none;
                        }
                        .berocket-rate-stars .brfeature_show_mobile {
                            display: inline-block;
                        }
                        .berocket-rate-stars .brfeature_hide_mobile {
                            display: none;
                        }
                        .berocket-rate-stars .berocket-right-block {
                            float: none;
                            padding-left: 0;
                        }
                        .berocket-rate-stars .berocket-right-block > span {
                            margin-left: 5px;
                            margin-right: 5px;
                        }
                    }
                </style>
                <?php
            }
            update_option('berocket_admin_notices_rate_stars', $disabled);
        }
        function disable_rate_notice() {
            $plugin = (empty($_GET['plugin']) ? (empty($_POST['plugin']) ? '' : $_POST['plugin']) : $_GET['plugin']);
            $later = (empty($_GET['later']) ? (empty($_POST['later']) ? '' : $_POST['later']) : $_GET['later']);
            $disabled = get_option('berocket_admin_notices_rate_stars');
            if( isset($disabled[$plugin]) && is_array($disabled[$plugin]) && isset($disabled[$plugin]['time']) ) {
                if( empty($later) ) {
                    $disabled[$plugin]['time'] = 0;
                } else {
                    $disabled[$plugin]['time'] = strtotime($this->later_time);
                }
            }
            update_option('berocket_admin_notices_rate_stars', $disabled);
            wp_die();
        }
        function feature_request_send() {
            $plugin = (empty($_GET['brfeature_plugin']) ? (empty($_POST['brfeature_plugin']) ? '' : $_POST['brfeature_plugin']) : $_GET['brfeature_plugin']);
            $email = (empty($_GET['brfeature_email']) ? (empty($_POST['brfeature_email']) ? '' : $_POST['brfeature_email']) : $_GET['brfeature_email']);
            $title = (empty($_GET['brfeature_title']) ? (empty($_POST['brfeature_title']) ? '' : $_POST['brfeature_title']) : $_GET['brfeature_title']);
            $description = (empty($_GET['brfeature_description']) ? (empty($_POST['brfeature_description']) ? '' : $_POST['brfeature_description']) : $_GET['brfeature_description']);
            if( ! empty($plugin) && ! empty($title) && ! empty($description) ) {
                $response = wp_remote_post( 'https://berocket.com/api/data/add_feature_request', array(
                    'body'        => array(
                        'plugin'        => $plugin,
                        'email'         => $email,
                        'title'         => $title,
                        'description'   => $description
                    ),
                    'method'      => 'POST',
                    'timeout'     => 5,
                    'redirection' => 5,
                    'blocking'    => true,
                    'sslverify'   => false
                ) );
            }
            wp_die();
        }
        function show_rate_window($html, $plugin_id) {
            $disabled = get_option('berocket_admin_notices_rate_stars');
            if( empty($disabled[$plugin_id]) || $disabled[$plugin_id]['time'] != 0 ) {
                $plugins = apply_filters('berocket_admin_notices_rate_stars_plugins', array());
                foreach($plugins as $plugin) {
                    if( $plugin['id'] == $plugin_id ) {
                        $html = '<div class="berocket_rate_plugin berocket-rate-stars-block berocket-rate-stars-plugin-page-'.$plugin['id'].'">
                            <h3>'.__('May we ask you to give us a 5-star feedback?', 'BeRocket_domain').'</h3>
                            <a class="berocket_rate_close brfirst" 
                                data-plugin="'.$plugin['id'].'" 
                                data-action="berocket_rate_stars_close" 
                                data-prevent="0" 
                                data-later="0" 
                                data-function="berocket_rate_star_close_notice"
                                data-thanks_html=\'<picture><source type="image/webp" srcset="'.plugin_dir_url( __FILE__ ).'../assets/images/Thank-you.webp" alt="Feature Request"><img src="https://berocket.com/images/plugin/Thank-you.png" style="width: 100%;" alt="Feature Request"></picture><h3 class="berocket_thank_you_rate_us">'.__('Each good feedback is very important for plugin growth', 'BeRocket_domain').'</h3>\'
                                href="https://wordpress.org/support/plugin/'.$plugin['free_slug'].'/reviews/?filter=5#new-post" 
                                target="_blank">'.__('Ok, you deserved it', 'BeRocket_domain').'</a>
                                <p>'.__('Support the plugin by setting good feedback.<br>We really need this.', 'BeRocket_domain').'</p>
                        </div>
                        <style>
                        .berocket_rate_plugin {
                            border-radius: 3px;
                            box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.06);
                            overflow: auto;
                            position: relative;
                            background-color: white;
                            color: rgba(0, 0, 0, 0.87);
                            padding: 0 25px;
                            margin-bottom: 30px;
                            box-sizing: border-box;
                            text-align: center;
                            float: right;
                            clear: right;
                            width: 28%;
                        }
                        .berocket_rate_plugin .berocket_rate_close {
                            margin-top: 30px;
                            margin-bottom: 20px;
                            color: #fff;
                            box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                            text-shadow: none;
                            border: 0 none;
                            min-width: 120px;
                            width: 90%;
                            -moz-user-select: none;
                            background: #ff5252 none repeat scroll 0 0;
                            box-sizing: border-box;
                            cursor: pointer;
                            display: inline-block;
                            font-size: 14px;
                            outline: 0 none;
                            padding: 8px;
                            position: relative;
                            text-align: center;
                            text-decoration: none;
                            transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                            white-space: nowrap;
                            height: auto;
                            vertical-align: top;
                            line-height: 25px;
                            border-radius: 3px;
                            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                            font-weight: bold;
                            
                            margin: 5px 0;
                            background: #97b9cf;
                            border: 2px solid #97b9cf;
                            color: white;
                        }
                        .berocket_rate_plugin img {
                            margin-top: 20px;
                        }
                        .berocket_rate_plugin .berocket_thank_you_rate_us {
                            color: #555;
                            margin-bottom: 35px;
                        }
                        .berocket_rate_plugin .berocket_rate_close:hover,
                        .berocket_rate_plugin .berocket_rate_close:focus,
                        .berocket_rate_plugin .berocket_rate_close:active{
                            color: white;
                            background: #87a9bf;
                            border: 2px solid #87a9bf;
                        }
                        @media screen and (min-width: 901px) and (max-width: 1200px) {
                            .berocket_rate_plugin{
                                padding-left: 10px;
                                padding-right: 10px;
                            }
                        }
                        @media screen and (max-width: 900px) {
                            .berocket_rate_plugin {
                                float: none;
                                width: 100%;
                                margin-top: 30px;
                                margin-bottom: 0;
                            }
                            .berocket_rate_plugin .berocket_rate_close{
                                float: none;
                                width: 100%;
                            }
                        }
                        </style>';
                        add_action('admin_footer', array($this, 'wp_footer_js'));
                        return $html;
                    }
                }
            }
            return $html;
        }

        function get_plugin_data($plugin_id = false) {
            $host = 'https://berocket.ams3.cdn.digitaloceanspaces.com/plugins/banners/';

            $plugins      = array(
                array(
                    'plugin_id' => 1,
                    'id'        => 1,
                    'price'     => '35',
                    'slug'      => 'ajax_filters',
                    'image'     => $host . 'Filters.png',
                    'image_top' => 'data:image/png;base64,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',
                    'title'     => 'WooCommerce AJAX Products Filter',
                    'desc'      => "Increase conversions by making the product search easier and suitable for your customers' needs",
                    'desc_top'  => 'Get nice URLs and correct variations filtering for your shop with WooCommerce AJAX Products Filter for only ${price}!',
                    'url'       => 'https://berocket.com/l/filters-upgrade',
                    'bg'        => 'white'
                ),
                array(
                    'plugin_id' => 18,
                    'id'        => 35,
                    'price'     => '29',
                    'slug'      => 'products_label',
                    'image'     => $host . 'Labels.png',
                    'image_top' => 'data:image/png;base64,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',
                    'title'     => 'WooCommerce Advanced Product Labels',
                    'desc'      => "Capture client's attention on needed products. Create labels easily and quickly",
                    'desc_top'  => 'Capture client\'s attention on needed products. Create labels easily and quickly for only ${price}!',
                    'url'       => 'https://berocket.com/product/woocommerce-advanced-product-labels',
                    'bg'        => '#f2f2f2'
                ),
                array(
                    'plugin_id' => 2,
                    'id'        => 3,
                    'price'     => '29',
                    'slug'      => 'list_grid',
                    'image'     => $host . 'GridList.png',
                    'image_top' => 'data:image/png;base64,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',
                    'title'     => 'WooCommerce Grid/List View',
                    'desc'      => "Users need option to see more info. Add Grid/List toggle and Products per page to show more",
                    'desc_top'  => 'Users need option to see more info. Add Grid/List toggle and Products per page to show more for only ${price}!',
                    'url'       => 'https://berocket.com/product/woocommerce-grid-list-view',
                    'bg'        => '#5f4a8b'
                ),
                array(
                    'plugin_id' => 3,
                    'id'        => 5,
                    'price'     => '29',
                    'slug'      => 'BeRocket_LMP',
                    'image'     => $host . 'LoadMore.png',
                    'image_top' => 'data:image/png;base64,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',
                    'title'     => 'WooCommerce Load More Products',
                    'desc'      => "Load next page' products with infinite scrolling, AJAX pagination or load more products button",
                    'desc_top'  => 'Load next page\' products with infinite scrolling, AJAX pagination or load more products button for only ${price}!',
                    'url'       => 'https://berocket.com/product/woocommerce-load-more-products',
                    'bg'        => '#5f4a8b'
                ),
                array(
                    'plugin_id' => 9,
                    'id'        => 17,
                    'price'     => '29',
                    'slug'      => 'MM_Quantity',
                    'image'     => $host . 'MinMax.png',
                    'image_top' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ4AAAB4CAYAAAADvRzNAAAABmJLR0QAAQABAAGy5shuAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4wgKDi8pWfnlIgAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAgAElEQVR42uydd3hc1Zn/P+fce6ePNOoadwO2JYzBppkAAWMgJJBsSCe0hM3uJtlfQjok2WQJ6ZiUTW+76SROp8QmBDCG0JsBY1vuTdaoa3q55ZzfH3cky7ZkbGPTovd55tFoyp2Ze+75nu/7fcsRTNgLYouvTdcAYaB/xQ0JD+C938gxWIY/fDo+cYIm7GVlcuIUHHHAGL57JbAV+OziT6Znvu3L2UChzKuDghjA5V/JTZysCZsAjn9ScNjjPsCKGxLDd08Hwhqucz0+Epbi/cB9wFUAdTHNO76UJ5NJjbx39P0Jm7CXkomJU3DYwKMOmLTihsSaYfAYBRosvjb9BHCiq+hojMqfx0Lio0rTDHTHI/rSH3yo5p5RgBEHIrW1yZ5MJkVtbXLiBE/YBON4Jdm5n9zDFVm++Nr0G1bckGDFDYkR9nHOtWkJTFWacjQgNsRD4l0amqV/9ls37DSWAGjdWZvJpJYAjwDXAROgMWETjOMVzjgeqLojReD7K25IfGL4uQs+lbZsD1saONPqZFEjavuzEq30UNmVSz7yxsLO1yxwLq244jSlqQUMoLu2NjmBGhP2kjRz4hQcul3+lRy//lScRZ9Iv19r5ghBGQgBH198bXqe1vzbPUsSnY7iLQhIxg0rVyJaqMjMvOnurf95UfGhSQn9PsPSx+crEq33QPL6TCZ1RW1t8lcT7sqETTCOVxZw1BuSf3E8nSvZulJ2eHXF5TyNng9INA8Dd2r4YEPErKmv0YNzpzlr33lWpdTcoNpLZTHN9QSeAjn2SKyurU0eP3GmJ2yCcbyyrMFTfMWQorUmLJxEhGeFIFas4ORKIojgNCHd0/qy0DLFLV1+dsV+1fHOCbiyNp2TCECIcUHDZx3Z7lNqa1ofA3A6rsVqu2HirE/Yi24T4ujzOXmCIQFPSAGeh5UtigV9WTG9WBZdsyaptR//l/LOY5KqErLgme069IXfByZ998/B2jXbJYmIJh7VKDXWkTVCCEwr0ALm64cfdaddMHHSJ2yCcbzcbdeQ9Bqiyi1UBLYjdjQl1G8vOM5OnLvAWdhSr5O4uu5rt0WsXFkwJylFf05xwy1BfnO/4vgZijec6nLhSS6ZohhhHUIIAoEQjuPQn9pUbijes9h+9qNdYsopP7Uii52Jsz5hE8DxMrV0OkUikeTuG6JDTW8rf/qKcyrLPvm2QlssxBukxVGOLQIRU8uv3xZgwy7Bled6rHgyxIffWKQm4vC95SH+9w6LaQ2ai05xEQJM00QISaGQpatrG8VyiZqgjpqmOBMz3qz7N60E1tsbriMw+/qJQZiwF9UmxNGDtGo2pykgIgTtptRfDQX0okJF4nq+ZiEEFMuaa34eZmuv4L7PFrjoxlqmNXp8530FzKBm2YMW5xzn4QqQwiCXG6K/vxutPerrW2hsbAXhUe59mGjlMdDipyA+EJh9fcne8FkCs78wMRgTNqFxvIxsGvAFDWuU5mFbiUXposRTPmAAWIbm2W0Gj2yQXHyKixeGxcc7rN1hsXq7hedIXneKouwW6e3pYtv2DorFLNOnTmd22zywWrntYc0Hvhvh+j8dS7YcQwr9r8BrAaxZn6f87DUTIzFhE67KS9UWpy9mReLm0Q/NAj458p8eIyqiBVt7Ja6Ci052qNgGF5youe1RzX1PV5hckyadLSKloLYmyOToTAaLcZ7cpnhgteDBdRZlVzC9SdF2TB1ecBoYG8FVNzqbP/+YEKKz+LfzJwZnwiZclZeZu3IvcNb4NE6TLgoGSyZHt0ClUkHbQ7z/B5JtPZLv/afi2JkxXC/Epm6TB9ZIHukw2dkvaYhrFs5xOP1Yh2OnetREDdTgIxhDD0EwiDRDvzRnXvuu0Z9X+OvZRF9/78TATNgEcLzYdmXuA/wy/l0Azku/ea5CfQW4ekXi5m2ZTGoxcPfY79RYVhApBblsL/0DgyAMmhuiPLmploc2Bbj0bEBIVjxlsOzRALYnmDfd5ezjbI4/yiUeBsvUAHjKRBd3Yaz5ElIVAFHCq9yP697hDW6/M37ltmcActdcQnzJ0jG/0f6em7AJmwCOw2zvyn3gmF+s+e7ms9vekDIwWoCPrkjc/M1cpiegUGn8xjw+y5ASwzBxHJt0eoB0up9gKEJdXSOhYAQ/N8N3a7QGDaA1rhLYriAWVJgmVFxRfXK0QxnHWPMF5NAzIDXYJU2wxjOS7SURrlkYOOoz60aBRA1QByggHV+yNDcBHhM2ARxH0AZT/dQnG0f+vyz3/1aaGKdmVPryITX0TUMYkwXi1LsTf3lyS3rdn5pF3Zs9NKZpUSoV6e/bRbFcJBaL09Q0iYAVwPM8bBe0FuSKgoGcJGApWusUIesAv5gMQGYd1rovoytFjIYZGC2z0coD5dyvukoXOct3hqvay8VAEvCq774N+A7wwDBwTIDIhE0Ax+HSLVID1CYb9njsnfn35TU6amDgaHfDkB6cbevKzwPa/JYhrL/8Utw4o5DN6WImI6QBdYl6auJ1lJWFW3EJBRTFCtzyUJBtPZJJDQpPKb5+W5AvvrPEW8/0cL0DGyYtTKzVn8JK1CGiDWi3DJ6Hzru493f/UXUWz0AwXiWcDXwf+GJ8ydKBidGesAngOETLPXw58dN+vcf9TGogDiwM62DlSXPtzF+F/vJhS5uGQISEIFJLbXCb2ta0hrWcqxe705mSe03hxLqGSBjPqKezX/NEB2zrNfjXC8o01mg2dxls7xWcPMujtdGle1By6Y1RamOKpR8vUbbFSBh3HLoBqoR0eggUH0fqLNotoXMuakceb10GPVQ50J/9HeDT8SVL8xOX/otnqVSK5EF0TDjY178Q9k8Rjk3fuRiAxPkrSN+5mMT5Kxg9W4cBBDgKuK0kKul53hzn2/nPZRSe6eIahjB0STvy38IfcYQwrB7d7X6u/DHZTZa7Vlvct1qyepsJQnDZIh80PAUzWz3mTNV4HuQKBnURzeJ5Dj+5K0DHDoP2GR6lihgbwbWHsLuwyh1IZwdC2KiSg9qSw9uQRfeW2V9p7Rj2AeBPwL0T7sqLBxjJZJJUKgUwG1gMHAPEqwt5DtgM3JdMJp8FRl7/UgIP8c8CHFXQiCTOX1EcYR2PXHE60Bdf+KuNVXflJODxvU+MoU0qWvGeyAcwhUnQiLPd28xJ6gQ+3P9ffPEWyFXgNfNtFs5xSTZoLGN3cw1VvauBcEDz6AaDd/1PmLec7rLkqjKZ/GjWIQCJkCamBGNoGaKwCowavM1ZvLVD6L4KOOpQR/AO4F8AewI4XniGkUqlJPA/wCnAVGDyOG/rBnYCq5LJ5HuHjzMMJC+2/VNkjlZBwwK2p+9cfEv6znNWpv9+zkUg/h9a/yP38OXzq0v8yHtKGLjVmVkRLu+JfIASFT5R+RAdxibuyP8JjaY26nLtm8v88P05rjy/zPQWhWloNNXoyajmPAKwXcGCGR7zZ3rc9ZRBT3p3gRtCgFdCZh4h0PdrQrKE1fw6dL4R564duPel0KkSuMo/2KHB/gXApfElS8ldc8nErH5hQeMqYAfwQWA+sB54LzAPaKze5gL/CjwJHAf8RyqVKqdSqbdPMI4Xh3WEqyheg1YpGay5VYTq3o726kCU0O5PVNtvfqDKQ+sqSDQCgSaMAqW5y3yAk7355GSOd9d8jAeHbqGMS1F6hEURHfQQ8rlxWGuojSt+vSLAh34c4n/+vcwV5zukM2DYvVj5hzH0IDgZtO2ht83GfexhdLnAc4ghB2Pb40uWzoCJCMsLARqpVCoBfLUKEgAfTiaT3zrAY3wO+AhQA6xIJpPnjjrui8Y+/mlqVRLnrygBmers7RHhhjRa1QHg5ZEqP73glM+siAC6iqcagS0kpVKR12XOpr4QIV/KYhfLVApl8pUBhLsVq+t/MIpbDhiqK7bgnLkuzQn4y8MWlIsEyusJ5Fdi6G50NofXUcG5rQt7xe3oUv7AQSNoQFDmgP0pptNz11zyX8AEaBx50GgEflYFjVuAltGgMex+7P3eYUsmk58D5gC/BxanUqlVw+7NKK1kAjgOhwg63v/AIKCFFS6BeAfaQbgDO1Voxk1D7bc+iuYnWrt7vMFRUHQctthbGSynSVcy7CztpJjfiOhZRt3Or2GVNiILG9k3c2tsileqSCY3Sy46qUjE20x2170Ey/ch8n2ozVmcu1O4/+hCDylEIAjywIZJRE3MV7dgvW7aLhG3fgOU9vPyz+auuWTeMOuYsCMCGhHgB/i5NV9OJpMXJ5PJ3tGvHYsxjH6seqzuZDL5DuALVRfnJ1UWM8E4DgdoVLWMo/fSNka/bADtCRFumCe83AzhZToqDW9fUZh241xpF74klL2P9yaEINzQwJNTt/DpY77LH6feyX/L04iaf6bJeBKkiTITGJnHDtDzkwgUdjbF+xeu4CsXryCstuFuzODe141zbze6uwSGPLjRESCmRDHnJJCt4TbrLTNSePru/bwjAHxqgnUcfhs1mb8BvBX4HnD9eAzjAI8F/pYZXwJeB3z6UI43oXGMzzjuBPrx9yb5WeL8FZmR5+8463eY4bebkQga89lS8uM73PAJJwptt6LHZwsBK0DK7afTW0Fj+mHaiwrTc0CMmtnKxml+HV7TebAXa9ktcIAopzBK6zBEH6bI4nXmcZ8eRKWKUFbPa1TEjBiBC6aAp0EKx3mg+xq1Jv3N/Z0y4D3xJUv/PKF1HHa2cR5+1u5OoD2ZTHrPR5MYddww8Ff8MO6ZyWTygRdD63hFAEf6jjNIXPAA6TsXHwcsww9z2cAA8BkhrJtqz7vDTt+x6AEznjhdBaZsLU76ZFrLhjZUJezrB3pMtmGaBj3dfcRLj9AiHwY3P7ZLIgN45X6cBT8Ebwx5QSmMwrOYxSeRpoPOVHCfGkBtzkJFHZaREAGJeVYr8qgaPwYsebz87bUrRMjYX/OOW4HLJpLCDjuAPAacjFJtycmT1x/OyZ3q6joRIf4KlJLJ5NETjANY37GOOW3tB/x47pHLiC+8yQeQvy96N0J+DT9yItDKQAiBZjtSRFBOk44da5eO+e+8tprr0EpgmMhKBS2DCGd35EJKCWg2b9zC0fyJOrEVLUxfbxDGmOChy704J/0U5e3VGtQrYWUewnQ2oD3wnh3EWz0IJe/gRiAgd3+sPQbYaJAzY5hnJRFBnw2pvsqvnd9tOYegHC9fIAdcHl+y9NaJ6T62bb7lz/Q8sEpEmuv0/lQs7XmceO0nSHV1XQL8VASDv25taPiPZ3/6c+EMDu7zVqE1gZoaIi0tmOEwGAaVUolSKoWdTiOG28mNsns//lE+DKS6U39BcbGQYnZra+vGp7/zPaEqlbG/ntZY8RhDqzeLV39viX5FAcdgXxf1TZMOCVxyD70JM3m2Vdl46+uFNOdpIZqFEZiJME4RQjRJ4eJZkx2dXm0JlB/qNGvQoamo0HRUZCYqugBVsxDh5ZFSkssV2NXZR0N9kGNq7kcUN6BdB5wKaA+cMkgL9LB7IdBOHnHy/1EqpRHCAK+ArOzEKj2DKHajusp4qwfRqSIjeyMciGkfNKxFrcjpMbwdRbyHetAZe8+sUQ0EJeYZLRizagG0SleEs3RLBilq9/MJd1fBo3sCJva1Lcv+Es52bHuVUsoUQuxv4gk3my00XnThf5h1iSsLT69+Z3Hz5iFhmmpvJjtcHe3ZNl65jPI80BppGBihEEYwCMLPKNaj4UoI4fT1Fxsueu0FgZaWT7tDmb+kfv3rr4RnzGjY84V7znLtekjLtBLHHXPnjNe84Xk3vX5JpJx/90c/GwGN9RvWx1FqKjCjKuAp/OhAH7B1Tlt7Zhg0RgBEhk2354lLjJoplwph1Gl0FMSxQpclXnnICx/fX5z6sbrw469vwIgIEW/EMxrBzWDknsBK/YHK7C+iEq/CsxXd3QMUi2WmzpxBTbRCqbdMhDIi3ABRA9wyXtdajHgz2q1URwZQZSwrRDlfRNi9mPZmZGELDBRwnxlAbc/7NavGQeK1pxAtUeT0OAgwpkfRQ7V4j/btuwyUPZ/NGBIRlsLryIC/reT+7Nzq7aYJrWOM01+qTDJror9zMvlGP6Nv/PEza2oY/PudKNvVMhz8rREKoat7YPiAoXGKfvKyEQoRqKlB1tf7x9Qa0CjPwyuVcUsl0NpnI6PZQ1Mj6ZX3oVwPIeRbwjOmv2V/Gp32NEJKrJoYdrbQVNUAX77Acc7rL+eev/6aD7z3quo5cd6z+pk154dCoTat9dTq9zOqwNEPbFzfse5O4Hdz2tp7hwEkvvA3bu7hy4IgXqu1AmEi3H4g8GC56dKsXffG47RXrBNeRhBsxLDArV1AOfEGhDOEdIYgNhNdGaS/P42UBlOPaSdiP4W59WeYxadx68/ENP0MD5XvRxeH0OEECNMXQ6UFTh7h5ogYObzscui28danUZ0FKLg+OzAOhRcKdN5B2woRkCAExsw4anse3VPekzdKge6r4N7XjTAFOlddXDz9XID18dw1l9wVX7K0ZwI89jQnX9JmOJSVltlY7hsC7e97M57JcBgZQYDYDRpS4hQKeKUydce2UT93LkYgQCBRixWLIQwTIUC5Lk6hgJ3J4pZKDHV0MLh6DVYkghmLooeZSSSCHAax/YGG0ggpCNTXYgQs3HzxsLgqLypw3PNXv7jsVee99fVmwPpob6r75Hg8Gi+VKhjGHjMsip+O2wacB1yxvmPd1+a0tf9+fcca5rTNBa1vQwbuFso+F683r42GH+enfXmuNlpORVj1ZvpBhMpTanorRjCGMlsRwkKHZmLEZzMwMIDrDNHQEEeaIQLpPxPq+T5gUE5+CDeygFjpdgLBCmqoE4REFfqRNUlQBigPYQZQbjeiYKLu78Tb4UCxWjf/XCxDUxU0xb4OpCFgsIK3IY15QgN4GpEIYsxJ4KZ7fb1jb+ZR8dAVwBTIGXGQOGprXuHp4DgO6nzgY8BEF+S9cVv6ACBNg1BTPeX+oZEJud/xHBakhKA8MEj9ccfSunAh4ZZmzEjEl6pyOSqDQ3jlsg86wRCh+gTRqpBa195G8vRX0f/U0/Q8/AjB+nqkYez2Svanumh/oQvW1SACJlqpA8pufslqHIsuuoyVy25i0UWXHaXh8yHLetP2zu7IW990Pp/+4FVs2d6JZT1nlxsN/Mectvb/HX6g8I/zvg/qRLvh0i+Vmi77nnDdKaCECtYQ2vgZrM5vUzz5cVSwluDgMgKlp7Fn/BcdG3Jot8Qxs49ByDzC7QW3QDD9N0q1b8P2JlHZ8iyB0tO0HGvjrfkDIhgH5WHEW8AMQjCMliH05gDe+hJ6cBcHnIhR1SZEXRCdtqHijf2amEXgX6YiYv650QUX56870Vl7HI6tEcfUEHjNZPAU3uqh9e6jfZPRxMb5JhUgGV+ydGiCdey2db/8+VH4Yf6jfMqpKfUMVsXL535/JZ3hqDdfTNOC+SMTetd999P76KNUhoaQloW0LLTWaNdFOy5WopaWhafQunAhRiiE9jxyO3ey/ue/xAiHkZa1f9CoXjSBulqMUADtjSwuje1Xvvt592SRLxRQjLovgcCiiy77ArBWwGUVx4k0NSb47R9vZ8PWnbS2NOI+d4cbAfxkfce6/zdCnzKPX5Odc/MvS01X/lG49lRQvvggwCg8gw40oKIzENpERqbhDT7KljuvI1YToX1eO9LeTHjLJ4h2/DuG00e59UPktuTJ3r+M3JOPMNhRJrspXc3fEGjt4RX6EeF6dHcQ9699uA9uRg/sOrhTG5SYr2omcPF0Am+dAU0hX9nZ+9dmbbynB9HCf0C7fh3NuKZ0VST11XnZnpghGkIVxk9xDQLfBD8pbCKjdGyQF1ISaq7jQDKF3UKB+R/9EE0L5qNsm6233Mqj132e1H3/ACDU1EQgkcCMRLAiEQKJBMGmRoSUpO67n8c+/0W2/OnPoKFmxgzmfeD/YVgWyrb3/zWVxqqJY4SD6APrFvXSAY5hwFi57Kbh/5PA5cA64DPVCxWAQCBILBLix7/8E9FwWFmm0aO1XodfQdjDvlNp2P57fUfHOb09+SkDJ3f9nyL0PeEWAqNnnHAVymrAbbkc4RVBWKTsBWzKLWKq9ztmBlfgDdxJdPtHkc4ghbr3M9SdpP/2ZWQeuAunu6vqf0oq3V1gBEEopFmHELOxH8ji3N2JHipXd2Q6uNMqIiZyctS/KGMW1sKmsUfGEHgbs6inB1G7Cj6IFN39jK5Ab8n6PTs0iKAZNObVlxCU9/N1rsxdc8k5Ewgxntwk8VyX3LZdqOEduMbQpJRtg9bM/9hHCdbVkd20mWe+8136Vz1NsC6BGQ37boNS/m2YPVT/F0JgRiJEWlvpf+ppnvjyV0lv3EiosYH291xFMJFAOeMHRwzLJL8zhSrbCMM4/OfhSLsj1fu1+OXcVwNnjH+dS/qHMju//aVP3H7qyXP/sHHTji2hUBD8BjuvAd5Wjbbs5YMa62KxprIQcsG+iVzaFzCFBFHGtR16UllcEWVy0qRu0+UoI4yUiorTStq5gEK3iduzDVXIY8RiI/XxSkkmT3+UmrosghZ0ZwhvUxoqjp8ifqiDEDYwzk5iTIv5DCIgce7uQq1N76uNjGghVSjdSxgdC2ys8yf7OoerQIJzR2ef2lZoGmf0NbA8vmTp62GienbEVdHcKUx5lHYV6Q1b8SoO0ZaGfYFDCLxKBcO0OPZ9/04gHqfn4UfZcccdGMEQMnAgLsbeE0OC52Fnc0w59xwmnX0W5YFB1v30Z/5HGsY+x5SGJN/TDwgSs2dgBEy0z04Pi6tiHinQGHX/rcCV+Cmy0f28raQFv5DopYsWvfph9qzu3ALctb5j3R34+f/H78XL2h2nTCAQHRMbBR6mFcCtOPT1DBGOxqlvaiBYvA8dTOAWTQqpCjnvVDyngs6nkGgqto3d20uorg4RDIKrMKhD7wihehR6sN+fwM8FGhpwq405zH1nqy55qA0ZZFMIETLAUZjH1+PszKPz7p6AIKpiqQYsgZxVg6gNoLbl/F4dewONp/FWDyKTEf+zhUC219WrreNW3ArgnNw1l7w7vmTpzyc4BijXwwoHcW2bzPrtOKUyZjiIHiu6ojXCkMx80xsJxOP0P/0MO++8CyMYPDTQGGYhQmDVxNl1730YoRAtC0/lmHe8nbU//DGh5iY/2rI33hgGXsUhs3E7iTkzkIZEuYclqHLkXJWq+Hkd8BPgDc8BGrcDb/Q875qnHrjtXqBy8aXvG3lyfYff/X9OW/vdwH/h99UYNVYaxy6i1L603TAkUkq2bt5GxbZpaJ1MU3Mtob6fYG39HzKdUfo7j0e0XM7Uc17H7DdfyJwrLuOYd76DtisuJ3nmGXiuh86laSjsxFqTR20oodPVSSqeO1qiTYE8LoGcVcOwuLb3a1RnAd1d3E2SaizE9Dg4elz9Qk6PYZ7SiHl8PdY5kxC1AT/suvdLu0t4G9JgSUTQQPeUjOfgmhHgqtw1l0ya0DqgdsYU263YamjtZjzHxRhPmBQCJ5+ncd48ao85mmJ3D5t+9wesSBgZCBwaaOxxeIEZDrPjjjsoplLEp01l8rmLKfX0jn0dVvUY5boMdWwBIYhNa3FfssBRBY0TgY8Dif28dC1wSdUFufO+5b/JAZz3hiu5+Tc/HHnRnLb2EfAA/oZfPLTnwurZuG55L9AwcByXjo5tmEISSdRi6TSBTZ/C3XIPnR1zGew5icnnXcnkRW+gsKuL7X+/k02/+wO77llJflcXjXOPZe6rFzC9soP6fCdGuaotHmjSp6sIvWYS1uktWGe3Yp7UuK9LMZy4tTGLdnZvQqsHyuPnfWiNiFuIgP8CEbMw5tWN7a54GveJAZy7u7Bv24731MCBZK2eSXW3un9WV+WBaz8DQPdjq9sG12yuAeGHZscRRZXjEEwkaJx/AgAdv/gV4eamkSzRw6ItSIkRDLH1r8vRnkfyzNMJNzcjlBp35fK1FOh/aj19j6+dBPDgJz/7ktU4FgPLRwugo4h7D36fgm+sXHZTfm9NZDwbzhRd37HuNGApMH30YQ0joMOReiGEgWkaZHN5Onf20NraRH1zEyZ5yo99HbtrO0MD7SgvyLFXXYFbsdl28y0UenvRjoMMh1HlMjFdookhYm4BLcTBd+DSvlsRfOdREDZ9sTLr4NzT5ZfOi33BQM6II2fG0aki3rr0+I2IPY2YEsU6d5Lv3gCq6OCuSKG7ivu+T48KAkjGqukby7YAF8SXLN30z8o2Hrj2Mydqz7tJGLJt+ITpamp4qK5mj7wI5Xk0zjuOaa97Lb2PP07nXSv8sOnhFialJL9zJ21XvZuGecfR8/AjbFt+O6G6upGEM2lIiv1DKMetCvZ+ophWqiSlcfbpN3zhsZdqVOVeYOtej6Wqrss5K5fd9PmVy27K7x152Z8NZ4rOaWt/uAoc3mgM9DxbOE5Jm6Ys9nT3k9rVz4yZU2mos/DW/oihlb+ib3WYgd6TsHMuydNPRZgWG3/zWwo9Pb6K3dxETUgwPVRguu4mpopoKQ+tbZ8AHIW3o1AVMzWixsQ4Kjam1oEQqC053Lt24a2pgsZ4iWOGQHcVUNtyuwczYiGPqQFTjv1dZHUFqgtinNmMsaAB4pYedwn1Rem35K65RB4ud6Xjbze8bEDj0c9/5ShpWb+SgUCbMEyEYYy6SZDSTwKrTkwzGCQ+Y6bPUh58CGn5i8Xhvmk0kcmT6Lzbb7cSmzqlmgdS7XhfjewJufu7CikQhkRaZhhT/vXJr339uJekOLpy2U3eoosuewd+v8RjqyDy45XLbloxWkA9EMAYi3Xgd4q+FL+EfmR2OHZB9+UrXy0U7I8eM2tqQg9uIPfYCsqpIcr5WpRnYAQ9Ik0N1B17LFv+/BecQpFQfQOWWySe3UG83I/lVdBCjrQR3C+rQO9ewcW+boj3RB9yShQRt/wK1qNrENsKfrHbGIDg52HhVQgAACAASURBVF2AnB5DtoZRXUU/bV3t+9nuU4OIyVFkrQUIZEMIL2RAwRkb7DyNeXozcmrUL6KqD+Le1z12ta1vVwO/iy9Zuu1wRFjaXnstz674EpXBZy3LbDBMMz5ubdaLq4gaMFh/Ishjx04v35uB+tdAfOZU8p3bMWMhhBllrBJm8Tw6TY9mHZVMGqdYJNjQQMPxcyn19vjFcVWzamOMk2vSrJX+GHDVSwo4qhoHK5fd9Myiiy57L/6WhEMrl92UHf2aQ7FRrKN7fce6G4Fv7ylAe9IKekZLtvDj/JN3XFPZtgm3BFonEZaHGfTFolBTM3YuT6arl3g8RDy3g2h5iKBX8CsSDyQXQ2uIGBgzayBk4K3PQM7dk8dJ4e+2tnoQ68wWcDWiJoBMhvF6SuMmcIm4hfnqVj/HY3Ytzt1d6M7ivuHXjI16egBxWgsiIND9ZSh741+YlvT7knq+7yInRYRIhtHbC+P9yknAV4B3Hi6tI+Q2Rs3o/Pd57tA5Wmv7JUk3pCdo2NUqvDCMVRCrx9AtBHSv+Qt2NoOoz+z1vEDjIYSBqDSD8/xdGCMYIrt1Cw1zjyOaTFLq7dnDdRLGftWI51Uhe8RqVUaBhw1s34uNPK9jj2Id3wfewV65IVqJT5bT3f/nbOnCLQfQHkjTqz6n0FoTamqk1NVJk9dP3VA/lltGVGnDga9/EpIJ5MImpAlichR32c59wUAK1Lo0anoMOc3P9taDlfEFMw0EDUTYn+AiamEc34DbU/LDunsxFG99BpV2EFEDtavog8J414un8Z4dRJ4zyT922MRsr8PpHQacMe1tuWsuuS6+ZOmGw7KYq4JpyOBJGPGLXHcIISzgJcg6hI02Kwf1lkzX0z6j2McVVSAkptWIstXhYVlSUtjVScPc4wjEa/xiyNHHHV0vc7hx9Uie97EA4vmCxugoy5y2dg/44hhMICinTX9VsL6hK9RUj1UTYaQcWgg/425bB9FHbqWp2EnALVfn2cHRRy00BTuBZ/uRZqM1gpxds3uzpL0mrHNPCueeLuxbd6B2FsYfUwE6a/sgYEpwFMbUKHJSdOz8WQ26u4jakvObA2n/88Y8viFQG3Ponmo4WWnktChycmS/ixvwlsN4aWitlWsYMUyzobr4vVSb0YmDuglhjJQk7Ln5jYFlNnFo5dHj4YbEzuer7EIezu0zXlzgOJI2KkS7An9bw724lNmuWlpXGaaBEQ7g2g5IQcDOkyztpHbNCti19SB2TxzbVZFDBXKdQZTntx8059UjagJjT9qii1ozhN6RH+4LOv6xywrv6QGouCPbwBmnNe//ulMg6gKY503CeuM0xOTI2EDjKZyHe9HDSWmGQISfk3yecbjHUGsPw4hiGAl8j+WVu82PadYjhHnYGYDwdjeSEv8swPHoR/74vCnbnLZ2G7+DdO/errRsaZlHvGadVygjsoM0ZHcyOdNBnT3gp+keUImxGFfvEEIQLg9Q2OCQH/JrTagNYMxNjO+GGP5qJCdHMM9oxjihztcd9L4Lneop423LVwFGI5tDiMR+EomkQB5dgzGnFjklinVWq994SO/7Ot1dQq1Po8seqr+ixxRq97QjokVo7WAYtZhmPXsEyV4h5vetbUDKkB/1OFznrco4HOWNyG3ynwE41n7/EU795ltHUHLVdcunV/8etFAKbAJu2s3bNVpKvJq6RplsJpHe5k1XXdTnd2IoF3WARWhaQK5hGunWOZRjDWNfGK5NPLOF7NYInmf43lAy4k92PTZLETET87RmjHn1mKe1YJ7SOPZK5ChURwbdX0F7Gm9DBp12xqekw+nonh4RYY0TGsYVYN2H+3D/vku7f+vUaqDyXAi+/ki5AlrbGEYcKWvQ+pUDHlq7SKOuChrOYQck0PzwoUcAeLa7m/V9/QRM45UJHGu+/QAAx/7nQgBW37jy3FWfu/3rwM2rrlt+7ILrLzwU1lFCiF+i1FqNwEk0oY0AsWcfCjfc9duZ9YNbCQu3yhwODJWFVtiROjLJdnLNx5BubccO1eyz2mshCRf6MTv76N9Sj0agdhX23DR276UiUI1s2Ao8jZweRzSF/SK0vb2PVBFnZQr33hTuP3pGKl3HdlU0amcBlbNHUtvNWTWISZGxwcNRqK6i0Fn7QE7M8iM0vZAygPLKlIrdvkbwikEOSSm/E7RXFYAPm0BEayzG8rUdrO31ifaubJabn12D66kXxOGTLzRgzL36jOH/z13znQfvkUHrt0LwUbSeD/z0YFnHSB3LnLanvGj8j15toxvZspbW336dhmW/EMGenSFtBgx98KODE4yjhUR6Dk4oTrFuEtowx4J/agc3E3p8HZXbOvGeHhyfFUiBHqrskbglIiZGWy0EjDFBQfeXUZtyfvtBQ0Kg2i5urNemiqgN2d01KwED49jE2IlkwwzluYWee4H7jwRoCGHheWVymXVoVUGIV87mgkJIXDdHLrcBrb3D9ts0YEiJC/T29pDNpOlKpShUSiPX48seOH4QftvegPGpNd9+YBuwDK0XIWWTDAaG58DCVdctn3UwrGN0HYsORn6V/PUNG1p+uwRzsAsVq0EFI4ckSGkhCeYHMFx7ZI4VEpOxQ2NX4ErlECoMILrzfsjU1X50ZZyP9p4ZQuftkYxOeVQcOSU6vgsCEBRYF00h8LajMBY2+xEXPQbr2JRFDffpECCqkZlDtDT+7vb68Ba7aYS0UNohl1mHkOYLKu69YFqAkCjPppjfOsrFeL7HFOxMp7nipBNJ1tUx85zF/OaPf+C9Z52NJeULklB3RPI41v9sNeR7mPPB83h/6Q+s/c6Dk7TWV+D3s6zf0yXQGKEgXmkkXn418MFV1y3nQAFkTls7qzzNXENsKr3nnP9zG1q+ipDWsN7xXL6iHqfSMVDJE+vbQibZhtAKZQYp1E3DKq1F7kfo0nVhAq9qRNsu3qpBP2djr2PrTAV3TRrrtGb/IcuA8H5outLI2XV+p3Pbwzy2Dt1bQm3N7ellSIFOV/BW9SPmN/ogtTl74NgpBGiF8HWifuBKFKnD25dD+UzDrZDPrEFIk1eq6SrzcJwMxfxWwrEZHI7okRSCoVKJ3152Kc9299AUixIyDIqO84IA8BEZsdyGTZz8lTex+bdro0PPbJrjlMpPWJEw2lVo9Ej/Q2kZaAHCNJEBE2W7AO9bdd3yjy24/kL7YMCj7YNvB6ByzIk/NIZ2XYVW+8/FF5JBq4ZnWo7mKDdDc+cGwobcY35pIYgO7aBU24Id8Te2r0QbnmPgBenE0cRrg8QbC4iAgXNn1776hZSotWm8SVFkSwg9ZKP7Svs7rA+CrhrpUSpn1aBSpX17lAqBWpvG3l7VWkqeH7nZ38EFCM9D2CWN1j2iXFpu5PNfD/72kbWH+/oIBJO6XNwhioXtfpOlV3AYdvdwB6lUhkAKgrQzEmN/ngJpKpulJR5DaU3RdV8w1nZEXJWTv/ImVn3hjpPsdO5Gr1T5u5svIYRACAjVR4jPqCM6uQbtOLi5MkgwwiP7TwhgCcDBuCzh7/+B3KffQ+JTXy/ib8yr9qdf2OFaOOYk7t+4g6/1eKzzglhj+PqG5xLv24xVKSCURyTTtX+2gUY5BkM7Ejg5iZwcQU6P7dsnQwCuxr2nC/euLpwVXei+yrjaBUKgNmRRXQVfk/A0xrSYn7g11rViSL/DekntX8OQEqFcjNwQspyn0HYyPe/8eOfaH9/10LM/vqd4OGnv40v/FYCB3nuPy+c2vUop+xXpnoztZ2oMI4RdHqKkNqKxD8v0E0LgVbOhX8gzecQ0Dq0UzmD2/UYo0GBaJsVins3mZnSdwIgGMKMBamc3EW6J4mSKyKDlVxNqbQDnrLpuefigP9QZERx/D/xxf2PpSQPPMPm3U0/mopoY048/nYoZ2r0z2yiLZPuo3/kUDTtXEe/bWsWkcTfNIprZRSVtkOuPIwyBeVIDRM0xczWoKL+ALetAxPRDuSG5bxSkuuWB+9SgD7ACMA2MefVjV8MOj64c3yURAszsIEYxR3H2Anre/mH63/AeUZ466+TY5i0/ie3a9osNHetmr9/w/DPNn/rTRzj5kp/y2NKrztTa+zmIo8H3x/2bqhbpvlJuu92y0b9RiACu7KdirkNRRvDyjCIdMeA48brXPeFV7JuNoIV2PYLCoqXcgr2lQGZ9H/ntabLbMsRmNBJMhNCOhxkLD/dFnAG8FQ4uwhK/8ffDPTJVlbWUx0OOYCmDSPegNJw0fRoqFCddOxkx5gqrCRTThLM9GJ6NlhZKWohxmEegOEQknWJgZx3lbACRCGC0JcYMt45U1IYNzNOasC6YgnXuJKhW0u7JIgQ6VULvLPrhXAFiLJB5zlE3kJUSVm8ndn0r3Zd8hL43/SflabMxSgWMfAbhVABxFkKcgvKe93Uy/y3f5Infv3++1OLPaGaj1ci+H/4uA2KUJCVeGTdRbbIvRj+qkdrEMzLY1lqUKL8sXbUjonE8+rE/cOrX3wZCfB64WGvwymViiZqRxr9aaZSjKOzKYEQtnHwBIxxEmgZa6xrgTauuW/7HBddfWDqYzx4l4D0DfAu4dqzXGW6F2NBOBiJ1lC2/f6TRMJVKvo9gMe037tl7hdaKSqSOwSknoKUg1r+dmv4t+2SWSs8lmu6kFG1kcGcDyWN7EEFjP0KtRraEkdNjiKBETIlizKvH+0f3vtqEq3Af6cWwPWR9EPfRvjHbBY4HGEK5WAMp3HCc3ovfS/6Es/yninl2X+V7/Hbn+TjjqVQ3yWQrALGa2d8S0GREmxDSRDlFVDkNygVpIpBoNK8k7yVQ37Jf5VTlFKr48sOOIwIcp37dD8EuuP7CVauuW74DwTTtKjzbQVrGaH0SrTTa8fw5JQRGNISTziNM4xzgbPxWgQdt8SVLndw1l3wfuBiYs8+YVcOtoeIgpdpJ/uIgDTwrxLjtsTTkGmbgBv2CsHzjDIKFQYKl9J7gIQSBYppYtpOsmkYhqrE2pMEyxr2AdMXzE8KCBigwZtWg1qXRQ5V9NAo9ZOPe3bU7CvJcF50QCOUh81mE65A5eTEDr7kUr6YBqz8FnldNv9/nQI8DD85pa38+QodMpbonIcR7lFOaa4RqUU7JxyjDAGHg5bqx+zrw8j3gltGvKLF0fzutCdRLsSr4xXRVRrkYHxnWPLTj7iHqKFdhmIa/c4Eh0Z6HDAWH8xMSwImrrlv+fL5jCvjFuD9eK+J9WzHLOaRbJpgfJFgY2Jdt7B7nPS4EzwqRb5iGGiOcKNDU9mxk8rb7MG7vQA9Ue5WO5VZIge4p423KjjwvggbmyY1j52oIdneEeo45JrRGlvKISpnS0cfT+d4v03vx+0FBoHu730F735qdfvwOa++a09beeSgso/q3DvhvYD3CuFTle53cs3+i0HErhfW3UVh7K6XNK9DKITT1VCKzX4uZmM5Lu/bycE90MQEco23B9RcOg8ct+LUkeKUKSilMw8IrugTjISKTYghhIodz7CWY4ZGCoPcDMw/1O8SXLHXwK2cfHxsIJIFSmsZtj9Ow8xnqd63GcCpj9m0ZHuR4/zakUxn5vxJvohxtGP8EezZCgrJMxJQYojE0Tiq6n7g1skk0fs2LaAodwj4cvltkFLLIQoby1Fn0vem9dL/9QzhNk7AGe5DlItqw9nZLCsDNwHuBK+e0ta8FRjeK3mPri9E2/Piwa5JKdc8Cfl091mdbmxuOLWy6Y5Uup9GVPLqSQ5UzuOmtFDfcTmHD7bjZXQSnnEJg0vwxN7XSo/6+IOu01qA8tOegPYfhnhqg0cqtCroHQOu1R0C7WNobdXOxtEtAeVgvwi3kuodcPPODp1cdebhbdd1yowoA39FKYdZEkFaALYFNLDrlQvo27aK4K4cc7tYtQHkKeyiH8FOd37Xg+gu3H+znjk5Yyl1zyYfwu1iFx7tABBotBJ4ZxrWCWOUcUu/bSUsoj0zLLDKtc/wkKSGIDe4kkVqHUN64a1Q6eSyheWESdf04/+hDdY1RjepqjPn1mKc1gyX9vWGX70D3ljmg+n/hy29GIYOwK1SmzSJz8vmUZs3HCwQwSgWE546309zdVXZ2y5y29uxowBhVTHigjKMOuAu/w/07ksnWxwE2/O0bd+JvGj72JAXMxDRCUxfiDG6h0vXkyPmzDEltKETRdogGLAq2Q962MY6UIKJcRDCOEWnCiDYggjGksFBaYSgbw8lRzvUiC72UvP2lkws2xqYz4FoYYzhh2jHRrvGC846CaXV0xeIrDK0PqIhGaayIJVc2xa3fvbv9hPIRT9lbcP2F3qrrlt8PVIQQQVnRCAlRJ0rq4a3YdmUP3UP7vu8vhBA3aM/rk4YxeIhsYzR4/AZ/C4YzxptwGoFrhhiachx2uJZQrp/6Xav3iZxoIYkO7qRQPwU3GK92kHuODFUhKCYmYachNrmCebyD01f2t0LYK2ridWTAEMjWMN62PLrvAEHDMBC2jTXUjV3fytAb/o38nBMRaIRtYxaqOSL7XuBbgc8Cfwf6RqfwDwPG2Rdexr3LR3blezXwL/gd5muBLLASuGPlspuq3dD1zWimI+WsZGvL0IExdv83uuntlIUkNHUhXqEfN70N0zBQWvPNex5g5foNNNfWcOMbLyRiWVRc9/BesNrv3RxsOY5Ay1x/y8fSEF5xANctEwyEyCuLjEowa84JFDK9BLffR7mUR45Rx6SBgVAzXcQolp199+4KvjiuhoA2Q+u2AyVe0YAkFjS8UkX96YiJo2PYauCrCHGdY5exYgGmGNNxPNtnGv6cS+Mnbv0eIVILPvc6Z7RecihVs8OMI75kaV/umku+BZy8v6GqxOopJSYhXZti/WRC+T6iQ7v2pPNCYHgODdufIpNs9zeRH+pE6PH7fEqlsMpZKkYTA9sSNM9yEDPy6E17pYILP2riPT2It0ZUd397DtCQEqE0xlAfSMnAuZeQXfgaVCCAUSqB8naDxZ7HyuI3fP4R0DUMEqPaMo64IMP75AQsvul6nKYUBnu2t3ojkD7prDd+9zvf/fYOZSUW4nH+5Jbw0MFf0RJnYBNGzSRC008nn96GAP7yzDqWPvYk5518Jo88+yjv+Pnv+Ot7r6DkOIevD4VWiGCMyLQzkZEEdm8HzsAmtFsG7edjZByHC3/4C85sm8u8WcdzxvyzmHvc26HzMSqp1YgxwENqRW1IYihByVH79Dh+KZvSELYkNSETT2l3uLPmEVehqpPeQ/MQMOTZHsVd/cqTHkLIMpodwMeAhrlXn/G1uVefsQOlnL31kudr8SVL/wDct99F27NH9AuhNdnmY/CMsZlcoJylafNDNG19lEApvV+RSwuo7d2MwCXfH6UwEMOclxg/ylJ1W5571dAY5TzmUA/Fme3s+MCNDLz2Sr9bWD4/FsPQ+LktdwAL57S1Xzenrb1r9AuGQWN0F/pFF132ZuAJ2+EspQjg9yEb3R/PAprC4fD1N/3sez/Lbr87NWnt8QPlRz9wdOWR9yQrj/5Hg49bSooxk6T2+l2GRWXHQ4AmNPkUDDR/e+oJ3n7+G/nBf/+Qb1/7Lbp6dvDkrhTxUPAwzT2NsCJEZ78OYQUprL8du+tJtFP0QQOIWBb3bNpCS6KOH1z3I66+4kN86MarefjJu4lOOYnglJPG/VlKa2rDJpYh0C+TQIrWEDQlDRETby9R/4gDx/CktwezT8qg9Uiwpa7TLRZ/4ZXch6Ul3zL36jOmz736jG/MvfoMtXfp/eGyUVWd/wWMmxcSzA8RSe/yoypa4wajFBpm7KfjlkSgUIZFOdaIHUmME0oUBAqDRAZ3obRFrieGqoshGgP7S4wfFy4EGqNSQhZzVBom0fWuT9P9rs/gRRMEe7YhXGesSEkeuBN445y29tfOaWvvOBANY9FFl72eUa0ZpfSjPMpTCC1QIzuICUzTYNXaHfzoJ/83w4mcvUboyiYQ61DO4/bDVzzQFNl8fCLURU2gl4g5SFBmMChiUEFKDyEUQlSP5znYQ12YiekoI0AsGCQcrgEgGo0xmM1SH4kcpv4TGmGGCc9chCplKKxfji5lYFS0zK8Q0DRGomRKRYQQxKM1mMEIHU/eSrDncazG2QSa28a9XjylaYkFCJjiJR+E1UAwIGmKmVTGWMReEFflN/IaFn73kr7V31j5TTMa3HD69y7bNvr5Nd9+gLlXn3HYAWMMveOx3DWX/Aj48JhTUrtEMinK8WaUGURoyNdPJTq4A8OtjOM2CHINM8k1zkSiiPVuoqZvyz5agkATH9xOpaaJ3GANoVSZSME78GicNEB5GKU8wrOpJGeSm38W+XlnoIIhjEw/wnXH6hfi4PfS+A3wuzlt7bmxXJK9IyRVptFQdR+roCEoFIoUy2VamxoxpCSbr1AslamJxzBMk4AJ/9gY4ZbHXN660KNSKdXK8ORaokfNiAUiftGyEQHPQ3klvFIfbnYbbjmLkvV4MoqnpJ9Wn/kHMn42AaPAxfNmUA5rUBVsx+H1c9s5urGZvlwOQwqe11QUBoGmNoRpUdx0XzUhbd811fY8Tp85nUVHz6TsuBQrFWY1NXLxggWkOtcRdCRW42zcfC+qNMDob6U1mFKQLjtkKzZRK4B6CVMPQwiG8mWCMkQsaGG7e7pYLwhwtH/mLNZNfRvt/37K38d6/kgBxjj2Lfz9alvHAoFgYYhwtod8w3S0EH65hx5/gnumRSVahzZMlNbkmo8hlB/03Ze9wMOs5EnsWkM51oTuyUDOOaDkLYRAlvPIYg6naQrZU84j334qKhpHlgqYxerO8/uCxirgf4GbR7sk+wONYfekaldRTZ7TWmPbLmW7wvve805efdopBAMWOztT3HbHCu5e+SB1dbWYpkk8pPnLIw6L5rfSOOt8dO1JiGAj2kmDWwZVARnEDNZjCkEgtwHVdz9e3wOgemG405rXCamnUcEAbz25BtdK42z+X2Zpl+9d+Wo8bwe1QY2jgjgqcsgugAzVYtbNwO5+Bm0XxtWV/EcVn7voXOojYaRl8uXXL8bARWlQg+sJNSSxJs1maMtjeErjaQOFwDIE+bLDtoE8kYDpO3cvYRNC4LiKrf15ZjbG9gGPFwQ4Fnz+9S/6iRi16/pO4OvAjWOzDo+a3s0Ydhk7Ukt8YFs1hDmO8Ol5WOUcdtRvM6KlSa5hJvWdT++DCUJrwrleQoUBP/HquVZJKRGeiznYg4rEGLjgSgonnIEKhMAuY+Sz1S61+6yOaeDz/P/23jxOrqrM/3+fc5faq7q7ujvpdHYSupst7EJYhn0TENxAZBlGFJfBFXEZHGTEGZURdXT8iagoqCB+B1EEVAQRRJR97+5AyNpLeq+96i7n/P641Z1K0p10OgkGzeF100XVrapb9577Oc/zeZ7n88CdwLq29g5VCxjTCa8e9+Z3JwiaToeCoI2B43h85PJLuPTCt0/st/eSRRx4wD5IafDgQ4+SbqgPmgNnJE/kT+T05uNQq25HDf8FrRyElQx4A1UOVL/jSzDnnYux1+XIWSfhvXojKr8SYafBCCG0h5QKw9DYegg9MkhSSnRc4PtjSNMib8xldHhmfZ0FYMSakcqBYj9SaBAbHU4hxlvtajxfUPIlJQwi+QoVz2XAm02honC1TcWXqGeG8MwYfnEpWkt8JJV0mGLFZc1wfmNdzu7PcARtIxWsHSkyvyFKfcRmrOi8fsCxO43EV273c1edfztwLrB8sqlkeGWSQys39fjG+7JskdfhERtdTyXRhGcHqeiVRJpysolIdsOkORNT5XvUWhkCgZEfA7dC9tATGT32HPxEA7JSROazG2tKNgU0B7i1Cowrqn1ntgswasYcYG7tCtQyu5F3v/PsLXZMN9Tx9rNP5cWXVuA4DkIKPF/xl0d+w+mNt+OViwg7BUKi3arwkBHUB5Hrwn3hamTqAMylH8Q84D/wXr4enV8BMoKuanVOFL8JESTXah8ZCiPrmtGjRlWMSUxKIItq/cu446AR+FriaYmnJE3188hnRhkc1XiyCUeb+NrC1SajBZ+iExyvZUeQhkHFVZTveYGK4xMyW4hENrodfkWjfAfLSjFeuuB6sCabr06fN1imqAiI3TUjBcoJVTlsQYP6hwOOmgK4HoJkp0MBe6u4KwSVWCPaMLGLYxNSghtPrMQujhHObiCfXghC4FshSqnZRHIDM7pQ0nWQ+VGcpnkMnXUZxaXLMDODGIVMAERbWhgO8BLwkbb2jkfGwWImgKGUh5QmUsqw76vw+DyXCCzbxrYnP13xeAzbMqk4Qam40FDMDqJIIO3a1gCCGiQAaYMRQWVfxnnivVgH/CfWfv+G8/gHQLvjfQw3A3EgFEPUzUZ7FXxPoPXkytBDbj0lz6aibRxt4SgbR5l42sDDxPch+4chpBSEQ8smrA3X9XE8jwVzGnhTeyuL5zVg1CRhZAtlhkbyPPnCWtb3jxGLhAGFIQWG3CjSUzYVr+bzmNVm0G+syhQ9YXUB9GQKb+t5vvB94Pl/OIujSpLq3FXn302QFHbS1OdNUY43M7joTSANoqPrqe99cVLwSAytopxowgvFJ9yX8czS6RnMGumUkU4FJz2LsZPfRWH/o0Fo7A3rg/6lYlLAeAb4FnB7W3uHN0ProsY7CqaEV3Hdsut4kUgYhKBcKeNW3EAwZpLfNDI6Rq5QQEqjCriQilsIobbtQmgfrBSoMs5LX8Ta97PYB9+A88TlYMY21UiRJjhFZN1eaM9BK4HvT+FGosnK+ax1ElRcL8ijE2LCEgnEkgXxqAqiVULh+4qKp2hb1MSRBy0mGQ+RK1RYvX6YobECruMTCpmkEhEa6mK884yDKZQcHn1qFd2vbcCQQXQJrSlYmjUpH7NaXq9rFUc0u3VYdvz4dA3IG0K0An/62P1/POYfDjhqwKOvGmE5BKifnO9QlJKzg0ZHvkMpNYtIpp9YpmdTs1gITLdEet3zFOpagrL6xXJxxwAAIABJREFUkXXbrvIUElDIchHhVnAbmsjvdxq5w07Gi6cwsyMIp1IlPcXmS8FzBIJF329r7xiYDum5tVH73lUrVy4tVZy3XnnN9bOGhseIRCKYlsnanh7++tRzHHHogZuGbVyX39z/MCNjGZrSDVXRGljQKBAGCA8mq9TTm4E0MgTeGN6rN2IfeD3m4ktxu7+GiMwNwKUqcOwXh1FrnsBccDiqUpkSOABsSqQTjeRLUCg7VeWAzTOwgi5ohVKF+mSUf1q2kH2WzKL7tQ386ckh1vaMkCmUUT4gNFIIpJREwhZ7zW9kbksdpxzdxpL5aR5+YiXZfJlIyKQ0x6JZWlus4ZaQ2Kbcba0PTTVJJxZC6U1lDrQmYUr5s2k7XJm+YVIt6S0ev4GBg9xV54eAnwNnTuoga00pOYuR+QehRdDRLVQco3H1X5G+PwnQaDa1mMWUHIYWEsMtI3Nj+PEU2UNOIL/sWLxUI6JSrCaiicl84nXA96sWxg43SaoFjO6uznrgXVrrS+vrkod8/bs/Eff89hEa0wGuZjI5GtMNfOQDF3PEYQdSl0oyPJrh/355H9/+/k9JxuPBagtYJtxweZSFTQLXG1eykRPnxcQjPFlRuZDocj/moksw5r4V55mPQ2UEqjegMGz8bA+qnMVoWAiN+7BhvaZcMrc4VRJFj78XG9QCDCmouD6FsoPn+5uAhxCCiuORSoQ484T9iEVC/PoPL7GudwQhJbZpBCHf6nvGV2GlNY6rUErROjvF6cd24Pmau+5/nkK5gr1/atJMKb0Lm0HvXHpj8uprrSmL7QGNTN9wONWSLv89gEcNiCwnqNOYtDeBlgYjc/ajmJ6H8D20EKT6V5AaeHWabsjm9rMBSmGNbgjCH0eeRvZNp+In6hGui3DdYPXd8rMrwA3AD4E1be0dlfEbH7a/EG1z66S7q/Ni4AqgQykdq0vFWLt+Ax/+9JeJRCNIKVC+wnUcrJCFQFBXX0exUGAslydkWhiBCBPa18w9ZDbHvG0Ryq2Cg9gIpC5wmMxwZKULF7mRdBayen5ctKoQOvwm/LEX8V6+DhGZD9pFGBbeyJrAAlEebqqD4coS3LLa4pQJFBvUfHr9JWg0Esm6wRGi4U2rDnwVJJGdc8oy4lGbH/78r0hTBtKKhrHVdgPjbIbj+sTCNmeduB9CwE/ufhL7gBTC2M5G5lO4gruOwtAzIWwzcrqgUR1XZ/qG12f6hlNVIHlDWx1VwvTPbEWfVCif2MgajEqxGu2AYl3LzEBDSIxiBmt0A4Uly1j3gS8xfOrF+OFYILLjVia7kAr4BXAMcE1be8eKtvaOykzJz/FRY2Us6+7qvBP4TpUsjkkpyOXLHLysnbeceRwrVq0JAAGNaVsBDyIEA0PDlCoVYpHwBGgoX2HFLPY6aS+G/DAjIsKoiDBKhFHCDGFj2Gn8cBqRXoCob0UkmhGROrCjVfAIQXkYNfoCRnIpCBv8SgCoygftAQYYFkZuDdIZmzx6BZh4aK2JhEKs2jCE5yuk3NTaUEpz0D7zaGlKcsc9zxIKm1iGxJhGj5LxV23LoFxx+O0jncSiNictb9tuANBaE0nFMUPWRCeAXcdhaIywjZ2IzaQPizC3BhhArXvyYeBKgrj+85m+4eNSLelVf0vrQ7/nrYjv37mjERYIch7OBCb9EaFSlujoevKNi9DSJJwb2g7is2r0KQ8zO4hbl2bDWz9EYb8jMfJjmJmhYJWVW9SteMAa4Kq29o47p7rxp2tdbAYWRtXCuhr45FTRlTVre/n4By9lJJvlrrv/wOL5rTiuh6qGky3DqO67ceJFUmEOf//B2HFrC/bP1YpZoXgQUhVVASFpB53pMBDjN7QGkYyivV4wD0U2tKErIwhs8F2U8hCqAkohhaZOPsuwPAx/EtUEgwph2+KVdf3k8hVS8fAmh6W0oi4Z4YiDFvLrP7xEvlQhbM+M+jMtgw1DOR5/bh1HHbqQP780gD9Nl0QrRTgZY+VDT1G/qJWGBbMpZfK7xvrQYEdCjK3bQH5wlIVHLqM4mkHI6VegbPUM1YDG+cDn2VhZOh94INM3fHGqJf2nXQ0a+qRDEL9/assX3B0vqa7yHa/lrjr/q8B/TkWSJodew6rkQRgb61mmAxhuBVkp48cTDJ18AblDT0CFIphDfUHJ/paAoYEugnDx99raO4Z3hPicxCWZC7wN+Hj1Ok7q22ohGCiN8exrq1nwtkUcFc0x8PwQIuOjxldDsdFAMkIm6cX1LD5+IaFYaAsXXqMJC0nItCj745XE1Q9QOjCslN74nJECdyjQIk3MhZAFMoTQGjs9H3wH7VbArxDz8hQyFQqVyBYnMmwoegYzZAplIiGrmm+x8eAcx2dJexNj2RKDw3nsauFhbTRhk88UYkLQWlejJZKNOT6JWIjV64bYv70lAMJpCElL00AaNrec9xlm16cYGBjhgHefxn7nHo9bLO/0+8mwLbp/9xdevOUeDGnw2Lf/H++8+RqcQmnHgaMGNE4mqFfYPPKwCPhxpm/4Y6mW9C8Azhg9j3vrf7bzSZrfP4U+6ZAwWp8M8hVa5zgsWLJWfOGGCeToGc6K1nRyu22uGsvje8B5wLJJL67vER9ZF9wCW+s8VtUAlU4FUS6ioklyBx1DZvmZOE2tGLkxzLGqlbGlef0Kgcbqt9raO1bU3vg7ChrdXZ2zgZOBDwJHTEmGocmpIj3uEKvcDYyV80gh6Thtb+bsN5v+lwYoDBRwyx5a6SD/oS5Eeq80zR2NGJaxEVhqJ5o06M0N4ypFc6RuiuxJsfGv9tDKqZrVErwSGIGYcRAIkYhQDCGTSHMWomIHLNBmn/bqaJj+kVEsw8LxPDTGRKWnAMoVj2UdrXSvGiBXqBAOmRN0jJhQK68qNSKCbEohJzJKg8dBqwlDCoSU5LIlNgzlph33jKTiPH3rvQjX5bbf3czPf/JLvn7jT9n3rGOrSuk7kUgVUMkVeO7uP/LeD13I2y85l8XWfmx48VUa956P7/ozBw7doxCtkkzf8IEEWYiLp3j/Ao36Xr4vMzfekvrmvfU/45sj3+GKhvfvCgujCbgdQ/yARKKVZx57CPif9WP5hcJXB7amk3f1DGdpTSdnyncMAV8mKAZjKqJ060uHRHguRiGDNi1yBx1H9tATcdMtCK+CNdy/kSDddIwSZHzeAjzd1t6hdzQfo7urU1Q/xyTQy3gvcDxTJLwZSErKYY27gTXuAKMqj0JjVBOwvIpHYnaMePMivIqH7/gBhyvBiloYtoH29aSgMb6ER0yboWIWRyn2r2MrzcwkKAcjlAxeV5WgJ8k4sIxrvyoPrVyUD5V8CSHqJxLNpNC8kmmgc7SOsG0ihSAsTSzLJGKZQZWvgHQqTiRsMTxSIBkNEQlbCC0mAlpiE0ybHO70ZkBgGILV64bR6Wnc8ELgFMvMPWI/1tz3FwBi4QiR+iSGbeI57s5dhTVY0TCzOxbjVqrp42GLhsWtKG/6vMqWwOGFEKZEr9OtGYZuEsj9p3qzwieiIw2eEF9uzC09eCjxyqVXNLyfkb4BGlqad8zCOPnQKJqw+P2T4wpgSYSMkEodS9/6hYQj/b2jYzfh+sdpKbK9w9k/zEknMzP9zmpS2O8I6jveut2REq0wM8MI1yF3wFFklr8Zt6kV/KCidRNBnU3HbcB/A11t7R3FmXAYk3EZVdA4Cfg0cBCb9eytBQwfxUqnj5VuH1m/iIuHQCA3u01UtQ2DETIwQ+YmRNt0Jp0GTCnJOkW6cppD6yUGWyoLaC2w4y4FUU9MeFh1FZy+qaX5hDSJz15K38tPEW2Yj/IdHB3Cjy5icbiu2nRBbxJeDIq4fFqbU5QrLrl8mZBlYtT6+eMuSc0/09IYNU1y+TK6YXrNltximWRLIyf8+3sAaDm4jaNS5+GWnF0TZhWw7PxTWNrcgu95XPDTL2LYNl5l+t+35dUwK6AJd9krfhTTiUP1FIIRGo2tQ4R0nMvDH4/MV3MvWtfXcxtAQ0vzjkdclH8KqKf0CQdfqd9yfBhDKFxHYBuNxXPOf6j31l8fpaX1zwixBGjz4QKA3sGxGROlia/cPgz8BJg+AEmJLBexRvpxGufQd8lnGXzbh3DTs5GFLLJSmkqy73HgdOCf29o7nh4HjVph4Jm4JVXpv0XdXZ03V6MxJ0wFGlpr+r1RHi68wBPlFQz5WRy8YJFHT71pja/VxLa1fXX1r68VnlJ4WgGKJwuau8YsnM25EAX2bJsbblvPj+5TjGXKXP//vYKdjk5hsmuU0jTufQLz3nQ+xZGemiZIAWeiRTXpa7McOs/1iUYtlAoqfzW6puuanrEospA6KODbjjvZr7jUzWkKCOa6OOFUHO37uwQ4lK+wwiFCyTjSNKlrbd5uy8YEeO6xp1h25CGM9g7K+jlN6lOZ6265NPHxE2/MfZl9vTYKMsPmmSAGBlESfDzyWZ40nuVP+XuNJJHzR/sGbQPjkmRLQ35GltQlZyN+9CuwQopiMYdlf5mhDWeipeG+/R2lwX/5cMGP1Z1ojmRi2pQoNEIYsZAhz1jTP3zLnKa6wg6QpBDkdDxUNe+3erGF8jFHh/BDEQbPuozM4achvTIyNxqcrS1dEgX0A9dViU+3Nqy6g9ES2d3VmQDeR6Ahmtja2l/RHqu9Ptb7g/iGpk5GdynBLQQkrdAmHWteVSZZvZq0cCZuUCEC9Ljt7gxfuKaJTK7Ibb/p45MXLUAYarzT3xbBQa01dQsOJtm6D6/87n9QThnTVFtvOyPAU4ElIoImPzvlt4ZDEQYG+qA1PT292CpA+eMWzuuUICarLp9CB1HC7QGO8VBqtn/EFlKev+b5V45vHJn1jp+mF/GJ+LV8svgBTnCXUxYlVNX6EAhsHeIG+zvcaz7A7YXvUU+CvMghkW/VaDvTN3xFqiW9uiZ5bHoh20cfQb/n3CMR4ps4Tp/2vXXlI449xHrwgbj5yztJrR1Yqua0Urz0MrSM4LqaYqVCxamkOqxsCiiM/uVh6o84drutjip45HNXnf8t4HCgZZLlBOG7yHIBFYowdsRpZI48Ay/ViJkbRjhOEGbcMuqymiBF/Ntt7R1rdkakpIb4TFeJz08Q5GJMHilBU1IVBlWGXjVIXpcwpES+Tn1MLDZt+qSlMfk3Fz2+fOWBfOXbtzKrqY6L3zIP4hI9uKUuihACp1ymsP5lEvMSGHaEttM/yVD3Q/SuCZLRxBTEhGEaOK5GSrBtE9fbOcARicb5xU++xFn7fBHDnp4asQDKXmBhRIygq90uBQ0CsR5fBcpkQojtyucwqze10Fr/i4SvRZOx8LqB17jAP4uGxhTfjN7MaCnDWc5JCOHj4xPVUX5q3sX37Fv4Rum/OEC3kxFjGBsb6J4JJDJ9w59OtaT/kukbFqmWbTNFff0jiNkN6MRfelm4dLWz/0Hx/FkXbMgcenhs3i23xZ33XgIFH/P5F1D9A2Sa51F2HFL9q5n/9MNHG8MDbwZu2l7QmMRl+X3uqvPvBy7euAwKhOcgywW0HaawZBljx55DZe5SjEIWc3QgsDCMLayMHuAPwDfb2jsen8RS2O5RAxipKsBdAZzFVuIUZe0worKs9wcZ04VqP2qJFQoyPt2Ki+t4O5Q3oLXGMCV2JOBfnZKL721M7/Y3oxJ9NYk5IMDJeJxwdIp4QuNWPI46cj7uhsqUGY6ep1j39B3UDfVTv/Aw4rMW09h+PKnKeoZWb2BLafHgKGw7xEO/vZ1lbR+g85n7Sbcuo6EhXSOHOLPh+z4LFu/D9jZbKlRbncRtm4hp4mu984SYa49Pa0xDkrBDFFwHU26/DqqZ6RsWBG0S/9P3/XAkGmXO/Pn09/VwdHkZ6dYGbgjfyKAc5n2lC4kIwYPmn/nv0P/ymcpHOcE/irzI1oLG+Pgn4KeZvuErUy3pO6cDHm48iMP33vvMAumUXNXQ1KDisda6X95heak0paNPJPyL23j1Oz/E8wzS/auYf8f3iaxfRfGIY8XQcRdfuO4zX7pznhAzJlhqXJbrgdMQolmgMHIZUD75fY8gd/gplFsWgq+wRjZUC7QmTeD6eTXM++e29o7yjgLGZuHVEwmaHZ1C0KZgklVF4mqPQTVGnxoiowp4KMa7eFghk1eeXMOjdz3FWz50IrGG6A5byHbI4o6v3IcpJW+/8jRKxcrk7sUEIE/ytCFwh1wOb0sCAneDM/W+QuB5GiuWZKz3CSqZIUKpZmZ3LMcy5cakss1gw7Ij5LMj/PF3P+ezn7mKda89jy8SNDbNQqkdIyXLpTynnn0ZA+b0OQoBVHyPoWKJqG0yOxpjoFTcNcChFHErRDocZlUmS8S00Nt54U0CsZY7a9FSCkHTnNmMDA2wdE0L/zHvk3w+8t8MGqOcWjyGfw1fxaXOhZzvnkNFlLaGrIuA72X6hjOplvQD2zqY+fEIPcPZzyHEFX4k0SQcH+VqrGiC0r99jl6rjtSLr9H8kx+S2PAakVUrKB1xLAPnXYLfNBeUPlaO5M7qGcr+CIGeSWi2xvp4MXfled+UbvELRiFLaeE+jB39FkqLOhBKIUuFQJBHyMmIzwcJ6koebWvvGNsRt2QS4nN/4DPAiUDz1BNRMOxn1GrVL3Nqy0iJEILh3lFe++06PnjOhfzqjt9z+qePJT9SmpHWjFaa+tYUd371d1xw7Jn09w9z63W/4pJrzyE/Utz+TocC3Ly/aROGKdgB37dRysGwbBynF28wR0+uj4K/GClaJ/1wp1KioamVVHo+7zznRCLJOSxasi++t+PhT+V7CMMIzP/tceWkwZ97+zl7ySLmJhL0FvLYUu6CppOC2dEYYdOkc2RkRqki0kf1a/hMbYRaaQ1asaBpbwzbpHFtlBtzX+FVuYqbo3fwDvcsPlX5MEWRR7FNVP0j8Mh2HNP/Q+sm4fsgBLFigb5lR9G9eBlmQyMNRx7CrO9/GRmLMfgf32Dkog/ix5uh4kHgI34JQd1MQaNGYpDQ2MhXUf7LQ2/+F/ouuZryog6MYhApEZNHSlYCl1Xdhnt2FDRqidPurs767q7OLxB0XDt/a6ABvJKWyTOe91aWR1UOD3/SDAQlNMl4jJOOX040Fq2mHOsdmJBBif1hhxzAoQftz9hYBsMwZv6J0wIbjVJhpGxAa7dqio/huTkMXZ6SKRBCkMuOcMm/Xs8pb7uKs991FaHwjOo2JrWk9Ha6OxqoC4d4YkOQ6zMrGsUyJLuiYiVimRzY3EzFU6zJZrdofTAt4GhoaXLrWtJf0tp8n60jeYnUGo2hLK4xruO5Od3EwjHK67JkGMAXin4rQ5/sI6wj2/r8h4DzUy1pZzrh2Z7hLATZk9dbholXKvPaYJay77HvyEraPnoe9qoVuM17kVt+Iq6VRGYKgQ+7cZLNAt5V83nbHaUY5zpWfu57F6/51PdbMkecjixmkcXcJIpUaGAEuBY4oK294/u1+RgzcU1qQrICsLq7Oi8k0N+4miARbqo8pIzQ7lfry4+cld57zn0+U2sUaq1JNMQ44xPHYcUsTnjfm8gO5mbMcQgpGOnJcMwlhxKpDzNvv9mcc/VJ5IYLu1QtT2vwPDCMKLbdgtZu1X0ZRbgDE8zK5FEFQTE/Rn3THLTyJ2pw/lZDKU1DJMSzG4ZYUldPayyOu5NDskprIobBrGiMB9auD6qGZ3CBJMAhxdNix9e9rfiz0N23SG2+amKosnB4wHyUJ/0nSTY3cs2cr1PsyXFN7qM0izQfi3+eFcYaQlODx+PAhamWdGW6EZXWdJLWdNKLWqFvjWQLjw4XSmpBfzf733I9ka9dx9i7L6fvhh+QP/4EUt/4L4TNVLUAX+0ZzqZa08lpg0dt/kR3V2eyu6vzh7KY/47Mj9WboxsQSk1mYQwQ1JQc39be8fm29o7ijpS51763u6szCZwK/Iogq3TeFLesNlSBqPNKX3PhzgcXjf7Xgjr17C/0iouWbtukDtK3Y8kwvusjjR1j8qUh8RyfWF2UUMzGd9Uul7lSvkQpgdY+YGLbcxHCBmEgKSC2aRELlOftHEtjJ9zUMcvgt6uDVsn/NHc+EdPYKa6KqH6+pzRv37udvOOyKjOGbc7smssL8u8XHf5ehy1Vi35wR/iXb/9G5Acvu+juCGF/gT+fPBW+YvwvdjrC7dYPia+TXDn8Pg73D+aa2PU8abxIREcnwn3V8RLwwVRLumd7K2e1fon6ZGhtozN80QH3/uil9E9vJLvPwQx89+fkTjwbY6RA5dIPQl8vkSceRaUSk4FHmKDJ9HaloFdv2DhBbc7FCBkkEUljc3IuQ1BTcmlbe8elbe0dz9e6JFN1dJ8Wh7Gi2+7u6jwG+Cbwa+C0yaaBljaGFSPCBt00+gM9u/jzOTF35bnKiL+deFMH0flzt2nsi4Ao01UA2eEZWn2/pU0c16mmbu9abQlfCZQa/44gucyympBGA7bU7Bpjf9eCR0V53PnKShrCYY5oaaXieTsUYZFC4CiFq3zeunQpIcPggbXrGamUsOQMgcPQppDI5qiOPtKk06NP2S8cf03sa+Ue2b9mvpqjH7AfJS+LXDFyKQ2xZkpzBfnhUS4cOJu3+mdybewG7rEfJKHqxyMrrwIfTbWknwK2u9xeiH0BaFzSvio3b+Gvhz73NT930rnoio8xFkQ23HiMwsc+S+K6qzHkpCvK16npPjZd0KiOc6vRiqmu0n0EYdoL29o77t2ci4BN+pJMOk56yyWTfnd3V+fB+P63gNur3zGJXxRc6Ej5RRrdR5mdjsrY7IOFn1+HNmy0V0Zl+8A3Z7ONTn0BLAaZnwixwyXcQggMIQmbITJOnpBp7/KV3PcFyhebydt5SCOMbUR4g2mKA9AQsXmyb4DnBobYv7GJMxbtRcF1Kc6gT64UgpzjIIXgjEV7MTee4KWhER7r6SNhWzO2CMW/lb7Kq25X86ge/YCAfUIicrgSekEdqXLcDdt3W78zZuumAAnJ8mb1Zr5S/HdWDb5K0q7jry0vcb31bc5zz+Sy0gUjFUr/Gp9TdxvMXKdjPLtwtdZzzEzxYeF5e206M0ClEzSdegTumecycsWnMAczYMg7q6DxeGs6WRl3U6ZrdXR3dc4D7gEmq895gaDg7zdt7R0bNic9N+u1ahD0JHlTlZPQwFrg2Yfu+cnD4xzD+I3a3dU5B/gw8E5g4eSgJdEIbK+XVOUJIu5KDJ0HYWEu/hdwR3FXfg+MOvzR1UT9wkPfWfixI0CEpz7PmrAR5oxFx/Hs4Eu8MroGUxozv4mVT9yOcVHHufz8lfsYKY0EGZlTjJI2uTDyKvU1maPbyUFSLJgMDkRrrI5NR5d3GGUd+5sAwMg8gRYz+12jRZe84/OeA/ZhUSpJX6HAA2tXsyabpSESQW4jYUtW7a+Rcpm9UnUcN28es6IxXhgc4scvr6A+bNMQtWbaTS5rfjHyCY4be4sjYG+NfqerPXz8rixj/Uh15Hnu2d65zlmyV/RbB+p9yJHDkw7plmY2rF/Psb0HsWDOF3mf9Un3GfniF76b+uptAF5vBbMlNLMJ+Ovb4Mx3sVCI3p7h7HcQ4j/ZpPeVhoJD/tu3kDrnJEIXvndtuanhkuRvn3i4dFi7Guc2tgMwqK7uZ04CGpogget8YHCyru6bgcZy4EsEGZxWDQgowDv+zAuf8Hz1CSHEk48+/KjV0Fh3EUFP20VTWTkaiamLJMuPEXdexqAcqJ5jgQzjPH0l9hE/Rja8CZ15Dlk/Dyx5rIGW/lbWXIFguDzKYGmE5S0H80T/CzRFGrY7ph+sbJKByjBvajkQKQQrx9bRGE5Opiq6U4lRpbeevCTeYK7K+IyL2yaughuefIYrDt6fvesbOL+tg5dHhvnN6lUYQhA2zWppv5goNNY6qC4reEHtzRmLFrOsqRkBPD80wA9e6GZ2LEIyvGPciQA4YeycMEG68lyBuFMgXhDaGVbS+kqjbLriI6XLXj7SO2hRSRTjaIkjykhhIE2T3rVrSIi4480LXT+vcc7VO/sc9gxnQwRKWLM2o6B9vzE12vye8z4U/sEddwCsVZr5cmbGaXdXZ6TKW2yedpoB9mtr71g/WVh1vM9q9fHnCepE5OYTfKI/iZT09g0Mv+Psk//3c1de/uaVq1cfIrei7yG0QyLkkej9Fpb0qnkjgWKYMEwwQ4BCh9PY8y7GXX8jwkxiCo+bsovwt5G6XPLKhK0ol3Scw6O9T9A1sgpLbr8Clqs86kJJ3rH36fxu7WN0DnVTF0puFYR21OIAyOdshgYjU0xuzSveweR16g1lcUCQDp6r+BQdRd4rc9TcWZy8YDFhw8DXmrW5LF3Dw/QVC5Q8D18pDCmJmRYtsTj7NTYyKxbDFIJMpcL9a1fzZN8wMcsmYgpSEXNGYdgJi6P6oPJg3V1frH3lxLG3hrRWS0b0qPFfsW8lP1J476PH+ocsK4vybIEM0nI9l9b5Cxjs71+3sHHR1Zv4GTsHNKi6HFcDN9Ws3K8ixF3zhfjU+L5jD/2OOrlDXxwGJguFfHWaoPEx4JqJFVjKgMV2XYQwMKvstUaTSMTS9/7+0X8/YN+9OeOUo1m1ugfT3KjSrREYfhFbraPOGiMWSuLHowgjDIYNphX0FxEiwCgB0iuDaSJDs1ClwaCCSYttXoukHWNNbgPPDnZz8vxjWDm6Fsf3CBnTzybUVcXvY1oPpeCWeaTnKfapX0DFd3b5zan1psC8+dK97ajK7kuSJkIGrlJECfF0/zArxzIc3TqXeYkkLdEYi5KprSwIHoPFIuvzOR7pWUe27BM1LUIG1EdNXH/HLEFxwtg5PFh3F1XLY+LxiWPn1mnUyyERztcZDdFRMeZcXrrwwTOcE06siNLC2kllhewbow2J9++KEzjucvQMZx8lEBS6Gfjf1nSyZ2d+T3dXZ3s1GlS7RCtgUVt7x9qtvfe4N7/7xCqh2RiAhiCXL1JxXKLhENIQVCoulmUIETkAAAAMFElEQVQSjUSQQpIt5FjfO8hvbvsmzbOaGBkZwzAkAqVtRkRCvUxcrMGML0LEFuKP/KUqCVHtOVtbQanAC8ELg6dh+510NL1CCIOb8u34YpuBFTSwPj/MP+/7FhYn53LzS/9H1skRNkJIIScFEFHtRVL2KiDgmNbD2KdhCd9+/g5CRkCSbosY3RkWR2YsxMhweFLgkChW+geQUY1vOIujltwcKbpUPIVlCCwTbEMyOxZldjROYySCKSRSCBQaTykGSkU2FAv054s4vo/nC1wfwpagIWLi77j3mDXHgQKg9rFGRzSipaCLf27UjRsadOrcGyM/PudAd7+PNJO61sPfq7rjj8qF0ucARvsGqN8BAZ9tjA8BidZ08pHNQWUnjdAkUYgCsH4boBEmKGVvHF8Ci8UypmFw+eXvYu8lC/E9j+dfWsEv77mfgcEhZjU3UZdMMjiY4es3/ZSbvnYNmVK+hG39Mm6uer4u99TnLato40ukocC0kAb4rju5OaeBiOTpx+9nTqNm/9n2eBbttIhoQ0hiVoifdN3Hu/Y+lUv3fRsPrnuMZwdeRgNxK1Zl8yeK3yn7DtlKjr3q5nN066E0R9Lc1n0fWSfH7EjqdcmL0DqIqmgtEEJPwQ69ATmOzay5+qhJwVFkSh5KBzqn63J5+vJ5LMNA1hDQvg7Crr4fSC46nsBXmmTYJGrLnQEawNbFij0Bt0rEiy6eE8JeYmHdclfoN/dcXjmvk6C/RwbBvzXMax4c6x2irmXnI3sN0fns5oCxE0EDgg7vk5HTiwhSyTcZJ5x1EQ/efStV92bZ+EQOh0IUK2W+dt2nWf6mgyf2P2b54Ry8bF8++qkvkMlkSKZSLJzbwktdr5HfkEc9NSrLr2aXe1Z+WfqMoqlNjdY+L7zay6+fSnPyoQ4HtRoob5Irb4CV01xwiouhNdq12J6kDF8rmsJJNpSz/Lj7Hk5fsJwT5h3JPukldA6vZHV2PTm3gK8VQgvCps28xGz2nr+cpXULyDo5bn75F/QUhpgTrX/9Wg1pgecZVWtDTGpNBeToTvSfX3+eNCBLLQPbEGQrHnknaCtpmwJPaQzhV8vigxJ5TwtcT+NrH9uQ1MdMTEPs1Fy8rQHHEHCZgaFMjKSPf4+lrVWrjB4v2lL3dKZv+BxAp1rSPQB1c14fc3AnWxm1o1IlQmsdxxhwNLByc46jChoQFAnODlwUyBeLHLT/fpuABoBhSI44bBnnnnUKP/n5r0jEE0hTMtw7xhO3PsYsLxLC8+d7noXvGZimh2GGaE4rZht/YF66MWgnspWVKSKMCR5le+8TTyuawwl6ihnuX/sXnht8mdMXHseJ85ejtNokbDeer1H2He5f8whPD3aihEVTJDGtXiQ7jwcAzxVb/akmztTSpm8kzgONKQXpqIXjabIVj6KrqlGVTc8JgG0I6kMWIUviK73TE3inBI4H6+7Sl+U+4TzNYzyQ+MUIQT1G4FcG+Rmv1TzepSetFih2EWgAZIFOtlQA/2B3V+ev2to7RqtcyCYAIoUIa61jmkDxOjuW4+Blk6ebm6bJwgVzMU0TBCilWLighRc6u5mz9CA8NMox0J4BeChf0xiSXHpeM35eT2h+bn152jFCriWSZKBSYKic52cr7iFmRpgTn8WsaCMh08bXirFylnW5XjJOlqLng7BJmiEi0p5pXsAMzXhFqTSIP4VYj4+HII2eKlv/Dem6gG1KmkwLDXiexqv26hVCYEiwpAjyPNA7EjmZscXB9xJfnfT5WqD4e2gDOb7oEhTlbQ4chwPXd3d1frKtvWN088jKyGi2nEjGfMOQZtC4uDhl0pNSmoGhkYk7PBCMETTPbkKGbGzbplwGz7WwRBl00GXMH9UTXRJ3uVkMNIVijDpFKsrHUD5r8728MrYaT3kIJCHTxpAmJT+QAE6aIWJWCF+/vnyCUuB5kqmzpjUm5Tesm7I163L8WpmGwBJiE4ujVoZwVw3JnjFuRTgEkZH+SXZ5D3Brd1fnW7q7Oud2d3U2dHd1zs2ODu73ofe881hDSlMpjVKK+vo6Hn/quU26m42PweFhHv7TX7Eta8Lkt6TJvKY5qJjNiMhgp2JUsg3IGrJPyNd/7qdDMVJWBEd5ZB0HTwuEDKGFSdHzyLsVwoZFUyj+NwENAN/Tk57njbAhsCj9Xc9dXQWL8e31GnuAg03qVLoIOsFPNt5MQAjfRlARe+vQ6NjPlh++7GP1dUnDdT2U0sRjUZ57qZvPf+l/2DAwNPHm0dEMN958O6+uWkMymZhYNWbH6pkba6Lkl+gP9SOET3GgCYy/cYm31oRNiwY7Sr0dJWGFiZo2cStEyo7QYEdJWCGE4G8CGgCut60GzRKL4p4J/nq7Kv+AAFLp7uq8iaAPyRmT7FJHQJYGvo3rU1+f4rBD9uPOu39PNBzC8xXxSIi77/09jzz2JPPnthCPRli7vpe16/pobkxPEFWO53DCwgNxfIeoFWP5ouMZe3WI/HADUqi/eSBxnK/YWEEpJtY5XfP638rXdx29VUMsSMofV6jTeyb4Hotj17grVfBYQ9Cj5cFt+9gK1/E4/+xTaGqop1gqYRgCjaC+oQ4E9PT288wLXeTyBRobG6rV65qK77Jv0yKWt7bjOi5G2EBVRVuUa+OWIrvNudET2/h/uwkp5WrEVmewwKSM/jvjOPYAx27mrtSUx2eAs6suyZQilEIISuUyqWSMz37ssrIdChVdN9hd+QrtKzzfJxoOI6WBUgqtNZ7yWVo/h/cdeCqeUghDkliSRpgS5XoIoSllUlMkNW3H+Du/X1x3Gg2dcTFwXv+DE1RbD/x9bntclUm4jip4FIB/7u7q/BlwFbCAoPF2iI2VukUhRHFgePSVIw894Oale83vWvHKqm+DWCwEyUC7FVzfDTI0EUStMMsWLuFtex9FIhRFe4pQKorQmsEn1mGnImilKI/WEWsaAt+Y8cwNWu3svmvDxgbSM3v/9FT1BAZlFPHX3UobKDg7nHK+h+N4A1ofVVWu+4D7urs69yUouW8FxtuerQNWCy2eNcPxMYDDTjzv+HhEn2kK86xkKHqqgJTWmrgdpiWe5sCG+Rx+6DISc+qDkJnSlIey9P9xJXZdrGrJaCr5+A575hm3jKfFbundC6CgJV54ZkzOeJak2OYNHBCkLonXl+fQYEuB2gMc/5jWR83/v0RQBDflOO7Mi3jo17eOAT+++9r/e9g2zblK6+VaKxJ2lFQ4RlhaZDsHKa7PYIQM3JyLV3QINyVq+o9ovJ3AcSit8PXua3EE5KqYOXB403m7qEZW9hCkeziOv9GYrCF07XMP/frWCVGfjqb57vxUs7uwbjaL6ltojKUwpYGLwqqPgWHiOwIjYhNqTgSRFlHV6JQiAI4d5TiqTRF2122HgWcaiQsaiU1hD2TssTh2HytksufG9TnKTmV6XUEUk2buuCUT37GVMHz5d+so78BwXY1WYptLX+CqFGBPZGUPcLwRhjM0tmMEgNYUNsT85LyMVN6Uk94nSJX3CGpt1hE0t34Bu3Svq8UjYiMfs1sOHRy7YBJh5q2NSFTcesC5X7p4Ovv+4O7eI+P0/3nPrNwDHLv9EEGPkh06v8WBpE4tHAVPukCxZisBo8CTBPkmD9t7X7tFA5nrHv/ubm2h22g2+Mb1zdJNAfsAScAmUGMLVTe75rGssdQenu73JOgr78nl2LnTew9w7LqRJ+jJ2wUzSALV6OJofExIJQi0QtYBqwiEhdbae1875Wc6K67B3vtaCJo5vWu3BQ6hV3xjbO43f3TURX01xx4iUIYf3xqrWxPQQBASXwf8dPqnUuT3TMc9FsduP5655l4OuvaMLEHj6V3nDm0EiE1vyI3PvR+4m90vpCAAqeGppVapv/a32HtfW6mC4/qZ/v5JRmVX0CwEUhMjVetvlEDPJQ8UDU1WapYowQV/h1P8P/bYb7vpKD5/HdEDrt7i8XTHdY9/l6sPf98b4rdO91hrgWI7QIM77n46AfQAiW3sOi4XliGokl5PoLC/hqAvznoCLkkDzmabW/3rA16qIrynZ7lxz2DR9vI3u/lwgc49wLFn/EOMO+5+uq1qga0HeoFBNvJFpZrHZcZ7SVar1mu3d5518J6TCfz/jczT6CPaaisAAAAASUVORK5CYII=',
                    'title'     => 'WooCommerce Min/Max Quantity',
                    'desc'      => "Define quantity rules for orders, products and variations. Group the products and limit all of them together",
                    'desc_top'  => 'Define quantity rules for orders, products and variations. Group the products and limit all of them together for only ${price}!',
                    'url'       => 'https://berocket.com/product/woocommerce-minmax-quantity',
                    'bg'        => '#f5ebdd'
                ),
                array(
                    'plugin_id' => 10,
                    'id'        => 19,
                    'price'     => '29',
                    'slug'      => 'tab_manager',
                    'image'     => $host . 'Tabs.png',
                    'image_top' => 'data:image/png;base64,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',
                    'title'     => 'WooCommerce Product Tabs Manager',
                    'desc'      => "Upgrade your tabs to a powerful marketing instrument. Show there related products or special info.",
                    'desc_top'  => 'Upgrade your tabs to a powerful marketing instrument. Show there related products or special info for only ${price}!',
                    'url'       => 'https://berocket.com/product/woocommerce-product-tabs-manager',
                    'bg'        => '#955188'
                ),
                array(
                    'plugin_id' => 14,
                    'id'        => 27,
                    'price'     => '29',
                    'slug'      => 'image_watermark',
                    'image'     => $host . 'Watermark.png',
                    'image_top' => 'data:image/png;base64,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',
                    'title'     => 'WooCommerce Products Image Watermark',
                    'desc'      => "Don't let them steal it. Add watermarks to protect your images",
                    'desc_top'  => 'Don\'t let them steal it. Add watermarks to protect your images for only ${price}!',
                    'url'       => 'https://berocket.com/product/woocommerce-products-image-watermark',
                    'bg'        => '#c2c3c5'
                ),
            );
            $plugin_ids   = array_column( $plugins, 'plugin_id' );
            $plugins_data = BeRocket_Framework::get_product_data_berocket( implode( '-', $plugin_ids ) );

            if ( is_array( $plugins_data ) ) {
                foreach ( $plugins_data as $plugin_data ) {
                    if ( ! is_array( $plugin_data ) ) {
                        continue;
                    }

                    foreach ( $plugins as &$plugin ) {
                        if ( $plugin[ 'plugin_id' ] == berocket_isset( $plugin_data[ 'id' ] ) && isset( $plugin_data[ 'price' ] ) ) {
                            $plugin[ 'price' ] = $plugin_data[ 'price' ];
                            break;
                        }
                    }
                }
            }

            foreach ( $plugins as & $plugin ) {
                $plugin['desc_top'] = str_replace('{price}', $plugin['price'], $plugin['desc_top']);
            }

            if ( $plugin_id !== false ) {
                foreach ( $plugins as $plugin2 ) {
                    if ( $plugin2[ 'plugin_id' ] == $plugin_id ) {
                        return $plugin2;
                    }
                }

                return false;
            }

            return $plugins;
        }

        function show_ad_above_admin_settings($plugin_version_capability, $cur_plugin) {
            if( $plugin_version_capability < 10 ) {
                $plugin = $this->get_plugin_data($cur_plugin->info['id']);
                if( $plugin === false ) {
                    $plugin = $this->get_plugin_data(1);
                }
                echo "
                <div class='berocket-above-settings-banner'>
                    <h1>{$plugin['title']}</h1>
                    " . (empty($plugin['image_top']) ? '' : "<img src='{$plugin['image_top']}' alt='{$plugin['title']}' />" ) . "
                    <p>".(empty($plugin['desc_top']) ? $plugin['desc'] : $plugin['desc_top'])."</p>
                    <a href='{$plugin['url']}?utm_source=free_plugin&utm_medium=settings&utm_campaign={$cur_plugin->info['plugin_name']}&utm_content=top' target='_blank'>" . __('Get it now', 'BeRocket_domain') . "</a>
                </div>
                <style>
                    .berocket-above-settings-banner {
                        width: 100%;
                        padding: 20px 30px 30px;
                        background: #38395c;
                        color: #81a0cc;
                        border: 1px solid white;
                        text-align: center;
                        position: relative;
                        margin: 5px 0 15px;
                        box-sizing: border-box;
                    }
                    .berocket-above-settings-banner h1{
                        color: white;
                        padding-bottom: 25px;
                    }
                    .berocket-above-settings-banner p{
                        margin: 20px 0 15px;
                    }
                    .berocket-above-settings-banner a{
                        box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                        text-shadow: none;
                        min-width: 120px;
                        width: 250px;
                        -moz-user-select: none;
                        background: #ff5252 none repeat scroll 0 0;
                        box-sizing: border-box;
                        cursor: pointer;
                        display: inline-block;
                        font-size: 18px;
                        outline: 0 none;
                        padding: 6px 8px;
                        position: relative;
                        text-align: center;
                        text-decoration: none;
                        transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                        white-space: nowrap;
                        height: auto;
                        vertical-align: top;
                        line-height: 25px;
                        border-radius: 3px;
                        font-family: -apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Oxygen-Sans,Ubuntu,Cantarell,\"Helvetica Neue\",sans-serif;
                        font-weight: 500;
                        margin: 5px 0;
                        border: 2px solid #ff5252;
                        color: white;
                    }
                </style>
                ";
            }
        }

        function show_related_window( $html, $plugin_id, $plugin, $location = 'sidebar' ) {
            add_action( 'admin_footer', array( $this, 'wp_footer_js' ) );
            $plugins = $this->get_plugin_data();
            $plugins_use = array_rand($plugins, 2);
            
            foreach($plugins_use as $plugin_use) {
                $plugin_data = $plugins[$plugin_use];
                $html .= '
                <div class="berocket_related_plugins berocket-related-plugins-page-' . $plugin_data[ 'id' ] . '">
                    <div style="background-color: ' . $plugin_data[ 'bg' ] . ';">
                        <img style="width: 100%;" src="' . $plugin_data[ 'image' ] . '" />
                    </div>
                    <div>
                        <div>
                            <h3>' . $plugin_data[ 'title' ] . '</h3>
                            <p>' . $plugin_data[ 'desc' ] . '</p>
                            <a class="brfirst" href="' . $plugin_data[ 'url' ] . '?utm_source=free_plugin&utm_medium=settings&utm_campaign=' . $plugin->info['plugin_name'] . '&utm_content=sidebar" target="_blank">From: $' . $plugin_data[ 'price' ] . '</a>
                        </div>
                    </div>
                </div>';
            }

            $html .= '
            <style>
            .berocket_related_plugins {
                border-radius: 3px;
                box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.06);
                overflow: auto;
                position: relative;
                background-color: white;
                color: rgba(0, 0, 0, 0.87);
                padding: 0;
                margin-bottom: 30px;
                box-sizing: border-box;
                text-align: center;
                float: right;
                clear: right;
                width: 28%;
                display: flex;
                align-items: stretch;
            }
            .berocket_related_plugins > div {
                box-sizing: border-box;
                display: flex;
                align-items: center;
                float: left;
                width: 45%;
            }
            .berocket_related_plugins > div:last-child {
                width: 55%;
                padding: 4px 10px;
            }
            .berocket_related_plugins > div h3 {
                margin-top: 0;
            }
            .berocket_related_plugins a {
                margin-top: 30px;
                margin-bottom: 20px;
                box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                text-shadow: none;
                min-width: 120px;
                width: 70%;
                -moz-user-select: none;
                background: #ff5252 none repeat scroll 0 0;
                box-sizing: border-box;
                cursor: pointer;
                display: inline-block;
                font-size: 14px;
                outline: 0 none;
                padding: 4px 8px;
                position: relative;
                text-align: center;
                text-decoration: none;
                transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                white-space: nowrap;
                height: auto;
                vertical-align: top;
                line-height: 25px;
                border-radius: 3px;
                font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                font-weight: 400;
                margin: 5px 0;
                border: 2px solid #ff5252;
                color: white;
            }
            .berocket_related_plugins a:hover {
                background: #ff6e68 none repeat scroll 0 0;
                border-color: #ff6e68;
            }
            .berocket_related_plugins.berocket-related-plugins-page-1 > div:first-child {
                align-items: start;
            }
            @media screen and (min-width: 901px) and (max-width: 1700px), screen and (max-width: 500px) {
                .berocket_related_plugins > div h3 {
                    margin-bottom: 0;
                    font-size: 14px;
                }
                .berocket_related_plugins > div p {
                    margin-top: 5px;
                    margin-bottom: 5px;
                    font-size: 13px;
                    line-height: 1.3;
                }
                .berocket_related_plugins > div a {
                    padding: 1px 5px;
                    min-width: 100px;
                    width: 60%;
                }
            }
            @media screen and (max-width: 1400px) {
                .berocket_related_plugins > div {
                    width: 30%;
                }
                .berocket_related_plugins > div:last-child {
                    width: 70%;
                }
            }
            @media screen and (min-width: 901px) and (max-width: 1200px) {
                .berocket_related_plugins{
                    display: block;
                }
                .berocket_related_plugins > div{
                    float: none;
                    clear: both;
                    width: 100%;
                }
                .berocket_related_plugins > div:first-child {
                    height: 20px;
                    box-shadow: 0 0 4px 0px #ccc;
                    margin-bottom: 6px;
                }
                .berocket_related_plugins > div:last-child{
                    width: 100%;
                }
                .berocket_related_plugins > div:first-child img {
                    display: none;
                }
            }
            @media screen and (max-width: 900px) {
                .berocket_related_plugins {
                    float: none;
                    width: 100%;
                    margin-top: 30px;
                    margin-bottom: 0;
                }
            }
            </style>';

            return $html;
        }

        function show_feature_request_window($html, $plugin_id) {
            $disabled = get_option('berocket_admin_notices_rate_stars');
            $plugins = apply_filters('berocket_admin_notices_rate_stars_plugins', array());
            foreach($plugins as $plugin) {
                if( $plugin['id'] == $plugin_id ) {
                    add_action('admin_footer', array($this, 'wp_footer_js'));
                    $meta_data = '?utm_source=free_plugin&utm_medium=plugins&utm_campaign='.$plugin['plugin_name'];
                    $html .= '
                    <div class="berocket_feature_request berocket-feature-request berocket-feature-request-'.$plugin['id'].'">
                        <a class="berocket_feature_request_button" href="#feature_request">
                            <picture>
                                <source type="image/webp" srcset="'.plugin_dir_url( __FILE__ ).'../assets/images/Feature-request.webp" alt="Feature Request">
                                <img src="https://berocket.com/images/plugin/Feature-request.png" style="width: 100%;" alt="Feature Request">
                            </picture>
                        </a>
                        <div class="berocket_feature_request_form" style="display: none;">
                            <picture>
                                <source type="image/webp" srcset="'.plugin_dir_url( __FILE__ ).'../assets/images/Feature-request-form-title.webp" alt="Feature Request">
                                <img src="https://berocket.com/images/plugin/Feature-request-form-title.png" style="width: 100%;" alt="Feature Request">
                            </picture>
                            <form class="berocket_feature_request_inside">
                                <input name="brfeature_plugin" type="hidden" value="'.$plugin['id'].'">
                                <input name="brfeature_title" placeholder="'.__('Feature Title', 'BeRocket_domain').'">
                                <input name="brfeature_email" placeholder="'.__('Email (optional)', 'BeRocket_domain').'">
                                <textarea name="brfeature_description" placeholder="'.__('Feature Description', 'BeRocket_domain').'"></textarea>
                                <button class="berocket_feature_request_submit" type="submit">'.__('SEND FEATURE REQUEST', 'BeRocket_domain').'</button>
                            </form>
                            <div style="margin-bottom: 10px;">* <small>This form will be sended to <a target="_blank" href="https://berocket.com' . $meta_data . '">berocket.com</a></small></div>
                        </div>
                        <div class="berocket_feature_request_thanks" style="display: none;">
                            <picture>
                                <source type="image/webp" srcset="'.plugin_dir_url( __FILE__ ).'../assets/images/Thank-you.webp" alt="Feature Request">
                                <img src="https://berocket.com/images/plugin/Thank-you.png" style="width: 100%;" alt="Feature Request">
                            </picture>';
                    if( empty($disabled[$plugin_id]) || $disabled[$plugin_id]['time'] != 0 ) {
                        $html .= '
                        <div class="berocket_feature_request_rate berocket-rate-stars-plugin-feature-'.$plugin_id.'">
                            <h3>'.__("While you're here, you could rate this plugin", 'BeRocket_domain').'</h3>
                            <ul class="berocket-rate-stars-block">
                            <li><a class="berocket_rate_close brfirst" 
                                data-plugin="'.$plugin['id'].'" 
                                data-action="berocket_rate_stars_close" 
                                data-prevent="0" 
                                data-later="0" 
                                data-function="berocket_rate_star_close_notice"
                                data-thanks_html=\'<picture><source type="image/webp" srcset="'.plugin_dir_url( __FILE__ ).'../assets/images/Thank-you.webp" alt="Feature Request"><img src="https://berocket.com/images/plugin/Thank-you.png" style="width: 100%;" alt="Feature Request"></picture><h3 class="berocket_thank_you_rate_us">'.__('Each good feedback is very important for plugin growth', 'BeRocket_domain').'</h3>\'
                                href="https://wordpress.org/support/plugin/'.$plugin['free_slug'].'/reviews/?filter=5#new-post" 
                                target="_blank">'.__('This plugin deserves 5 stars', 'BeRocket_domain').'</a></li>
                            <li><a class="berocket_rate_next_time brsecond" 
                                href="#later">'.__("I'll rate it next time", 'BeRocket_domain').'</a></li>
                            <li><a class="berocket_rate_close brthird" 
                                data-plugin="'.$plugin['id'].'" 
                                data-action="berocket_rate_stars_close" 
                                data-prevent="1" 
                                data-later="0" 
                                data-function="berocket_rate_star_close_notice"
                                href="#close">'.__('I already rated it', 'BeRocket_domain').'</a></li>
                            </ul>
                        </div>';
                    }
                        $html .= '</div>
                    </div>
                    <style>
                        .berocket_feature_request_inside input,
                        .berocket_feature_request_inside textarea,
                        .berocket_feature_request_submit {
                            width: 90%;
                        }
                        .berocket_feature_request_submit {
                            margin-top: 30px;
                            margin-bottom: 20px;
                            color: #fff;
                            box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                            text-shadow: none;
                            border: 0 none;
                            min-width: 120px;
                            -moz-user-select: none;
                            background: #ff5252 none repeat scroll 0 0;
                            box-sizing: border-box;
                            cursor: pointer;
                            display: inline-block;
                            font-size: 14px;
                            outline: 0 none;
                            padding: 8px;
                            position: relative;
                            text-align: center;
                            text-decoration: none;
                            transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                            white-space: nowrap;
                            height: auto;
                            vertical-align: top;
                            line-height: 25px;
                            border-radius: 3px;
                            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                            font-weight: bold;
                            margin: 5px 0 15px;
                            padding: 10px;
                        }
                        .berocket_feature_request_submit:hover,
                        .berocket_feature_request_submit:focus,
                        .berocket_feature_request_submit:active {
                            background: #ff6e68 none repeat scroll 0 0;
                            color: white;
                        }
                        .berocket_feature_request_button {
                            line-height: 0;
                            overflow: hidden;
                            display: inline-block;
                        }
                        .berocket_feature_request_form {
                            overflow: auto;
                        }
                        .berocket_feature_request_button,
                        .berocket_feature_request_form,
                        .berocket_feature_request_thanks {
                            border-radius: 3px;
                            box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.06);
                            position: relative;
                            background-color: white;
                            color: rgba(0, 0, 0, 0.87);
                            margin-bottom: 30px;
                            box-sizing: border-box;
                            text-align: center;
                            float: right;
                            clear: right;
                            width: 28%;
                        }
                        .berocket_feature_request_inside {
                            padding: 0 25px;
                        }
                        .berocket_feature_request_button img,
                        .berocket_feature_request_form img {
                            width: 100%;
                        }
                        .berocket_feature_request_inside input,
                        .berocket_feature_request_inside textarea {
                            outline: none;
                            box-shadow: none;
                            resize: none;
                            margin-bottom: 10px;
                            margin-top: 10px;
                            box-shadow: 0px 0px 15px #aaa;
                            border-radius: 3px;
                            padding: 10px;
                            border: 2px solid #FFFFFF;
                        }
                        .berocket_feature_request_inside textarea {
                            height: 150px;
                            overflow: auto;
                        }
                        @media screen and (min-width: 901px) and (max-width: 1200px) {
                            .berocket_feature_request_inside{
                                padding-left: 10px;
                                padding-right: 10px;
                            }
                        }
                        .berocket_feature_request_thanks .berocket_feature_request_rate ul {
                            margin-left: 20%;
                            list-style: disc;
                        }
                        @media screen and (max-width: 900px) {
                            .berocket_feature_request_thanks .berocket_feature_request_rate ul {
                                margin-left: -80px;
                                padding-left: 50%;
                            }
                            .berocket_feature_request {
                                margin-top: 30px;
                            }
                            .berocket_feature_request_button,
                            .berocket_feature_request_form,
                            .berocket_feature_request_thanks {
                                float: none;
                                width: 100%;
                                margin-bottom: 0;
                            }
                            .berocket_feature_request_inside input,
                            .berocket_feature_request_inside textarea,
                            .berocket_feature_request_submit{
                                float: none;
                                width: 100%;
                            }
                        }
                        .berocket_feature_request_inside input.brfeature_error,
                        .berocket_feature_request_inside textarea.brfeature_error {
                            box-shadow: 0px 0px 15px #f00;
                            border-color: #ff0000;
                            animation-name: brfeature_error;
                            animation-duration: 2s;
                        }
                        @keyframes brfeature_error {
                            0%   {border-color: #ffffff;}
                            10%  {border-color: #ff0000;}
                            20%  {border-color: #ff9999;}
                            30% {border-color: #ff0000;}
                            40%   {border-color: #ff9999;}
                            50%  {border-color: #ff0000;}
                            60%  {border-color: #ff9999;}
                            70% {border-color: #ff0000;}
                            80%   {border-color: #ff9999;}
                            100%  {border-color: #ff0000;}
                        }
                        .berocket_feature_request_thanks {
                            padding-top: 20px;
                            padding-bottom: 20px;
                        }
                        .berocket_feature_request_thanks .berocket_feature_request_rate h3 {
                            color: #555;
                        }
                        .berocket_feature_request_thanks .berocket_feature_request_rate ul li {
                            text-align: left;
                        }
                    </style>';
                    return $html;
                }
            }
        }
        function wp_footer_js() {
            ?>
            <script>
                jQuery(document).on('click', '.berocket-rate-stars-block .berocket_rate_close', function(event) {
                    var $this = jQuery(this);
                    if( $this.data('prevent') ) {
                        event.preventDefault();
                    }
                    var data = $this.data();
                    if( $this.data('function') ) {
                        if( typeof(window[$this.data('function')]) == 'function' ) {
                            window[$this.data('function')](data);
                        }
                    }
                    jQuery.post(ajaxurl, data, function(result) {
                        if( $this.data('function_after') ) {
                            if( typeof(window[$this.data('function_after')]) == 'function' ) {
                                window[$this.data('function_after')](result, data);
                            }
                        }
                    });
                });
                function berocket_rate_star_close_notice(button_data) {
                    jQuery('.berocket-rate-stars-'+button_data.plugin).slideUp('100');
                    console.log(button_data);
                    if( ! button_data.prevent ) {
                        jQuery('.berocket-rate-stars-plugin-page-'+button_data.plugin).html(button_data.thanks_html);
                        jQuery('.berocket-rate-stars-plugin-feature-'+button_data.plugin).slideUp(100);
                    }
                    if( button_data.prevent && ! button_data.later ) {
                        jQuery('.berocket-rate-stars-plugin-page-'+button_data.plugin).slideUp(100);
                        jQuery('.berocket-rate-stars-plugin-feature-'+button_data.plugin).slideUp(100);
                    }
                }
                jQuery(document).on('click', '.berocket_feature_request_button', function(event) {
                    event.preventDefault();
                    var $this = jQuery(this);
                    $this.hide();
                    $this.parents('.berocket_feature_request').find('.berocket_feature_request_form').show();
                });
                jQuery(document).on('submit', '.berocket_feature_request_inside', function(event) {
                    event.preventDefault();
                    var form_data = jQuery(this).serialize();
                    var send = true;
                    if( ! jQuery(this).find('[name=brfeature_title]').val() ) {
                        send = false;
                        jQuery(this).find('[name=brfeature_title]').addClass('brfeature_error');
                    }
                    if( ! jQuery(this).find('[name=brfeature_description]').val() ) {
                        send = false;
                        jQuery(this).find('[name=brfeature_description]').addClass('brfeature_error');
                    }
                    if( send ) {
                        form_data = form_data+'&action=berocket_feature_request_send';
                        jQuery.post(ajaxurl, form_data);
                        jQuery(this).parents('.berocket_feature_request_form').hide().parents('.berocket_feature_request').find('.berocket_feature_request_thanks').show();
                    }
                });
                jQuery(document).on('change', '.brfeature_error', function() {
                    jQuery(this).removeClass('brfeature_error');
                });
                jQuery(document).on('click', '.berocket_feature_request_rate .berocket_rate_close', function(event) {
                    jQuery(this).parents('.berocket_feature_request_rate').slideUp(100);
                });
                jQuery(document).on('click', '.berocket_rate_next_time', function(event) {
                    event.preventDefault();
                    jQuery(this).parents('.berocket_feature_request_rate').slideUp(100);
                });
            </script>
            <?php
        }
    }
    new berocket_admin_notices_rate_stars;
}
?>
