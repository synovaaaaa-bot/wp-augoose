<?php
/**
 * Page Template: FAQ (slug: faq)
 *
 * @package WP_Augoose
 */

get_header();
?>

<main id="primary" class="site-main">
    <section class="augoose-static">
        <div class="container">
            <h1 class="augoose-page-title"><?php the_title(); ?></h1>
            <p class="augoose-page-subtitle">
                <?php echo esc_html__( 'Answers to common questions about orders, shipping, and payments.', 'wp-augoose' ); ?>
            </p>

            <div class="augoose-card">
                <?php
                while ( have_posts() ) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();

