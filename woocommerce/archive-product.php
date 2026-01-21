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
		<aside class="shop-filters" aria-label="<?php esc_attr_e( 'Shop filters', 'wp-augoose' ); ?>">
			<?php if ( is_active_sidebar( 'shop-filters' ) ) : ?>
				<?php dynamic_sidebar( 'shop-filters' ); ?>
			<?php else : ?>
				<?php
				// Fallback: render common Woo widgets if sidebar isn't configured yet.
				if ( class_exists( 'WC_Widget_Price_Filter' ) ) {
					the_widget( 'WC_Widget_Price_Filter', array( 'title' => __( 'Price range', 'wp-augoose' ) ) );
				}
				if ( class_exists( 'WC_Widget_Layered_Nav' ) ) {
					// Try common attributes used in your screenshots.
					the_widget( 'WC_Widget_Layered_Nav', array( 'title' => __( 'Size', 'wp-augoose' ), 'attribute' => 'size' ) );
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
							<?php esc_html_e( 'Filter', 'wp-augoose' ); ?>
						</button>
						<?php woocommerce_result_count(); ?>
						<div class="shop-view-toggle" role="group" aria-label="<?php esc_attr_e( 'View', 'wp-augoose' ); ?>">
							<button type="button" data-view="grid" class="is-active" aria-label="<?php esc_attr_e( 'Grid view', 'wp-augoose' ); ?>">▦</button>
							<button type="button" data-view="list" aria-label="<?php esc_attr_e( 'List view', 'wp-augoose' ); ?>">☰</button>
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
