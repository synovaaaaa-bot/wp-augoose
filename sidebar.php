<?php
/**
 * The sidebar containing the main widget area
 *
 * @package WP_Augoose
 */

// Don't show sidebar on WooCommerce pages
if ( function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
    return;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
    return;
}
?>

<aside id="secondary" class="widget-area">
    <?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
