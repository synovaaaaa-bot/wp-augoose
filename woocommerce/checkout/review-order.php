<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WP_Augoose
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;
?>

<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<td class="product-name">
						<div class="product-item-summary">
							<div class="product-thumbnail">
								<?php
								$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
								echo $thumbnail; // PHPCS: XSS ok.
								?>
							</div>
							<div class="product-details">
								<div class="product-title"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></div>
								<div class="product-meta">
									<?php
									// Variation details
									if ( $_product->is_type( 'variation' ) ) {
										$variation_data = $_product->get_variation_attributes();
										$variation_parts = array();
										foreach ( $variation_data as $key => $value ) {
											$label = wc_attribute_label( str_replace( 'attribute_', '', $key ) );
											$variation_parts[] = $label . ': ' . $value;
										}
										if ( ! empty( $variation_parts ) ) {
											echo '<span class="product-variation">' . esc_html( implode( ' / ', $variation_parts ) ) . '</span>';
										}
									}
									?>
									<div class="product-quantity-wrapper">
										<label class="quantity-label">Quantity:</label>
										<div class="quantity-input-group">
											<button type="button" class="qty-btn qty-minus" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="Decrease quantity">
												<span class="qty-icon">−</span>
											</button>
											<input type="number" 
												   class="qty-input" 
												   name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]" 
												   value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" 
												   min="1" 
												   max="<?php echo esc_attr( $_product->get_max_purchase_quantity() ); ?>" 
												   step="1"
												   data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" />
											<button type="button" class="qty-btn qty-plus" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="Increase quantity">
												<span class="qty-icon">+</span>
											</button>
										</div>
										<button type="button" class="remove-item-btn" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" title="Remove item">
											<svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor">
												<path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z"/>
											</svg>
										</button>
									</div>
									<div class="product-price-mobile">
										<?php
										echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
										?>
									</div>
								</div>
							</div>
						</div>
					</td>
					<td class="product-total">
						<?php
						echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
						?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

		<?php
		// Display currency conversion notice if items were converted
		$show_notice = false;
		$original_currency = null;
		$current_currency = get_woocommerce_currency();
		
		// Always show notice if current currency is IDR (items were converted)
		// This ensures note appears even if original currency is not found
		if ( $current_currency === 'IDR' ) {
			$show_notice = true;
			
			// Try to get original currency from multiple sources
			if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
				// 1. Check cart items
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( isset( $cart_item['wp_augoose_original_currency'] ) ) {
						$original_currency = $cart_item['wp_augoose_original_currency'];
						if ( in_array( $original_currency, array( 'SGD', 'MYR' ), true ) ) {
							break;
						}
					}
				}
			}
			
			// 2. Check session
			if ( ! $original_currency && function_exists( 'WC' ) && WC()->session ) {
				$original_currency = WC()->session->get( 'wp_augoose_original_currency' );
			}
			
			// 3. Check cookie
			if ( ! $original_currency && isset( $_COOKIE['wp_augoose_currency'] ) ) {
				$cookie_currency = strtoupper( trim( sanitize_text_field( $_COOKIE['wp_augoose_currency'] ) ) );
				if ( in_array( $cookie_currency, array( 'SGD', 'MYR' ), true ) ) {
					$original_currency = $cookie_currency;
				}
			}
		}
		
		// Always show notice if currency is IDR
		if ( $show_notice ) {
			$notice_text = '';
			if ( $original_currency && in_array( $original_currency, array( 'SGD', 'MYR' ), true ) ) {
				$notice_text = sprintf(
					'<strong>Price converted:</strong> All prices shown above have been converted to IDR (Indonesian Rupiah) for checkout purposes. Original currency was %s.',
					esc_html( $original_currency )
				);
			} else {
				$notice_text = '<strong>Price converted:</strong> All prices shown above have been converted to IDR (Indonesian Rupiah) for checkout purposes.';
			}
			?>
			<tr class="currency-conversion-notice">
				<th></th>
				<td class="currency-conversion-notice-content">
					<div class="currency-conversion-info">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
							<path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9h2v4h-2V5zm0 5h2v2H7v-2z"/>
						</svg>
						<span>
							<?php echo wp_kses_post( $notice_text ); ?>
						</span>
					</div>
				</td>
			</tr>
			<?php
		}
		?>

	</tfoot>
</table>
