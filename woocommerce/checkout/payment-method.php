<?php
/**
 * Payment method
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if this is DOKU gateway
$is_doku = false;
$gateway_id_lower = strtolower( $gateway->id );
if ( strpos( $gateway_id_lower, 'doku' ) !== false || strpos( $gateway_id_lower, 'jokul' ) !== false ) {
	$is_doku = true;
}

// Check if this is Credit/Debit Card gateway
$is_credit_card = false;
if ( strpos( $gateway_id_lower, 'card' ) !== false || 
     strpos( $gateway_id_lower, 'credit' ) !== false || 
     strpos( $gateway_id_lower, 'debit' ) !== false ||
     strpos( $gateway_id_lower, 'stripe' ) !== false ||
     strpos( $gateway_id_lower, 'ppcp' ) !== false ) {
	$is_credit_card = true;
}
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
	<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />

	<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
		<?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
		<?php if ( $is_credit_card ) : ?>
			<div class="wp-augoose-credit-card-icons">
				<?php
				// Use WooCommerce assets if available, otherwise use CDN
				$wc_plugin_url = defined( 'WC_PLUGIN_URL' ) ? WC_PLUGIN_URL : '';
				$icon_base = $wc_plugin_url ? $wc_plugin_url . 'assets/images/icons/credit-cards/' : 'https://cdn.jsdelivr.net/gh/woocommerce/woocommerce@trunk/assets/images/icons/credit-cards/';
				?>
				<img src="<?php echo esc_url( $icon_base . 'amex.svg' ); ?>" alt="American Express" class="credit-card-icon" />
				<img src="<?php echo esc_url( $icon_base . 'jcb.svg' ); ?>" alt="JCB" class="credit-card-icon" />
				<img src="<?php echo esc_url( $icon_base . 'mastercard.svg' ); ?>" alt="Mastercard" class="credit-card-icon" />
				<img src="<?php echo esc_url( $icon_base . 'visa.svg' ); ?>" alt="Visa" class="credit-card-icon" />
			</div>
		<?php else : ?>
			<?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
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
