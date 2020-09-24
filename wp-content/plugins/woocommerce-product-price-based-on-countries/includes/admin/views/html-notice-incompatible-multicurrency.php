<?php
/**
 * Admin View: Notice - WPML Multicurrency
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error notice-pbc">
	<p><strong>WooCommerce Price Based on Country:</strong> <?php echo wp_kses_post( $message_text ); ?></p>
</div>
