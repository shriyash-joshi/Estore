<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wt_Advanced_Order_Number_i18n {

    public function load_plugin_textdomain() {

        load_plugin_textdomain(
                'wt-woocommerce-sequential-order-numbers', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

}