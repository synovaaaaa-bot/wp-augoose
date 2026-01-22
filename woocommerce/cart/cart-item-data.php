<?php
/**
 * Cart item data (when outputting non-flat)
 * Custom template: Hide Market, single colon format
 *
 * @package WP_Augoose
 * @version 2.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<dl class="variation">
	<?php foreach ( $item_data as $data ) : ?>
		<?php
		// Skip Market attribute
		$key = isset( $data['key'] ) ? strtolower( trim( $data['key'] ) ) : '';
		if ( strpos( $key, 'market' ) !== false ) {
			continue; // Hide Market
		}
		
		// Format: single colon "Key: Value"
		$key_label = isset( $data['key'] ) ? trim( str_replace( ':', '', $data['key'] ) ) : '';
		$value_raw = isset( $data['value'] ) ? trim( $data['value'] ) : '';
		
		// Clean value: remove any existing colon and extra spaces
		$value_clean = preg_replace( '/:+/', '', $value_raw );
		$value_clean = trim( $value_clean );
		
		// Format: "Key: Value" (single colon, no colon in dt)
		$value_display = ! empty( $key_label ) && ! empty( $value_clean ) ? $value_clean : $value_raw;
		?>
		<dt class="<?php echo sanitize_html_class( 'variation-' . $key_label ); ?>"><?php echo wp_kses_post( $key_label ); ?>:</dt>
		<dd class="<?php echo sanitize_html_class( 'variation-' . $key_label ); ?>"><?php echo wp_kses_post( $value_display ); ?></dd>
	<?php endforeach; ?>
</dl>
