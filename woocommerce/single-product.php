<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @package Minimal_Ecommerce
 * @version 1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header('shop'); ?>

<?php
/**
 * Hook: woocommerce_before_main_content.
 */
do_action('woocommerce_before_main_content');
?>

<div class="single-product-container">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>

        <?php wc_get_template_part('content', 'single-product'); ?>

    <?php endwhile; // end of the loop. ?>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action('woocommerce_after_main_content');
?>

<?php
/**
 * Hook: woocommerce_sidebar.
 */
// do_action('woocommerce_sidebar'); // Uncomment if you want sidebar
?>

<?php
get_footer('shop');
