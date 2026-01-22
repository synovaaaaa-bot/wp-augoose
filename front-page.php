<?php
/**
 * The front page template file
 *
 * @package WP_Augoose
 */

get_header();
?>

<main id="primary" class="site-main">
	
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>

		<!-- Latest Collection -->
		<section class="latest-collection">
			<div class="container">
				<div class="section-head">
					<h2 class="section-title">LATEST COLLECTION</h2>
					<a class="section-link" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">VIEW ALL</a>
				</div>
				<?php
				// Check if we have products first
				$products = wc_get_products( array( 
					'limit' => 8,
					'status' => 'publish'
				) );
				
				if ( $products && count( $products ) > 0 ) {
					echo do_shortcode( '[products limit="8" columns="4" orderby="date" class="latest"]' );
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
