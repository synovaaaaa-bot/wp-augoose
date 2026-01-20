<?php
/**
 * Wishlist Page (integrated)
 *
 * Create a WordPress Page with slug "wishlist" and this template will be used automatically.
 *
 * @package WP_Augoose
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="container">
		<?php echo do_shortcode( '[wp_augoose_wishlist]' ); ?>
	</div>
</main>

<?php
get_footer();

