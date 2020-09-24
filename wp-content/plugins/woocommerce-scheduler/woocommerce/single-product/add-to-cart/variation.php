<?php
/**
 * Single variation display
 *
 * This is a javascript-based template for single variations (see https://codex.wordpress.org/Javascript_Reference/wp.template).
 * The values will be dynamically replaced after selecting attributes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.5.0
 */
if (! defined('ABSPATH')) {
    exit;
}
global $post;

$parent_id = $post->ID;
$product = wc_get_product($parent_id);

// $childrens = $product->get_children();
// $variation_id = isset($childrens[0]) ? $childrens[0] : "";
$wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();

$expirationMsg = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration']) ? __($wdmwsSettings['wdmws_custom_product_expiration'], WDM_WOO_SCHED_TXT_DOMAIN) : "Currently Unavailable" ;

$wdmwsEndTimerText = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_end_timer_text']) ? __($wdmwsSettings['wdmws_end_timer_text'], WDM_WOO_SCHED_TXT_DOMAIN) : __("Available For:", WDM_WOO_SCHED_TXT_DOMAIN) ;

$wdmwsStartTimerText = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_start_timer_text']) ? __($wdmwsSettings['wdmws_start_timer_text'], WDM_WOO_SCHED_TXT_DOMAIN) : __("Available In:", WDM_WOO_SCHED_TXT_DOMAIN) ;

$launchTimerText = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_launch_start_timer_text']) ? __($wdmwsSettings['wdmws_launch_start_timer_text'], WDM_WOO_SCHED_TXT_DOMAIN) : __("Wil Be Launched In:", WDM_WOO_SCHED_TXT_DOMAIN) ;

?>
<script type="text/template" id="tmpl-variation-template">
    <div class="woocommerce-variation-description">
        {{{ data.variation.variation_description }}}
    </div>

    <div class="woocommerce-variation-price">
        {{{ data.variation.price_html }}}
    </div>

    <div class="woocommerce-variation-availability">
        <div class = 'wdmws_timer_circles' id = 'display_end_timer'>
            <p><?php echo $wdmwsEndTimerText; ?></p>
            <div id = 'wdmws_end_timer' data-date=''></div>
        </div>
        {{{ data.variation.availability_html }}}
    </div>
</script>

<script type="text/template" id="tmpl-unavailable-variation-template">
    <p>
    <?php
    if ($expirationMsg != "") { ?>
        <p class='wdm_message'><?php echo $expirationMsg; ?></p>
        <div class = 'wdmws_timer_circles' id = 'display_start_timer'>
            <p class="launch"><?php echo $launchTimerText; ?></p>
            <p class="start"><?php echo $wdmwsStartTimerText; ?></p>
            <div id = 'wdmws_start_timer' data-date='' data-timer='' ></div>
        </div>
    <?php } elseif (current_user_can('manage_options')) { ?>
    <p class='wdm_message'><?php _e('You can set a custom message in <br/>Scheduler for Woocommerce->Settings->Global->Single Product Expiration Message', WDM_WOO_SCHED_TXT_DOMAIN); ?></p>
    <?php } ?>
    </p>
    {{{ data.variation.wdmws_notify_button }}}
</script>
