<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//Shortcode for Handling Unsubscription.
add_shortcode('SCHEDULER_UNSUBSCRIPTION', array('Includes\SchedulerHandleUnsubscription', 'unsubscriptionShortcodeCallback'));
