<?php
/* 
Plugin Name: blockemails
Plugin URI: 
Description: prevent sending email notifications to users 
Author: 
Version: 1
*/
// disable all new user notification email
if ( ! function_exists( 'wp_new_user_notification' ) ) : 
function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) { return; } 
endif;