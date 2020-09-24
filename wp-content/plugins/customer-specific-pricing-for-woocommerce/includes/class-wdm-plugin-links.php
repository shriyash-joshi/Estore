<?php
namespace WisdmPluginLinks;

/**
 * This class contains the links to be added in a plugin
 * 
 * @since 4.4.3
 */
class Links {
	/**
	 * A link to the plugin documentation
	 *
	 * @var string - documentation URL
	 */
	private static $docs = 'https://wisdmlabs.com/docs/product/wisdm-customer-specific-pricing/';
	
	/**
	 * A link to the plugin support page
	 *
	 * @var string - support page URL
	 */
	private static $support = 'https://wisdmlabs.com/contact-us/';
	
	/**
	 * A link to the plugin changelog
	 *
	 * @var string - changelog page URL
	 */
	private static $changelog = 'https://wisdmlabs.com/docs/article/wisdm-customer-specific-pricing/changelog-csp/changelog-csp/';

	
	/**
	 * This method will add extra links to the plugins listion on the
	 * wp-admin plugins page. we are adding documentation page, support page & 
	 *
	 * @param array $links
	 * @param object $file
	 * @return array - updated list of row meta links
	 */
	public static function cspPluginRowMeta( $links, $file) {
		$pluginBaseName = plugin_basename(CSP_PLUGIN_FILE);
		if ( $pluginBaseName === $file ) {
			$row_meta = array(
			'docs'    => '<a href="' . esc_url(self::$docs) . '" aria-label="' . esc_attr__( 'Docs', 'customer-specific-pricing-for-woocommerce' ) . '">' . esc_html__( 'Docs', 'customer-specific-pricing-for-woocommerce' ) . '</a>',
			'support' => '<a href="' . esc_url(self::$support) . '" aria-label="' . esc_attr__( 'Support', 'customer-specific-pricing-for-woocommerce' ) . '">' . esc_html__( 'Support', 'customer-specific-pricing-for-woocommerce' ) . '</a>',
			'changelog' => '<a href="' . esc_url(self::$changelog) . '" aria-label="' . esc_attr__( 'Changelog', 'customer-specific-pricing-for-woocommerce' ) . '">' . esc_html__( 'Changelog', 'customer-specific-pricing-for-woocommerce' ) . '</a>',
			// Add/remove links if required
			);
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
}
