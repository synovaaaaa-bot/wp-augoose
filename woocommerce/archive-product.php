<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WP_Augoose
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
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

	<?php
	/**
	 * Hook: woocommerce_shop_loop_header.
	 *
	 * @since 8.6.0
	 *
	 * @hooked woocommerce_product_taxonomy_archive_header - 10
	 */
	do_action( 'woocommerce_shop_loop_header' );
	?>

	<!-- Backdrop for filter overlay -->
	<div class="shop-filter-backdrop" aria-hidden="true"></div>
	
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
						// Store attribute name without 'pa_' prefix (widget will add it)
						if ( $attr_name === 'size' && ! $size_attr ) {
							$size_attr = $attr->attribute_name; // Store as 'size', not 'pa_size'
						}
						if ( ( $attr_name === 'color' || $attr_name === 'colour' ) && ! $color_attr ) {
							$color_attr = $attr->attribute_name; // Store as 'color', not 'pa_color'
						}
					}
					
					// Render Size filter if exists
					// WC_Widget_Layered_Nav automatically uses get_filtered_term_product_counts()
					// which considers current query (category/products being displayed)
					// - On category page: shows only attributes from products in that category
					// - On shop page: shows attributes from all displayed products
					if ( $size_attr ) {
						$size_taxonomy = wc_attribute_taxonomy_name( $size_attr );
						if ( taxonomy_exists( $size_taxonomy ) ) {
							$size_label = wc_attribute_label( $size_taxonomy );
							the_widget( 'WC_Widget_Layered_Nav', array( 
								'title' => $size_label ?: 'Size', 
								'attribute' => $size_attr, // Widget will add 'pa_' prefix
								'query_type' => 'or' // Allow multiple selections
							) );
						}
					}
					
					// Render Color filter if exists
					// Same behavior - automatically filtered by current products
					if ( $color_attr ) {
						$color_taxonomy = wc_attribute_taxonomy_name( $color_attr );
						if ( taxonomy_exists( $color_taxonomy ) ) {
							$color_label = wc_attribute_label( $color_taxonomy );
							the_widget( 'WC_Widget_Layered_Nav', array( 
								'title' => $color_label ?: 'Color', 
								'attribute' => $color_attr, // Widget will add 'pa_' prefix
								'query_type' => 'or' // Allow multiple selections
							) );
						}
					}
				}
				?>
			<?php endif; ?>
		</aside>

		<section class="shop-content">
			<?php
			if ( woocommerce_product_loop() ) {
				/**
				 * Hook: woocommerce_before_shop_loop.
				 *
				 * @hooked woocommerce_output_all_notices - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				?>
				<div class="shop-toolbar">
					<div class="shop-toolbar-left">
						<button type="button" class="shop-filter-toggle" aria-expanded="false">
							Filter
						</button>
						<?php
						// Output notices before toolbar
						woocommerce_output_all_notices();
						// Result count and ordering are in toolbar
						woocommerce_result_count();
						?>
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

				/**
				 * Hook: woocommerce_after_shop_loop.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			} else {
				/**
				 * Hook: woocommerce_no_products_found.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
			}
			?>
		</section>
	</div>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
