<?php
/**
 * Admin View: Default Notice
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php if ( 'admin_notices' === current_action() ) : ?>
<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
<?php else : ?>
<div id="message" class="<?php echo ( 'error' !== $type ? 'updated' : 'error' ); ?> inline">
<?php endif; ?>
	<p><?php echo wp_kses_post( $message ); ?></p>
</div>
