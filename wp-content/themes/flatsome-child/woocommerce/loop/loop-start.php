<?php
/**
 * Product Loop Start
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cols = esc_attr( wc_get_loop_prop( 'columns' ) );
?>
<div class="products row row-small large-columns-3 medium-columns-2 small-columns-1 equalize-box">
<?php

