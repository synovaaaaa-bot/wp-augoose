<?php
/**
 * Template for Cart Page
 *
 * @package WP_Augoose
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php
    // Output WooCommerce cart content
    if ( class_exists( 'WooCommerce' ) ) {
        // Output the cart shortcode which will use our custom template
        echo do_shortcode( '[woocommerce_cart]' );
    } else {
        // Fallback to page content
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php
        endwhile;
    }
    ?>
</main>

<?php
get_footer();
