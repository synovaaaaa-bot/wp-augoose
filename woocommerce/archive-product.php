<?php
/**
 * The Template for displaying product archives
 *
 * @package WP_Augoose
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="shop-page" data-shop-page>
	<header class="woocommerce-products-header">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>

		<?php
		/**
		 * Hook: woocommerce_archive_description.
		 */
		do_action( 'woocommerce_archive_description' );
		?>
	</header>

	<div class="shop-layout">
		<aside class="shop-filters" aria-label="Shop filters">
			<button type="button" class="shop-filter-close" aria-label="Close filters">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<line x1="18" y1="6" x2="6" y2="18"></line>
					<line x1="6" y1="6" x2="18" y2="18"></line>
				</svg>
			</button>
			<?php if ( is_active_sidebar( 'shop-filters' ) ) : ?>
				<?php dynamic_sidebar( 'shop-filters' ); ?>
			<?php else : ?>
				<?php
				// Fallback: render common Woo widgets if sidebar isn't configured yet.
				if ( class_exists( 'WC_Widget_Price_Filter' ) ) {
					the_widget( 'WC_Widget_Price_Filter', array( 'title' => 'Price Range' ) );
				}
				if ( class_exists( 'WC_Widget_Layered_Nav' ) ) {
					// Get all product attributes from WooCommerce
					$attribute_taxonomies = wc_get_attribute_taxonomies();
					
					// Find size and color attributes
					$size_attr = null;
					$color_attr = null;
					
					foreach ( $attribute_taxonomies as $attr ) {
						$attr_name = strtolower( $attr->attribute_name );
						if ( $attr_name === 'size' && ! $size_attr ) {
							$size_attr = 'pa_' . $attr->attribute_name;
						}
						if ( ( $attr_name === 'color' || $attr_name === 'colour' ) && ! $color_attr ) {
							$color_attr = 'pa_' . $attr->attribute_name;
						}
					}
					
					// Render Size filter if exists
					if ( $size_attr && taxonomy_exists( $size_attr ) ) {
						$size_label = wc_attribute_label( $size_attr );
						the_widget( 'WC_Widget_Layered_Nav', array( 'title' => $size_label ?: 'Size', 'attribute' => $size_attr ) );
					}
					
					// Render Color filter if exists
					if ( $color_attr && taxonomy_exists( $color_attr ) ) {
						$color_label = wc_attribute_label( $color_attr );
						the_widget( 'WC_Widget_Layered_Nav', array( 'title' => $color_label ?: 'Color', 'attribute' => $color_attr ) );
					}
				}
				?>
			<?php endif; ?>
		</aside>

		<section class="shop-content">
			<?php
			if ( woocommerce_product_loop() ) {
				woocommerce_output_all_notices();
				?>
				<div class="shop-toolbar">
					<div class="shop-toolbar-left">
						<button type="button" class="shop-filter-toggle" aria-expanded="false">
							Filter
						</button>
						<?php woocommerce_result_count(); ?>
						<div class="shop-view-toggle" role="group" aria-label="View">
							<button type="button" data-view="grid" class="is-active" aria-label="Grid view">▦</button>
						</div>
					</div>
					<div class="shop-toolbar-right">
						<?php woocommerce_catalog_ordering(); ?>
					</div>
				</div>
				<?php

				woocommerce_product_loop_start();

				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();
						do_action( 'woocommerce_shop_loop' );
						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();

				do_action( 'woocommerce_after_shop_loop' );
			} else {
				do_action( 'woocommerce_no_products_found' );
			}
			?>
		</section>
	</div>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
