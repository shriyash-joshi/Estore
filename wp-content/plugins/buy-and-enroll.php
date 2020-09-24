<?php
/**
 * Plugin Name: Buy and Enroll Form Shortcode 
  * Version: 1.0.0
 * Author: Sreenivas
 * Requires at least: 4.8
 * Tested up to: 5.2.0
 * WC requires at least: 3.2.0
 * WC tested up to: 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if( ! function_exists( 'uni_add_to_cart_form_shortcode' ) ) {
	/**
	 * Display a single product with single-product/add-to-cart/$product_type.php template.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	function uni_add_to_cart_form_shortcode( $atts ) {

		if ( empty( $atts ) ) {
			return '';
		}

		if ( ! isset( $atts['id'] ) && ! isset( $atts['sku'] ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'id'				  => '',
			'sku'				  => '',
			'status'			  => 'publish',
			'show_price'		  => 'true',
            'hide_quantity'		  => 'false',
            'button_label'        => 'Buy and Enroll'     
		), $atts, 'product_add_to_cart_form' );

		$query_args = array(
			'posts_per_page'      => 1,
			'post_type'           => 'product',
			'post_status'         => $atts['status'],
			'ignore_sticky_posts' => 1,
			'no_found_rows'       => 1
		);

		if ( ! empty( $atts['sku'] ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_sku',
				'value'   => sanitize_text_field( $atts['sku'] ),
				'compare' => '=',
			);

			$query_args['post_type'] = array( 'product', 'product_variation' );
		}

		if ( ! empty( $atts['id'] ) ) {
			$query_args['p'] = absint( $atts['id'] );
		}

		// Hide quantity input if desired.		
		if( $atts['hide_quantity'] == 'true' ) {
			add_filter( 'woocommerce_quantity_input_min', 'uni_add_to_cart_form_return_one' );
			add_filter( 'woocommerce_quantity_input_max', 'uni_add_to_cart_form_return_one' );
        }
        
        if( $atts['button_label'] !==''){
            add_filter( 'woocommerce_product_single_add_to_cart_text', 'uni_btntext_cart' );
            add_filter( 'woocommerce_product_add_to_cart_text', 'uni_btntext_cart' );
            $button_label = sanitize_text_field( $atts['button_label'] );
            function uni_btntext_cart() {
                return __( 'Buy and Enroll', 'woocommerce' );
            }

           
        }

        

		// Change form action to avoid redirect.
		add_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );

		$single_product = new WP_Query( $query_args );

		$preselected_id = '0';

		// Check if sku is a variation.
		if ( ! empty( $atts['sku'] ) && $single_product->have_posts() && 'product_variation' === $single_product->post->post_type ) {

			$variation  = new WC_Product_Variation( $single_product->post->ID );
			$attributes = $variation->get_attributes();

			// Set preselected id to be used by JS to provide context.
			$preselected_id = $single_product->post->ID;

			// Get the parent product object.
			$query_args = array(
				'posts_per_page'      => 1,
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'p'                   => $single_product->post->post_parent,
			);

			$single_product = new WP_Query( $query_args );
		?>
			<script type="text/javascript">
				jQuery( document ).ready( function( $ ) {
					var $variations_form = $( '[data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>"]' ).find( 'form.variations_form' );

					<?php foreach ( $attributes as $attr => $value ) { ?>
						$variations_form.find( 'select[name="<?php echo esc_attr( $attr ); ?>"]' ).val( '<?php echo esc_js( $value ); ?>' );
					<?php } ?>
				});
			</script>
		<?php
		}

		// For "is_single" to always make load comments_template() for reviews.
		$single_product->is_single = true;

		ob_start();

		global $wp_query;

		// Backup query object so following loops think this is a product page.
		$previous_wp_query = $wp_query;
		// @codingStandardsIgnoreStart
		$wp_query          = $single_product;
		// @codingStandardsIgnoreEnd

		wp_enqueue_script( 'wc-single-product' );

		while ( $single_product->have_posts() ) {
			$single_product->the_post();

			?>
			<div class="single-product add_to_cart_form_shortcode" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">

				<?php 
				if ( wc_string_to_bool( $atts['show_price'] ) ) {
					woocommerce_template_single_price();
				}
				?>

				<?php woocommerce_template_single_add_to_cart() ?>
			</div>
			<?php
		}

		// Restore $previous_wp_query and reset post data.
		// @codingStandardsIgnoreStart
		$wp_query = $previous_wp_query;
		// @codingStandardsIgnoreEnd
		wp_reset_postdata();

		// Remove filters.
		remove_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );
		remove_filter( 'woocommerce_quantity_input_min', 'uni_add_to_cart_form_return_one' );
		remove_filter( 'woocommerce_quantity_input_max', 'uni_add_to_cart_form_return_one' );

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}
}
add_shortcode( 'add_to_cart_form', 'uni_add_to_cart_form_shortcode' );

if( ! function_exists( 'uni_add_to_cart_form_redirect' ) ) {
	/**
	 * Redirect to same page
	 *
	 * @return string
	 */
	function uni_add_to_cart_form_redirect( $url ) {
        add_filter( 'woocommerce_add_to_cart_redirect', 'skip_woo_cart' ); 
        function skip_woo_cart() {
            return wc_get_checkout_url();
        }

	}
}



if( ! function_exists( 'uni_add_to_cart_form_return_one' ) ) {
	/**
	 * Return integer
	 *
	 * @return int
	 */
	function uni_add_to_cart_form_return_one() {
		return 1;
	}
}