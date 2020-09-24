<?php
	$features = array(
		__( 'Automatic updates of exchange rates.', 'woocommerce-product-price-based-on-countries' ),
		__( 'Round up to nearest.', 'woocommerce-product-price-based-on-countries' ),
		__( 'Extra fee to exchange rate.', 'woocommerce-product-price-based-on-countries' ),
		__( 'Display the currency code next to price.', 'woocommerce-product-price-based-on-countries' ),
		__( 'Thousand separator, decimal separator and number of decimals by pricing zone.', 'woocommerce-product-price-based-on-countries' ),
		__( 'Currency switcher widget.', 'woocommerce-product-price-based-on-countries' ),
		__( 'Support for manual orders.', 'woocommerce-product-price-based-on-countries' ),
		__( 'Support for the import/export WooCommerce tool.', 'woocommerce-product-price-based-on-countries' )
	);
?>
<a href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro" class="wc-price-based-country-sidebar-section main" target="_blank" rel="noopener noreferrer">
	<h2><span class="feature_text"><?php _e( 'Upgrade to Pro version', 'woocommerce-product-price-based-on-countries' ); ?></h2>
</a>
<a href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro" class="wc-price-based-country-sidebar-section" target="_blank" rel="noopener noreferrer">
	<div class="section-title">
		<div class="dashicons dashicons-star-filled"></div>
		<h3><?php _e( 'Professional features', 'woocommerce-product-price-based-on-countries' ); ?></h3>
	</div>
	<ul class="feature-list"><?php foreach ( $features as $feature ) : ?>
		<li><?php echo $feature; ?></li><?php endforeach; ?>
	</ul>
</a>
<a href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro" class="wc-price-based-country-sidebar-section" target="_blank" rel="noopener noreferrer">
	<div class="section-title">
		<div class="dashicons dashicons-woocommerce"></div>
		<h3><?php _e( 'Compatible with most popular WooCommerce plugins', 'woocommerce-product-price-based-on-countries' ); ?></h3>
	</div>
	<ul class="feature-list">
		<li>WooCommerce Product Add-ons</li>
		<?php foreach ( array_unique( wcpbc_product_types_supported( 'pro' ) ) as $feature ) : ?>
		<li><?php echo $feature; ?></li><?php endforeach; ?>
	</ul>
</a>
<div class="wc-price-based-country-sidebar-section">
	<p><span class="dashicons dashicons-thumbs-up"> </span><?php _e( 'No ads.', 'woocommerce-product-price-based-on-countries' ); ?></p>
	<p><span class="dashicons dashicons-thumbs-up"> </span><?php _e( 'Guaranteed support.', 'woocommerce-product-price-based-on-countries' ); ?></p>
	<p><span class="dashicons dashicons-thumbs-up"> </span><?php _e( 'More features and integrations are coming.', 'woocommerce-product-price-based-on-countries' ); ?></p>
	<p class="cta">
		<a target="_blank" rel="noopener noreferrer" class="cta-button" href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro">
			<?php _e( 'Upgrade to Pro version now!', 'woocommerce-product-price-based-on-countries' ); ?>
		</a>
	</p>
</div>
