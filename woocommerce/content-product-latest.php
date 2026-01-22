<?php
/**
 * Template for Latest Collection products
 * Custom structure: .lc-card > .lc-thumb (440px) + .lc-info (120px)
 *
 * @package WP_Augoose
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// Check if product is on sale
$is_on_sale = $product->is_on_sale();

// Check if product is featured
$is_featured = $product->is_featured();

// Check if product is new (created <= 14 days)
$product_date = $product->get_date_created();
$days_since_creation = $product_date ? $product_date->diff( new DateTime() )->days : 999;
$is_new = $days_since_creation <= 14;

// Determine badge (priority: SALE > FEATURED > NEW ARRIVAL)
$badge_text = '';
$badge_class = '';
if ( $is_on_sale ) {
	$badge_text = 'SALE';
	$badge_class = 'lc-badge--sale';
} elseif ( $is_featured ) {
	$badge_text = 'FEATURED';
	$badge_class = 'lc-badge--featured';
} elseif ( $is_new ) {
	$badge_text = 'NEW ARRIVAL';
	$badge_class = 'lc-badge--new';
}
?>

<li <?php wc_product_class( 'product-item lc-card', $product ); ?>>
	<div class="product-inner">
		
		<!-- Image Area: .lc-thumb (440px) -->
		<div class="product-thumbnail lc-thumb" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
			
			<!-- Badge Overlay (Top Left) -->
			<?php if ( $badge_text ) : ?>
				<span class="lc-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
			<?php endif; ?>
			
			<!-- Wishlist Heart Icon (Top Right) -->
			<div class="product-wishlist">
				<button class="wishlist-toggle add-to-wishlist" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="Add to wishlist">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
						<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
					</svg>
				</button>
			</div>
			
			<?php
			// Product link wrapper for image
			if ( function_exists( 'woocommerce_template_loop_product_link_open' ) ) {
				woocommerce_template_loop_product_link_open();
			}
			
			// Thumbnail
			if ( function_exists( 'woocommerce_template_loop_product_thumbnail' ) ) {
				woocommerce_template_loop_product_thumbnail();
			}
			
			if ( function_exists( 'woocommerce_template_loop_product_link_close' ) ) {
				woocommerce_template_loop_product_link_close();
			}
			?>
		</div>
		
		<!-- Info Area: .lc-info (120px) -->
		<div class="product-info lc-info">
			
			<!-- Product Title -->
			<?php
			/**
			 * Hook: woocommerce_shop_loop_item_title.
			 *
			 * @hooked woocommerce_template_loop_product_title - 10
			 */
			do_action( 'woocommerce_shop_loop_item_title' );
			?>
			
			<!-- Product Price -->
			<?php
			/**
			 * Hook: woocommerce_after_shop_loop_item_title.
			 * We only want price, not rating
			 */
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			do_action( 'woocommerce_after_shop_loop_item_title' );
			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			?>
			
			<!-- CTA Button: ADD TO CART (WAJIB untuk semua card) -->
			<div class="lc-cta">
				<?php
				// Use WooCommerce add to cart button
				if ( $product->is_type( 'simple' ) && $product->is_in_stock() ) {
					?>
					<form class="cart" action="<?php echo esc_url( $product->get_permalink() ); ?>" method="post" enctype='multipart/form-data'>
						<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
						<button type="submit" class="add_to_cart_button lc-button" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
							ADD TO CART
						</button>
					</form>
					<?php
				} elseif ( $product->is_type( 'variable' ) ) {
					// Variable product: link to product page
					?>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="add_to_cart_button lc-button">
						ADD TO CART
					</a>
					<?php
				} elseif ( ! $product->is_in_stock() ) {
					?>
					<button type="button" class="lc-button" disabled>
						SOLD OUT
					</button>
					<?php
				} else {
					// Fallback: link to product page
					?>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="add_to_cart_button lc-button">
						ADD TO CART
					</a>
					<?php
				}
				?>
			</div>
		</div>
		
	</div>
</li>
