<?php
/**
 * The front page template file
 *
 * @package WP_Augoose
 */

get_header();
?>

<main id="primary" class="site-main">
	
	<!-- Hero Section -->
	<?php
	$hero_image_id  = (int) get_theme_mod( 'wp_augoose_home_hero_image', 0 );
	$hero_image_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';
	$hero_style     = $hero_image_url ? sprintf( '--hero-image: url(%s);', esc_url( $hero_image_url ) ) : '';
	?>
	<section class="hero-section" <?php echo $hero_style ? 'style="' . esc_attr( $hero_style ) . '"' : ''; ?>>
		<div class="container">
			<div class="hero-inner">
				<div class="hero-content">
					<p class="hero-eyebrow">International &amp; domestic shipping available</p>
					<h1><?php bloginfo( 'name' ); ?></h1>
					<p class="hero-subtitle">Authentic apparel, crafted in Indonesia</p>
					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<div class="hero-cta">
							<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn-primary">
								Shop Now
							</a>
							<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn-secondary">
								View Collection
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php if ( class_exists( 'WooCommerce' ) ) : ?>

		<!-- Latest Collection -->
		<section class="latest-collection">
			<div class="container">
				<div class="section-head">
					<h2 class="section-title">LATEST COLLECTION</h2>
					<a class="section-link" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">VIEW ALL</a>
				</div>
				<?php
				// Get latest 12 products (6 atas + 6 bawah = 2 rows x 6 columns)
				$products = wc_get_products( array( 
					'limit' => 12,
					'status' => 'publish',
					'orderby' => 'date',
					'order' => 'DESC'
				) );
				
				if ( $products && count( $products ) > 0 ) {
					// Use custom template for Latest Collection - 12 products (2 rows x 6 columns), NO slider
					// Loop manually to ensure exactly 12 products
					echo '<div class="latest-collection-products no-slider">';
					echo '<ul class="products columns-6 latest">';
					
					// Set WooCommerce loop properties
					wc_set_loop_prop( 'columns', 6 );
					wc_set_loop_prop( 'is_shortcode', false );
					
					// Loop through products and render using custom template
					// IMPORTANT: Limit to exactly 12 products (6 atas + 6 bawah)
					$products_to_show = array_slice( $products, 0, 12 );
					
					global $product; // Declare global before loop
					foreach ( $products_to_show as $product ) {
						wc_get_template_part( 'content', 'product-latest' );
					}
					
					// Reset loop
					wc_reset_loop();
					
					echo '</ul>';
					echo '</div>';
				} else {
					// Show dummy products for demo
					echo '<div class="woocommerce"><ul class="products columns-4 latest">';
					for ( $i = 1; $i <= 4; $i++ ) {
						echo '<li class="product">';
						echo '<div class="product-inner">';
						echo '<div style="width:100%;height:300px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;font-size:14px;">Product Image</div>';
						echo '<h2>Product ' . $i . '</h2>';
						echo '<span class="price">$' . ( $i * 10 ) . '</span>';
						echo '</div>';
						echo '</li>';
					}
					echo '</ul></div>';
				}
				?>
			</div>
		</section>

		<!-- Info Row -->
		<section class="info-row">
			<div class="container">
				<div class="info-row-grid">
					<div class="info-row-item">
						<div class="info-row-icon">⛨</div>
						<h3>Guarantee materials</h3>
						<p>Premium materials to keep quality, comfort and wear.</p>
					</div>
					<div class="info-row-item">
						<div class="info-row-icon">✶</div>
						<h3>Clean craftsmanship</h3>
						<p>Lightweight construction &amp; clean details by hand.</p>
					</div>
					<div class="info-row-item">
						<div class="info-row-icon">⌁</div>
						<h3>Global shipping</h3>
						<p>Worldwide delivery and tracking, every step of the way.</p>
					</div>
				</div>
			</div>
		</section>

	<?php endif; ?>

	<!-- How to Order -->
	<section class="how-to-order">
		<div class="container">
			<h2 class="section-title">How to order</h2>
			<p class="section-subtitle">Simple and straightforward process</p>
			<div class="steps-grid">
				<div class="step-card"><div class="step-no">01</div><div class="step-title">Browse catalogue</div><div class="step-desc">Explore our collection.</div></div>
				<div class="step-card"><div class="step-no">02</div><div class="step-title">Add to cart</div><div class="step-desc">Select your items.</div></div>
				<div class="step-card"><div class="step-no">03</div><div class="step-title">Checkout</div><div class="step-desc">Fill shipping details.</div></div>
				<div class="step-card"><div class="step-no">04</div><div class="step-title">Payment</div><div class="step-desc">Secure payment options.</div></div>
				<div class="step-card"><div class="step-no">05</div><div class="step-title">Track shipment</div><div class="step-desc">We’ll send your AWB number for you to track the shipment.</div></div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
