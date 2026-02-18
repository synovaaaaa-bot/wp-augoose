<?php
/**
 * Payment method
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get gateway info
$gateway_id_lower = strtolower( $gateway->id );
$gateway_title_lower = strtolower( $gateway->get_title() );

// Detect payment method type
$is_doku = strpos( $gateway_id_lower, 'doku' ) !== false || strpos( $gateway_id_lower, 'jokul' ) !== false;
$is_paypal = strpos( $gateway_id_lower, 'paypal' ) !== false || strpos( $gateway_id_lower, 'ppcp' ) !== false || strpos( $gateway_title_lower, 'paypal' ) !== false;
$is_google_pay = strpos( $gateway_id_lower, 'google' ) !== false || strpos( $gateway_title_lower, 'google pay' ) !== false;
$is_apple_pay = strpos( $gateway_id_lower, 'apple' ) !== false || strpos( $gateway_title_lower, 'apple pay' ) !== false;
$is_credit_card = ( strpos( $gateway_id_lower, 'card' ) !== false || 
                    strpos( $gateway_id_lower, 'credit' ) !== false || 
                    strpos( $gateway_id_lower, 'debit' ) !== false ||
                    strpos( $gateway_id_lower, 'stripe' ) !== false ) && 
                  ! $is_paypal && ! $is_google_pay && ! $is_apple_pay;
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
	<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />

	<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
		<?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
		<?php if ( $is_credit_card ) : ?>
			<div class="wp-augoose-credit-card-icons">
				<?php
				// Credit card icons
				$icon_base = 'https://cdn.jsdelivr.net/gh/woocommerce/woocommerce@8.0/assets/images/icons/credit-cards/';
				if ( defined( 'WC_PLUGIN_URL' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/assets/images/icons/credit-cards/visa.svg' ) ) {
					$icon_base = WC_PLUGIN_URL . 'assets/images/icons/credit-cards/';
				}
				?>
				<img src="<?php echo esc_url( $icon_base . 'visa.svg' ); ?>" alt="Visa" class="credit-card-icon" onerror="this.style.display='none';" />
				<img src="<?php echo esc_url( $icon_base . 'mastercard.svg' ); ?>" alt="Mastercard" class="credit-card-icon" onerror="this.style.display='none';" />
				<img src="<?php echo esc_url( $icon_base . 'amex.svg' ); ?>" alt="American Express" class="credit-card-icon" onerror="this.style.display='none';" />
				<img src="<?php echo esc_url( $icon_base . 'jcb.svg' ); ?>" alt="JCB" class="credit-card-icon" onerror="this.style.display='none';" />
			</div>
		<?php elseif ( $is_paypal ) : ?>
			<div class="wp-augoose-paypal-icon">
				<img src="https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_marcas_206x29.png" alt="PayPal" class="paypal-icon" onerror="this.onerror=null; this.src='https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg';" />
			</div>
		<?php elseif ( $is_google_pay ) : ?>
			<div class="wp-augoose-google-pay-icon">
				<svg width="40" height="24" viewBox="0 0 40 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="40" height="24" rx="4" fill="#ffffff" stroke="#e0e0e0" stroke-width="0.5"/>
					<!-- Google Pay logo -->
					<text x="4" y="16" font-family="Arial, sans-serif" font-size="9" font-weight="bold" fill="#1f2937">Google Pay</text>
				</svg>
			</div>
		<?php elseif ( $is_apple_pay ) : ?>
			<div class="wp-augoose-apple-pay-icon">
				<svg width="40" height="24" viewBox="0 0 40 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="40" height="24" rx="4" fill="#000000"/>
					<!-- Apple Pay logo -->
					<text x="4" y="16" font-family="Arial, sans-serif" font-size="8" font-weight="bold" fill="#ffffff">Apple Pay</text>
				</svg>
			</div>
		<?php else : ?>
			<?php 
			// Get gateway icon fallback
			$gateway_icon = $gateway->get_icon();
			if ( ! empty( $gateway_icon ) ) {
				echo $gateway_icon; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
			}
			?>
		<?php endif; ?>
	</label>
	<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
		<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php if ( ! $gateway->chosen ) : /* phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace */ ?>style="display:none;"<?php endif; /* phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace */ ?>>
			<?php $gateway->payment_fields(); ?>
		</div>
	<?php endif; ?>
	
	<?php if ( $is_doku ) : ?>
		<div class="wp-augoose-payment-notice wp-augoose-doku-notice" data-payment-method="<?php echo esc_attr( $gateway->id ); ?>" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
			<p>You can pay securely using DOKU payment gateway.</p>
			<p class="wp-augoose-notice-highlight">All payments made using this method will be converted to Indonesian Rupiah (IDR) in accordance with applicable national regulations. Thank you.</p>
		</div>
	<?php endif; ?>
</li>
<script>
(function() {
	var paymentMethod = '<?php echo esc_js( $gateway->id ); ?>';
	var radioInput = document.getElementById('payment_method_<?php echo esc_js( $gateway->id ); ?>');
	var notice = document.querySelector('.wp-augoose-payment-notice[data-payment-method="' + paymentMethod + '"]');
	
	if (radioInput && notice) {
		function toggleNotice() {
			if (radioInput.checked) {
				notice.style.display = 'block';
			} else {
				notice.style.display = 'none';
			}
		}
		
		radioInput.addEventListener('change', toggleNotice);
		
		// Also listen to WooCommerce checkout update event
		if (typeof jQuery !== 'undefined') {
			jQuery(document.body).on('payment_method_selected', toggleNotice);
		}
	}
})();
</script>
