<?php
/**
 * Registers the `ux_text` shortcode.
 *
 * @package flatsome
 */

/**
 * Renders the `ux_text` shortcode.
 *
 * @param array  $atts    An array of attributes.
 * @param string $content The shortcode content.
 * @param string $tag     The name of the shortcode, provided for context to enable filtering.
 *
 * @return string
 */
function flatsome_render_ux_text_shortcode( $atts, $content, $tag ) {
	$atts = shortcode_atts(
		array(
			'visibility' => '',
			'class'      => '',
		),
		$atts,
		$tag
	);

	$classes = array( 'text' );

	if ( ! empty( $atts['class'] ) )      $classes[] = $atts['class'];
	if ( ! empty( $atts['visibility'] ) ) $classes[] = $atts['visibility'];

	return '<div class="' . implode( ' ', $classes ) . '">' . do_shortcode( $content ) . '</div>';
}
add_shortcode( 'ux_text', 'flatsome_render_ux_text_shortcode' );
