<?php
/**
 * Template for Payment Failed Page
 *
 * @package WP_Augoose
 */

get_header();

// Get order details from URL
$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
$order_key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';

$order = null;
if ( $order_id && $order_key ) {
	$order = wc_get_order( $order_id );
	if ( $order && $order->get_order_key() !== $order_key ) {
		$order = null;
	}
}
?>

<main id="primary" class="site-main">
	<div class="payment-failed-wrapper">
		<div class="container">
			<div class="payment-failed-content">
				<div class="payment-failed-icon">
					<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="32" cy="32" r="30" stroke="#d32f2f" stroke-width="2"/>
						<path d="M32 20V36M32 44H32.02" stroke="#d32f2f" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</div>
				
				<h1 class="payment-failed-title"><?php esc_html_e( 'Payment Failed', 'woocommerce' ); ?></h1>
				
				<p class="payment-failed-message">
					<?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?>
				</p>
				
				<?php if ( $order ) : ?>
					<div class="payment-failed-order-info">
						<p class="order-info-label"><?php esc_html_e( 'Order Number:', 'woocommerce' ); ?></p>
						<p class="order-info-value"><?php echo esc_html( $order->get_order_number() ); ?></p>
						
						<p class="order-info-label"><?php esc_html_e( 'Order Total:', 'woocommerce' ); ?></p>
						<p class="order-info-value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></p>
					</div>
				<?php endif; ?>
				
				<div class="payment-failed-actions">
					<?php if ( $order ) : ?>
						<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button button-primary payment-retry-btn">
							<?php esc_html_e( 'Try Payment Again', 'woocommerce' ); ?>
						</a>
					<?php endif; ?>
					
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button button-secondary payment-shop-btn">
						<?php esc_html_e( 'Continue Shopping', 'woocommerce' ); ?>
					</a>
					
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button button-link payment-account-btn">
							<?php esc_html_e( 'View My Account', 'woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</div>
				
				<div class="payment-failed-help">
					<p class="help-title"><?php esc_html_e( 'Need Help?', 'woocommerce' ); ?></p>
					<p class="help-text">
						<?php esc_html_e( 'If you continue to experience issues, please contact us for assistance.', 'woocommerce' ); ?>
					</p>
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ?: '#' ); ?>" class="help-link">
						<?php esc_html_e( 'Contact Us', 'woocommerce' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
