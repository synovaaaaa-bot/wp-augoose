<?php
/**
 * Payment methods
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $available_gateways ) ) {
	foreach ( $available_gateways as $gateway ) {
		wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
	}
} else {
	echo '<p class="woocommerce-info">' . esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) . '</p>';
}
?>

<div class="checkout-place-order">
	<?php do_action( 'woocommerce_checkout_before_terms_and_conditions' ); ?>

	<?php
	$terms_page_id = wc_terms_and_conditions_page_id();
	if ( $terms_page_id && apply_filters( 'woocommerce_checkout_show_terms', true ) ) {
		$terms         = get_post( $terms_page_id );
		$terms_content = has_shortcode( $terms->post_content, 'woocommerce_checkout' ) ? $terms->post_content : '';
		if ( $terms_content ) {
			echo '<div class="woocommerce-terms-and-conditions-wrapper" style="display:none; max-height:200px; overflow:auto; ' . esc_attr( apply_filters( 'woocommerce_checkout_terms_and_conditions_content_style', '' ) ) . '">' . wp_kses_post( $terms_content ) . '</div>';
		}
		?>
		<?php if ( apply_filters( 'woocommerce_checkout_show_terms', true ) ) : ?>
			<p class="form-row terms wc-terms-and-conditions">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); // WPCS: input var ok, csrf ok. ?> id="terms" />
					<span class="woocommerce-terms-and-conditions-checkbox-text"><?php wc_terms_and_conditions_checkbox_text(); ?></span>&nbsp;<span class="required">*</span>
				</label>
				<input type="hidden" name="terms-field" value="1" />
			</p>
		<?php endif; ?>
		<?php
	}
	?>

	<?php do_action( 'woocommerce_checkout_after_terms_and_conditions' ); ?>

	<?php
	$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
	?>

	<button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_place_order" id="place_order" value="<?php echo esc_attr( $order_button_text ); ?>" data-value="<?php echo esc_attr( $order_button_text ); ?>">
		<?php echo esc_html( $order_button_text ); ?>
	</button>

	<p class="checkout-security-note">
		<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
			<path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9h2v4h-2V5zm0 5h2v2H7v-2z"/>
		</svg>
		Secure checkout + Email confirmation sent
	</p>

	<?php do_action( 'woocommerce_checkout_after_submit' ); ?>

	<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
</div>
